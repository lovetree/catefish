<?php

class UserModel extends BaseModel
{

    const LOGIN_TYPE_WX = 'wx';         //微信登录
    const LOGIN_TYPE_PW = 'pw';         //用户名密码登录
    const LOGIN_TYPE_GUEST = 'guest';   //游客登录

    /**
     * 用户登录
     * @param string $cmd
     * @param array $params
     * @return mixed 正常返回用户信息的数组；false=登录失败;0=帐号冻结;1=需要授权
     */
    public function login(string $cmd, array $params = array())
    {
        $result = null;
        switch ($cmd) {
            case self::LOGIN_TYPE_WX:
                $result = $this->wxLogin($params['wx_code'] ?? null, $params['terminal'] ?? null, $params['openid'] ?? null, $params['device_id'] ?? null);
                break;
            case self::LOGIN_TYPE_PW:
                $result = $this->pwLogin($params['account'], $params['password'], $params['terminal'] ?? null);
                break;
            case self::LOGIN_TYPE_GUEST:
                $result = $this->guestLogin($params['account'] ?? null);
                break;
        }
        if (false === $result || empty($result) || !is_array($result)) {
            if (is_int($result)) {
                //第三方登录过期
                return $result;
            }
            return false;
        }
        //status
        $status = $result['u_status'];
        unset($result['u_status']);
        //非正常状态
        if ($status == 0) {
            //冻结
            return RCODE_ACCOUNT_FREEZING;
        }
        //初始化用户数据
        $this->initUserData($result['u_id'], array_change_keys(
            array_fetch($result, ['u_account', 'u_nickname', 'u_photo', 'u_vip', 'u_gender', 'u_phone', 'u_invite_id','u_wx_unionid']), [
                'u_account' => 'account',
                'u_nickname' => 'nickname',
                'u_photo' => 'photo',
                'u_vip' => 'vip',
                'u_gender' => 'sex',
                'u_phone' => 'phone',
                'u_invite_id' => 'invite_id',
                'u_wx_unionid'=>'wx_unionid'
            ]
        ), $params);

        //从A平台同步积分信息
        $this->refreshPoint($result['u_id'], $params['terminal'] ?? null);
        //构建数据
        $userData = new Data\UserModel($result['u_id']);
        $userData->all();
        $result['u_gold'] = $userData->gold;
        $result['u_credit'] = $userData->credit;
        $result['u_ticket'] = $userData->ticket;
        $result['u_emerald'] = $userData->emerald;
        $result['u_safe_gold'] = $userData->safe_gold;
        $result['u_point'] = $userData->point;
        $result['u_agent'] = $userData->getAgent();
        //记录登录状态
        $_SESSION['user_id'] = $result['u_id'];
        return $result;
    }

    public function getLoginUserID()
    {
        return $_SESSION['user_id'] ?? false;
    }

    /**
     * 检测用户的登录状态
     */
    public function isLogin()
    {
        if (!empty($_SESSION['user_id'])) {
            return true;
        }
        return false;
    }

    /**
     * 微信登录
     * @param string $code 授权码
     * @param string $openid 微信用户唯一标识
     * @return mixed 正常返回一个数组; false=失败；RCODE_LOGIN_AUTH_EXPIRED=授权过期
     */
    protected function wxLogin($code, $terminal, $openid = null, $device_id = null)
    {
        $db = $this->DB();
        $bFromWx = true;   //是否需要经过微信授权
        $user_id = null;
        //已授权登录
        if (!empty($openid)) {
            //如果已经授权登录过了，直接发送用户的微信唯一标志给A平台
            $wx_reg["wx_user"] = $openid;
            $return_data = postData("login", $terminal, $wx_reg);
            switch ($return_data['result_code']) {
                case '0':
                    //成功
                    $bFromWx = false;
                    $user_id = $return_data['data']['user_id'];
                    $invite_id = $return_data['data']['recommender'];
                    $user_id= $db->trancation(function ($db, &$rollback) use ($return_data, $user_id, $device_id, $invite_id) {
                        $select = $db->query_fetch('select id from ms_user where wx_unionid = ?', array($user_id));
                        if ($select) {
                            $user_id = $select['id'];
                            $update = array(
                                'nickname' => $return_data['data']['nick_name'],
                                'avatar' => $return_data['data']['head_portrait'],
                            );

                            $this->edit($user_id, $update);

                        } else {
                            $register = array(
                                'wx_unionid' => $user_id,
                                'username' => 'wx' . date('d') . bin2hex(random_bytes(4)),
                                'password' => md5('weixin' . bin2hex(random_bytes(4))),
                                'nickname' => $return_data['data']['nick_name'],
                                'avatar' => $return_data['data']['head_portrait'],
                                'gender' => $return_data['data']['sex'],
                                'address' => $return_data['data']['address'],
                                // 'wx_unionid' => $info['unionid'] ?? '',
                                'type' => 2,
                                'hardcode' => $device_id,
                                'agent_id' => 0
                            );

                            //如果有绑定推荐人，则更新推荐人字段
                            if ($invite_id != '0' && $invite_id != "") {
                                $register['invite_id'] = $invite_id;
                                $register['invite_type'] = 0;
                                $register['invite_time'] = time();
                            }

                            $user_id = $this->register($register, false);
                            if (!$user_id) {
                                $rollback = true;
                                return false;
                            }

                            $share = new ShareModel();
                            if (isset($register['invite_id'])) {
                                //添加一条记录
                                $res = $share->insertInvite($invite_id, $user_id);
                                if (!$res) {
                                    $rollback = true;
                                    return false;
                                }
                            }

                        }
                        return $user_id;
                    });

                    break;
                case '10101006':
                    //用户信息不存在
                    //return $return_data['result_desc'];
                    // break;
                case '10101001':
                    //操作失败
                    //return $return_data['result_desc'];
                    //break;
                default :
                    return RCODE_LOGIN_AUTH_EXPIRED;
                    break;
            }
        }
        //授权登录
        if ($bFromWx) {
            if (empty($code)) {
                logMessage('wxLogin: 需要授权才能登录', LOG_NOTICE);
                return false;
            }
            //去A平台授权，完成注册登录
            $wx_reg["wx_user"] = $code;
            //logMessage("wxLogin: 微信登录code。".$code);
            //$return_data['code']=1;
            //$return_data['msg']=1;
            $return_data = postData("login", $terminal, $wx_reg);
            //logMessage("wxLogin: 微信登录返回数据。".json_encode($return_data));
            if ($return_data['code'] == 0) {
                $user_id = $return_data['data']['user_id'];
                $invite_id = $return_data['data']['recommender'];
                $user_id=  $db->trancation(function ($db, &$rollback) use ($return_data, $user_id, $device_id, $invite_id) {
                    //判断unionid唯一
                    $select = $db->query_fetch('select id from ms_user where wx_unionid = ?', array($user_id));
                    if ($select) {
                        //已有unionid的数据
                        $user_id = $select['id'];
                        $update = array(
                            'nickname' => $return_data['data']['nick_name'],
                            'avatar' => $return_data['data']['head_portrait'],
                        );

                        $this->edit($user_id, $update);
                    } else {
                        //没有，则新建

                        $register = array(
                            'wx_unionid' => $user_id,
                            'username' => 'wx' . date('d') . bin2hex(random_bytes(4)),
                            'password' => md5('weixin' . bin2hex(random_bytes(4))),
                            'nickname' => $return_data['data']['nick_name'],
                            'avatar' => $return_data['data']['head_portrait'],
                            'gender' => $return_data['data']['sex'],
                            'address' => $return_data['data']['address'],
                            // 'wx_unionid' => $info['unionid'] ?? '',
                            'type' => 2,
                            'hardcode' => $device_id,
                            'agent_id' => 0
                        );

                        //如果有绑定推荐人，则更新推荐人字段
                        if ($invite_id != '0' && $invite_id != "") {
                            $register['invite_id'] = $invite_id;
                            $register['invite_type'] = 0;
                            $register['invite_time'] = time();
                        }

                        $user_id = $this->register($register, false);

                        if (!$user_id) {
                            $rollback = true;
                            return false;
                        }

                        $share = new ShareModel();
                        if (isset($register['invite_id'])) {
                            //添加一条记录
                            $res = $share->insertInvite($invite_id, $user_id);
                            if (!$res) {
                                $rollback = true;
                                return false;
                            }
                        }


                    }
                    return $user_id;
                });
            } else {
                logMessage('wxLogin: 微信注册失败。' . $return_data['msg'], LOG_NOTICE);
                return false;
            }
        }

        $result = $this->getLoginUserInfo($user_id);
        if (false === $result || empty($result)) {
            logMessage('wxLogin: 获取不到用户信息', LOG_NOTICE);
            return false;
        }
        $result['openid'] = $openid;
        return $result;
    }

    /**
     * 用户名密码登录
     */
    protected function pwLogin(string $username, string $password, $terminal)
    {
        //对username进行判断，来区分是邮箱登录，还是手机号，还是用户名
        /* $sql = 'select id,status from ms_user where username = ? and password = ? and type = 0 and `status` != -1';
         if (checkPhone($username)){
             $sql = 'select id,status from ms_user where phone = ? and password = ? and type = 0 and `status` != -1';
         }

         if (checkEmail($username)){
             $sql = 'select id,status from ms_user where email = ? and password = ? and type = 0 and `status` != -1';
         }

         $db = $this->DB();
         $result = $db->query_fetch($sql, array($username, md5($password)));*/
        $pw_data['login_name'] = $username;
        $pw_data['password'] = $password;
        //$postdata=getPostData('login',$terminal,$pw_data);
        $result = postData('login', $terminal, $pw_data);
        if ($result['code'] != 0) {
            //用户名不正确或密码不匹配
            logMessage("Login[pw]: .({$result['msg']}). ({$username})");
            return false;
        }
        $db = $this->DB();
        $info = $db->query_fetch('select * from ms_user where wx_unionid = ?', array($result['data']['user_id']));
        if (!$info) {
            //用户不存在，根据A平台反馈的信息写入B平台数据库
            $userModel = new UserModel();
            $data = array();
            $avatar=$result['data']['head_portrait'];
            if($avatar==null || $avatar=='')
            {
                $avatar= toUrl(DEF_AVATAR);
            }
            $data['wx_unionid'] = $result['data']['user_id'];
            $data['username'] = $username;
            $data['password'] = md5($password);
            $data['type'] = 0;
            $data['phone'] = $username;
            $data['nickname'] = $result['data']['nick_name'];
            $data['avatar'] = $avatar;
            if (!is_null($result['data']['recommender'])) {
                $data['invite_id'] = $result['data']['recommender'];
                $data['invite_type'] = 0;
                $data['invite_time'] = time();
            }
            if (!$userModel->register($data)) {
                return $this->failed(_1000104);
            }
            $info = $db->query_fetch('select * from ms_user where wx_unionid = ?', array($result['data']['user_id']));
        }
        return $this->getLoginUserInfo($info['id']);
    }

    /**
     * 游客登录
     */
    protected function guestLogin($username)
    {
        $db = $this->DB();
        if (empty($username)) {
            $user_info = false;
        } else {
            $sql = 'select id from ms_user where username = ? and type = 1 and `status` != -1';
            $user_info = $db->query_fetch($sql, array($username));
        }
        if ($user_info) {
            $user_id = $user_info['id'];
        } else {
            $username = 'gu' . date('d') . bin2hex(random_bytes(4));
            $pass = bin2hex(random_bytes(4));
            $user_id = $this->register(array(
                'username' => $username,
                'password' => md5($pass),
                'nickname' => '游客' . random_int(100000, 999999),
                'type' => 1, //微信登录
                'avatar' => toUrl(DEF_AVATAR) //头像
            ));
        }
        $result = $this->getLoginUserInfo($user_id);
        return $result;
    }

    /**
     * 初始化用户数据
     * @param int $user_id
     */
    protected function initUserData(int $user_id, array $data = [], array $params = null)
    {
        $userData = new Data\UserModel($user_id);
        if (!$userData->exists()) {
            //其他数据需要使用脚本操作
            $r_p = [];
            $r_v = [];
            //金币
            $r_p[] = 'gold';
            $r_v[] = 3000;
            //钻石
            $r_p[] = 'credit';
            $r_v[] = 0;
            //绿宝石
            $r_p[] = 'emerald';
            $r_v[] = 2500;
            //冰封道具
            $r_p[] = 'frozen';
            $r_v[] = 10;
            //鹰眼道具
            $r_p[] = 'eagleeye';
            $r_v[] = 10;

            //2017-06-05 增加判断：如果是苹果送审版本，解锁炮台
            if (isset($params['publish']) && $params['publish'] == 2) {
                //炮台等级 max:10000
                $r_p[] = 'MaxBulletMul';
                $r_v[] = 10000;
                //炮台解锁情况 max:15
                $r_p[] = 'BulletUnlockSituation';
                $r_v[] = 15;
            } else {
                //炮台等级 max:10000
                $r_p[] = 'MaxBulletMul';
                $r_v[] = 10;
                //炮台解锁情况 max:15
                $r_p[] = 'BulletUnlockSituation';
                $r_v[] = 0;
            }

            //当前炮台升级成功率
            $r_p[] = 'BulletUpSuccessRate';
            $r_v[] = 842150450;
            //炮台等级
            $r_p[] = 'BulletLv';
            $r_v[] = 0;
            //双倍炮是否解锁
            $r_p[] = 'DoubleBulletUnlocked';
            $r_v[] = 0;
            $this->onChangeUserData($user_id, $r_p, $r_v);
            //记录绿钻石流水
            GoldModel::emerald([
                'user_id' => $user_id,
                'emerald_change' => 2500,
                'emerald_after' => 2500,
                'type' => 3,
                'create_time' => time()
            ], $this->DB());
            //记录金币流水
            GoldModel::log([
                'user_id' => $user_id,
                'gold_change' => 3000,
                'gold_after' => 3000,
                'type' => GOLD_FLOW_TYPE_NEW,
                'created_at' => time()
            ], $this->DB());
            // $this->onChangeUserGold($user_id, 100000);
//            $data['gold'] = 10000;
//            $data['credit'] = 0;
//            $data['ticket'] = 500;
//            $data['emerald'] = 10000;
            //钱柜金币
            $data['safe_gold'] = 0;
//            //炮台等级
//            $data['MaxBulletMul'] = 1;
//            //炮台解锁情况
//            $data['BulletUnlockSituation'] = 0;
//            //当前炮台升级成功率
//            $data['BulletUpSuccessRate'] = 842150450;
//            //炮台等级
//            $data['BulletLv'] = 0;
//            //双倍炮是否解锁
//            $data['DoubleBulletUnlocked'] = 0;
//            //道具鹰眼
//            $data['eagleeye'] = 10;
//            //道具冰封
//            $data['frozen'] = 10;
            //捕鱼任务
            $data['fishTask'] = json_encode(['date' => date('Y-m-d'), 'num' => 0]);
            //商城任务
            $data['shopTask'] = json_encode(['date' => date('Y-m-d'), 'num' => 0]);
            //炮台解锁任务
            $data['batteryTask'] = json_encode(['date' => date('Y-m-d'), 'num' => 1]);
            //分享任务
            $data['shareTask'] = json_encode(['date' => date('Y-m-d'), 'num' => 0]);
            //人气值
            $data['popularity'] = 0;
        }
        $data['token'] = session_id();
        //$data['ip'] = $this->input()->ip_address();
        if (!empty($data)) {
            $userData->save($data);
        }
    }

    /**
     * 注册用户信息
     * @param array $data
     */
    public function register(array $data, bool $trancation = true)
    {
        $db = $this->DB();
        if ($trancation) {
            $db->beginTransaction();
        }
        $rollback = false;
        $data['created_at'] = time();
        $data['updated_at'] = time();
        if (!$db->insert('ms_user', array_fetch($data, array('id', 'created_at', 'updated_at', 'username', 'nickname', 'password', 'type', 'phone', 'hardcode', 'wx_unionid', 'agent_id', 'invite_id', 'invite_time')))) {
            logMessage('ms_user 插入失败;' . var_export($data, true));
            $rollback = true;
        }

        $user_id = $db->lastInsertId();
        $data['reg_ip'] = $this->input()->ip_address();
        $info = array_fetch($data, array('nickname', 'gender', 'avatar', 'reg_ip', 'address'));
        $info['user_id'] = $user_id;

        if (!$db->insert('ms_user_info', $info)) {
            logMessage('ms_user_info 插入失败;' . var_export($info, true));
            $rollback = true;
        }
        if ($trancation) {
            if ($rollback) {
                $db->rollback();
            } else {
                $db->commit();
            }
        }

        //发送邮件
        $common = new CommonModel();
        $email = [];
        $email['title'] = "一指赢团队恭贺您的到来";
        $email['content'] = "一指赢团队恭贺您的到来，一指赢团队恭贺您的到来，一指赢团队恭贺您的到来";
        $email['from_id'] = 0;
        //$common->sendEmail($user_id, $email);
        return $rollback ? false : $user_id;
    }

    /**
     * 获取用户数据
     * @param type $user_id
     * @return type
     */
    public function getLoginUserInfo($user_id)
    {
        $sql = <<<SQL
            select 
                u.id as u_id, 
                u.username as u_account, 
                u.`status` as u_status,
                u.nickname as u_nickname, 
                u.invite_id as u_invite_id,
                u.wx_unionid as u_wx_unionid,
                ui.avatar as u_photo, 
                ui.realname as u_realname,
                ui.gender as u_gender,
                u.phone as u_phone,
                ui.user_level as u_vip,
                ui.agent_update_times as agent_up_times,
                (
                    select count(1) from ms_game_email as g where g.to_id = u.id and g.`status` = 0 and (g.expired_time is null or g.expired_time >= ?)
		) as u_email_count
            from ms_user as u 
            left join ms_user_info as ui on ui.user_id = u.id 
            where u.id = ?
SQL;
        $user_info = $this->DB()->query_fetch($sql, array(time(), $user_id));
        return $user_info;
    }

    public function getUserInfoByMysql($user_id, $field)
    {
        $sql = <<<SQL
            select $field from ms_user where id = ?
SQL;
        $user_info = $this->DB()->query_fetch($sql, array($user_id));
        return $user_info;
    }

    /**
     * 获取用户数据
     * @param type $user_id
     * @return type
     */
    public function getUserInfo($user_id, $terminal='ios')
    {

        $this->refreshPoint($user_id, $terminal);
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        if ($redis->exists(RK_USER_INFO . $user_id)) {
            $data = $redis->hMget(RK_USER_INFO . $user_id,
                array('nickname', 'photo', 'vip', 'sex', 'invite_id','wx_unionid',
                    'MaxBulletMul', 'gold', 'credit', 'emerald', 'point',
                    'BulletUnlockSituation', 'BulletUpSuccessRate', 'BulletLv', 'DoubleBulletUnlocked', 'popularity'));
            $user_info = [];
            if (false !== $data) {
                $user_info['u_id'] = $user_id;
                $user_info['u_nickname'] = $data['nickname'] ?? 'null';
                $user_info['u_photo'] = $data['photo'] ?? '';
                $user_info['u_gender'] = $data['sex'] ?? 0;
                $user_info['u_vip'] = $data['vip'] ?? 0;
                $user_info['u_invite_id'] = $data['invite_id'] ?? 0;
                $user_info['u_wx_unionid'] = $data['wx_unionid'] ?? 0;
                $user_info['MaxBulletMul'] = intval($data['MaxBulletMul']) == 0 ? 1 : intval($data['MaxBulletMul']);
                $user_info['gold'] = intval($data['gold']);
                $user_info['credit'] = intval($data['credit']);
                $user_info['point'] = intval($data['point']);
                $user_info['emerald'] = intval($data['emerald']);
                $user_info['BulletUnlockSituation'] = intval($data['BulletUnlockSituation']);
                $user_info['BulletUpSuccessRate'] = intval($data['BulletUpSuccessRate']) == 0 ? 842150450 : intval($data['BulletUpSuccessRate']);
                $user_info['BulletLv'] = intval($data['BulletLv']) ?? 0;
                $user_info['DoubleBulletUnlocked'] = intval($data['DoubleBulletUnlocked']) ?? 0;
                $user_info['popularity'] = intval($data['popularity']) ?? 0;
                //$user_info['ip'] = $this->input()->ip_address();
            }
        } else {
            $sql = <<<SQL
            select 
                u.id as u_id, 
                u.invite_id as u_invite_id,
                u.wx_unionid as u_wx_unionid,
                ui.nickname as u_nickname, 
                ui.avatar as u_photo, 
                ui.gender as u_gender,
                ui.user_level as u_vip
            from ms_user as u 
            left join ms_user_info as ui on ui.user_id = u.id 
            where u.id = ?
SQL;
            $user_info = $this->DB()->query_fetch($sql, array($user_id));
            //获取redis中的数据
            $estate = $this->getOneUserEstate($user_id);
            $user_info['gold'] = $estate ? $estate['gold'] : 0;
            $user_info['credit'] = $estate ? $estate['credit'] : 0;
            $user_info['emerald'] = $estate ? $estate['emerald'] : 0;
            $user_info['point'] = $estate ? $estate['point'] : 0;
            $fishEstate = $this->getOneFishEstate($user_id);
            $user_info['frozen'] = $fishEstate ? $fishEstate['frozen'] : 0;
            $user_info['eagleeye'] = $fishEstate ? $fishEstate['eagleeye'] : 0;
            $user_info['BulletUnlockSituation'] = $fishEstate ? $fishEstate['BulletUnlockSituation'] : 0;
            $user_info['BulletUpSuccessRate'] = $fishEstate ? $fishEstate['BulletUpSuccessRate'] : 842150450;
            $user_info['BulletLv'] = $fishEstate ? $fishEstate['BulletLv'] : 0;
            $user_info['DoubleBulletUnlocked'] = $fishEstate ? $fishEstate['DoubleBulletUnlocked'] : 0;
            $user_info['MaxBulletMul'] = $fishEstate ? $fishEstate['MaxBulletMul'] : 1;
            //$user_info['ip'] = $this->input()->ip_address();
            $userData = new Data\UserModel($user_id);
            $userData->save($user_info);
        }

        $user_info['u_ip'] = $this->input()->ip_address();
        return $user_info;
    }

    /**
     * 修改用户数据
     * @param int $user_id
     * @param array $data
     * @return boolean
     */
    public function edit($user_id, array $data)
    {
        $fields = array_fetch($data, array('nickname', 'avatar', 'idcard', 'realname'));
        if ($fields) {
            try {
                $this->DB()->update('ms_user_info', $fields, 'user_id = ' . $user_id);
                $fields3 = [];
                if (array_key_exists('nickname', $fields)) {
                    //当修改昵称时，同步更新ms_user表中的nickname
                    $fields2 = array_fetch($data, array('nickname'));
                    $fields3['nickname'] = $fields['nickname'];
                    $this->DB()->update('ms_user', $fields2, 'id = ' . $user_id);
                }

                if (array_key_exists('avatar', $fields)) {
                    $fields3['photo'] = $fields['avatar'];
                }

                if (count($fields3) > 0) {
                    $redis = PRedis::instance();
                    $redis->select(R_GAME_DB);
                    if ($redis->exists(RK_USER_INFO . $user_id)) {
                        //
                        $redis->hMset(RK_USER_INFO . $user_id, $fields3);
                    }
                }
                return true;
            } catch (Exception $ex) {
                logMessage($ex->getTraceAsString());
                return false;
            }
        }
        return false;
    }

    /**
     * 绑定手机号码，密码
     * @param int $user_id
     * @param array $data
     * @return boolean
     */
    public function upUser($user_id, array $data)
    {
        $fields = array_fetch($data, array('phone', 'password', 'type', 'nickname'));
        if ($fields) {
            try {
                $this->DB()->update('ms_user', $fields, 'id = ' . $user_id);
                $this->DB()->update('ms_user_info', ['avatar' => $data['avatar']], 'user_id = ' . $user_id);
                $redis = PRedis::instance();
                $redis->select(R_GAME_DB);
                $key = "info@" . $user_id;
                $redis->hSet($key, 'phone', $data['phone']);
                return true;
            } catch (Exception $ex) {
                logMessage($ex->getTraceAsString());
                return false;
            }
        }
        return false;
    }

    /**
     * 获取用户战绩
     * @param int $user_id
     */
    public function getExploits(int $user_id)
    {
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $data = $redis->mGet(array(
            RK_USER_EXPLOITS_TOTAL . $user_id,
            RK_USER_EXPLOITS_WIN . $user_id,
            RK_USER_EXPLOITS_LOSE . $user_id
        ), 0);
        $keys = array(
            'u_all_times',
            'u_win_times',
            'u_lose_times'
        );
        return array_combine($keys, $data);
    }

    /**
     * 记录最后一次登录的信息
     */
    public function recordLastLogin($user_id)
    {
        $this->DB()->update('ms_user_info', array(
            'last_time' => time(),
            'last_ip' => $this->input()->ip_address(),
            'login_times' => ['IFNULL(login_times, 0) + 1']
        ), 'user_id = ' . $user_id);
    }

    /**
     * 添加用户反馈的信息
     * @param int $user_id
     * @param string $content
     * @return bool -1=反馈时间太频繁；false=插入数据失败；true=成功
     */
    public function addFeedback(int $user_id, string $content)
    {
        $db = $this->DB();
        //第一步：检测上次发送反馈的时间
        $last = $db->query_fetch('select 1 from ms_feedback where user_id = ? and created_date >= ? order by created_date desc limit 1', [$user_id, strtotime('-2 hour')]);
        if (!empty($last)) {
            return -1;
        }
        //第二步：插入数据
        $status = $db->insert('ms_feedback', [
            'user_id' => $user_id,
            'content' => $content,
            'created_date' => time(),
            'ip' => $this->input()->ip_address()
        ]);
        return $status ? true : false;
    }

    /**
     * 获取信息，根据手机号
     * @param $phone
     * @param int $content
     * @return bool -1=反馈时间太频繁；false=插入数据失败；true=成功
     */
    public function getInfoByPhone($phone)
    {
        $sql = <<<SQL
                select id,status,phone from ms_user where phone = $phone limit 1
SQL;
        return $this->DB()->query_fetch($sql);
    }

    public function resetPass($user_id, $data)
    {
        $fields = array_fetch($data, array('password', 'updated_at'));
        if ($fields) {
            try {
                $this->DB()->update('ms_user', $fields, 'id = ' . $user_id);
                return true;
            } catch (Exception $ex) {
                logMessage($ex->getTraceAsString());
                return false;
            }
        }
        return false;
    }

    public function getInfoByRedis(array $fields)
    {
        //需要获取到用户redis中的值，如果不存在就提示系统繁忙，存在做处理
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        if (!$redis->exists(RK_USER_INFO . $_SESSION['user_id'])) {
            return [0, 0];
        }
        return $redis->hMget(RK_USER_INFO . $_SESSION['user_id'], $fields);
    }

    public function getOneUser(int $id, array $fields = ['*'])
    {
        $fields = empty($fields) ? '*' : implode(',', $fields);
        $sql = <<<SQL
                select $fields from ms_user where id = ? limit 1
SQL;
        return $this->DB()->query_fetch($sql, array($id));
    }

    public function getOneUserInfo(int $id, array $fields = ['*'])
    {
        $fields = empty($fields) ? '*' : implode(',', $fields);
        $sql = <<<SQL
                select $fields from ms_user_info where user_id = ? limit 1
SQL;
        return $this->DB()->query_fetch($sql, array($id));
    }

    public function getOneUserEstate(int $id, array $fields = ['*'])
    {
        $fields = empty($fields) ? '*' : implode(',', $fields);
        $sql = <<<SQL
                select $fields from ms_user_estate where user_id = ? limit 1
SQL;
        return $this->DB()->query_fetch($sql, array($id));
    }

    public function getOneFishEstate(int $id, array $fields = ['*'])
    {
        $fields = empty($fields) ? '*' : implode(',', $fields);
        $sql = <<<SQL
                select $fields from ms_fish_estate where user_id = ? limit 1
SQL;
        return $this->DB()->query_fetch($sql, array($id));
    }

    public function sysFilter($type)
    {
        //ip过滤
        $sql = <<<SQL
          select id from ms_ban_ip where ban_type = ? and ip = ?
SQL;
        $result = $this->DB()->query_fetch($sql, [$type, $this->input()->ip_address()]);

        if (!empty($result)) {
            return false;
        }

        return true;
    }

    public function refreshPoint($user_id, $terminal)
    {
        $info = $this->getOneUser($user_id);
        if (is_null($info['wx_unionid'])) {
            //当为游客时，直接返回
            $userData = new Data\UserModel($user_id);
            $savedata['point'] = 0;
            $userData->save($savedata);
            return 0;
        }
        if($terminal==null || $terminal=='')
        {
            $terminal='ios';
        }
        $queryData['user_id'] = $info['wx_unionid'];
        //$postdata=getPostData('userIntegral',$terminal,$queryData);
        $result = postData('userIntegral', $terminal, $queryData);
        if ($result['code'] == 0) {
            //成功获取用户积分信息之后，更新B平台的积分信息
            $userData = new Data\UserModel($user_id);
            $savedata['point'] = $result['data']['integral_total'];
            $userData->addPoint($savedata['point']);
            return $result['data']['integral_total'];
        } else {
            return false;
        }

    }
}
