<?php

class UserController extends \BaseController
{

    /**
     * 用户登录
     */
    public function loginAction()
    {

        $input = $this->input();
        $cmd = $input->json_stream('cmd');
        $rule = array(
            'cmd' => 'required',
            'game_version' => 'required',
//            'device_id' => 'required'
            'terminal' => 'required',
        );

        if ($cmd === UserModel::LOGIN_TYPE_PW) {
            $rule['account'] = 'required';
            $rule['password'] = 'required';
        }

        $user = new UserModel();
        //ip、黑名单拦截
        if (!$user->sysFilter(BAN_TYPE_LOGIN)) {
            return $this->failed('服务器忙', RCODE_BUSY);
        }

        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }

        //游戏版本比较
        if (str_replace('.', '', $input->json_stream('game_version')) < getPlatVer()) {
            return $this->failed('版本过低，请到官网重新下载！', RCODE_UNSUPPORT);
        }

        $data = $user->login($input->json_stream('cmd'), $input->json_stream());
        if (false === $data) {
            return $cmd === UserModel::LOGIN_TYPE_PW ? $this->failed('用户名或密码不正确', RCODE_BUSY) : $this->failed('服务器繁忙', RCODE_BUSY);
        } else if (RCODE_ACCOUNT_FREEZING === $data) {
            return $this->failed('帐号被冻结', RCODE_ACCOUNT_FREEZING);
        } else if (RCODE_LOGIN_AUTH_EXPIRED === $data) {
            return $this->failed('授权信息过期', RCODE_LOGIN_AUTH_EXPIRED);
        }

        //构造数据
        $user_id = $data['u_id'];
        $data['u_email_count'] = $data['u_email_count'] ?? 0;
        $data['proxy_ip'] = config_item('game.proxy_ip');
        $data['proxy_port'] = config_item('game.proxy_port');
        $data['u_token'] = session_id();
        $data['u_vip'] = intval($data['u_vip'] ?? 0);
        $data['u_gender'] = intval($data['u_gender'] ?? 0);
        $data['u_realname'] = $data['u_realname']??null;
//        $data['u_city'] = GetIpLookup($_SERVER['REMOTE_ADDR']);
        $data['u_ip'] = $this->input()->ip_address();

        WebApp::inform(WebApp::AFTER_LOGIN, [$user_id]);
        return $this->succ($data);
    }

    /**
     * 用户信息获取
     */
    public function infoAction()
    {
        $input = $this->input();
        //验证参数
        if (!$this->validation($input->json_stream(), array(
            't_uid' => 'required',
            'terminal' => 'required'
        ))
        ) {
            return false;
        }
        $user = new UserModel();
        $data = $user->getUserInfo($input->json_stream('t_uid'),$input->json_stream('terminal'));
        if (!$data) {
            return $this->failed('服务器繁忙', RCODE_BUSY);
        }
        return $this->succ($data);
    }

    public function baseInfoAction()
    {
        $input = $this->input();
        //验证参数
        if (!$this->validation($input->json_stream(), array(
            't_uid' => 'required'
        ))
        ) {
            return false;
        }
        //需要获取到用户redis中的值，如果不存在就提示系统繁忙，存在做处理
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        if (!$redis->exists(RK_USER_INFO . $input->json_stream('t_uid'))) {
            return $this->failed(_1000106);
        }

        $user_info = $redis->hMget(RK_USER_INFO . $input->json_stream('t_uid'), ['nickname', 'gold', 'popularity', 'photo']);
        return $this->succ([
            'u_id' => $input->json_stream('t_uid'),
            'u_nickname' => $user_info['nickname'],
            'u_photo' => $user_info['photo'],
            'gold' => $user_info['gold'],
            'popularity' => $user_info['popularity']
        ]);
    }

    /**
     * 用户信息修改
     */
    public function editAction()
    {
        //检测登录
        $user = new UserModel();
        $fields = [];
        $input = $this->input();
        $input->json_stream('name') && ($fields['nickname'] = $input->json_stream('name'));
        $input->json_stream('photo') && ($fields['avatar'] = $input->json_stream('photo'));
        if (!$user->edit($_SESSION['user_id'], $fields)) {
            return $this->failed('修改失败');
        }
        return $this->succ();
    }

    /**
     * 用户认证姓名手机号
     */
    public function certificationAction()
    {
        //检测登录
        $input = $this->input();
        $user = new UserModel();
        $rule = array(
            'realname' => 'required',
            'idcard' => 'required',
//            'device_id' => 'required',
            'terminal'=>'required'
        );
        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }

        $info = $user->getOneUser($_SESSION['user_id']);

        if (!is_null($info['wx_unionid'])) {
            //当为游客时，直接返回
            $queryData['user_id'] = $info['wx_unionid'];
            $queryData['cart_code'] = $input->json_stream('idcard');
            $queryData['real_name'] = $input->json_stream('realname');
            $result = postData('realNameBangDing', $input->json_stream('terminal'), $queryData);
            if (!$result['code'] == 0) {
                return $this->failed($result['msg']);
            }
        }

        $fields = [];
        $input->json_stream('realname') && ($fields['realname'] = $input->json_stream('realname'));
        $input->json_stream('idcard') && ($fields['idcard'] = $input->json_stream('idcard'));
        if (!$user->edit($_SESSION['user_id'], $fields)) {
            return $this->failed('修改失败');
        }
        return $this->succ();
    }

    /**
     * 获取用户战绩
     */
    public function exploitsAction()
    {

        $input = $this->input();
        //验证参数
        if (!$this->validation($input->json_stream(), array(
            't_uid' => 'required'
        ))
        ) {
            return false;
        }

        $user = new UserModel();
        $data = $user->getExploits($input->json_stream('t_uid'));
        if (!$data) {
            return $this->failed('服务器繁忙', RCODE_BUSY);
        }
        return $this->succ($data);
    }

    /**
     * 用户反馈
     */
    public function feedbackAction()
    {
        WebApp::inform(WebApp::INIT_SESSION);
        $request = $this->input()->json_stream();
        //验证参数
        if (!isset($request['content']) || !$request['content']) {
            return $this->failed(_1000301);
        }
        $userModel = new UserModel();
        $status = $userModel->addFeedback($userModel->getLoginUserID(), $request['content']);
        if (-1 === $status) {
            return $this->failed(_1000004);
        } else if (false == $status) {
            return $this->failed(_1000001, RCODE_BUSY);
        }
        return $this->succ();
    }

    /**
     * 找回密码，重置密码
     */
    public function resetAction()
    {
        $input = $this->input();
        $rule = array(
            'phone' => 'required|phone',
            'password' => 'required',
            'repassword' => 'required',
            'code' => 'required',
            'terminal' => 'required',
        );

        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }

        if (!checkPassword($input->json_stream('password'))) {
            return $this->failed(_1000007);
        }

        $phone = $input->json_stream("phone");
        $password = $input->json_stream("password");
        $repassword = $input->json_stream("repassword");
        $code = $input->json_stream("code");
        $terminal = $input->json_stream("terminal");

        if ($password != $repassword) {
            return $this->failed(_1000103);
        }

        /*$smsModel = new SmsModel();
        //$userModel->sendSms($input->json_stream("phone"));
        $sms_info = $smsModel->getOne($phone, 2, $code);
        if (!$sms_info){
            return $this->failed(_1000202);
        }

        if ($sms_info['status'] == 0){
            return $this->failed(_1000202);
        }

        if ($sms_info['is_used'] == 1){
            return $this->failed(_1000202);
        }

        if (time() > intval($sms_info['c_time'] + $sms_info['expire'])){
            return $this->failed(_1000205);
        }

        if (!$smsModel->updateUsed($sms_info['id'])){
            return $this->failed(_1000003);
        }

        //验证手机号是否注册’
        $userModel = new UserModel();
        $userInfo = $userModel->getInfoByPhone($phone);
        if (!$userInfo){
            return $this->failed(_1000101);
        }

        $data = array();
        $data['password'] = md5($password);
        $data['updated_at'] = time();
        if (!$userModel->resetPass($userInfo['id'], $data)){
            return $this->failed(_1000002);
        }
        */
        $inputdata['login_name'] = $phone;
        $inputdata['password'] = $password;
        $inputdata['verification_code'] = $code;
        $result_data = postData('modifyPassword', $terminal, $inputdata);

        if ($result_data['code'] != 0) {
            return $this->failed($result_data['msg']);
        }

        $this->succ();
    }

    /**
     * 手机号注册
     */
    public function registerAction()
    {
        $input = $this->input();
        $rule = array(
            'phone' => 'required|phone',
            'password' => 'required',
            'repassword' => 'required',
            'code' => 'required',
            'terminal' => 'required',
        );

        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }

        $userModel = new UserModel();
        //ip、黑名单拦截
        if (!$userModel->sysFilter(BAN_TYPE_LOGIN)) {
            return $this->failed('服务器忙', RCODE_BUSY);
        }

        if (!checkPassword($input->json_stream('password'))) {
            return $this->failed(_1000007);
        }

        $phone = $input->json_stream("phone");
        $password = $input->json_stream("password");
        $repassword = $input->json_stream("repassword");
        $code = $input->json_stream("code");
        //$user_id = $input->json_stream("user_id");
        $nickname = $input->json_stream("nickname");
        $avatar = $input->json_stream("avatar")??toUrl(DEF_AVATAR);
        $invite_id= $input->json_stream("invite_id");
        if ($password !== $repassword) {
            return $this->failed(_1000103);
        }

        //验证码
        /*$smsModel = new SmsModel();
        //$userModel->sendSms($input->json_stream("phone"));
        $sms_info = $smsModel->getOne($phone, 1, $code);
        if (!$sms_info) {
            return $this->failed(_1000202);
        }

        if ($sms_info['status'] == 0) {
            return $this->failed(_1000202);
        }

        if ($sms_info['is_used'] == 1) {
            return $this->failed(_1000202);
        }

        if (time() > intval($sms_info['c_time'] + $sms_info['expire'])) {
            return $this->failed(_1000205);
        }

        if (!$smsModel->updateUsed($sms_info['id'])) {
            return $this->failed(_1000003);
        }

        //验证手机号是否注册
        $userInfo = $userModel->getInfoByPhone($phone);
        if ($userInfo) {
            return $this->failed(_1000102);
        }*/


        $input_data['login_name'] = $phone;
        $input_data['password'] = $password;
        $input_data['recommender'] = $invite_id;
        $input_data['verification_code'] = $code;
        $result_data=postData('register', $input->json_stream("terminal"),$input_data);

        if($result_data['code']=="0")
        {
            //获取用户数据
            $data = array();
            $data['wx_unionid']=$result_data['data']['user_id'];
            $data['username'] = $phone;
            $data['password'] = md5($password);
            $data['type'] = 0;
            $data['phone'] = $phone;
            $data['nickname'] = $nickname;
            $data['avatar'] = $avatar;
            if($invite_id!='0' && $invite_id!="")
            {
                $data['invite_id'] = $invite_id;
                $data['invite_type'] = 0;
                $data['invite_time'] = time();
            }
            if (!$userModel->register($data)) {
                return $this->failed(_1000104);
            }

            $this->succ();
        }
        else
        {
            return $this->failed($result_data['msg']);
        }

    }

    /**
     * 导出游客数据临时脚本
     * @throws Exception
     */
    public function dumpAIDataAction()
    {
        $sql = <<<SQL
                select id
                 from ms_user where type= 2 limit 100
                  
SQL;
        $users = $this->DB()->query($sql);
        $redis = PRedis::instance2();

        $usermodel = new UserModel();

        $dataArr = [];
        foreach ($users as $k => $user) {
            $redis->lPush('uid_list', $user['id']);

            //基本数据
            $data = $usermodel->getLoginUserInfo($user['id']);
            $data['u_nickname'] = $this->setAiName($k);
            //财产数据
            $sql = <<<SQL
                    select gold, credit,ticket from ms_user_estate where user_id=? 
SQL;
            $estateData = $this->DB()->query_fetch($sql, [$user['id']]);
            if (!$estateData) {
                $estateData = [
                    'gold' => 0,
                    'credit' => 0,
                    'ticket' => 0
                ];
            }

            $data = array_merge($data, $estateData);
            $redis->set('aidata@' . $user['id'], json_encode($data));
        }
    }

    /**
     * 设置机器人名字
     */
    protected function setAiName($k)
    {
        $str = '初夏久不遇 孤猫与蜀葵 呓人卖梦吗 南北遇东西 孤独像条狗 念及你伤痛 爱我请举手 说爱太烫嘴 萌我一脸血 我没有怪你 爱过那张脸 我嫌你恶心 怪我太迷人 求你别离开 我不想爱了 白微 泽兰 芫荽 麦冬 橘络 款东 八月札 善茬子 抓紧我 你瞒 我装 皖予 缚蓝 佐夏 屋鲤 恹氧 缼唇 劣喜 橙杳 厘渊 湛呓 为她呵暖 少女医生 奈愁里 你女友 不惦记 煽动他 树漏光 忘了说 森梨容 逃走吧 溺你水 丢了愉快 马路表白 阁楼少女 让我难堪 多年以后 又逗我 人质 自嗨 由人 黑猫不睡 会唱歌的骗子 趁着不深 薄荷浓森 鳗鱼大叔 只当观众 青木如夏 梦书当枕 掩饰倦容 只身一人 动人声线 白水煮一切 冘归 再叙愁 几度相拥 以之为隔 借万里青山 害你哭 友难有 让你慌张 青睐与着迷 饮苦酒与江河 三天散 几许心酸 晚风吻尽 有人共享 终于见你古栈道 侬未歇 栖长阶 可共月 清歌九转 渔舟唱晚 不思量 笑给你看 不过惆怅 为谁风露 卸不下喜欢 走近你发尾 轻嗅风中 孤枕难入眠 浮生一梦间 叹花不解言 几回折柳 几度笑拥 折花入酒 薄茶半盏 云已向晚 绕柳 踏歌 温酒乡 清裳浅 瘦尽花骨 她喜茶 聚两眉 满纸谎言 思别想 枯竭 笑沉眼  锁身 少娇娆 甄别 话扎心 你对谁都笑 遭了心 夏色 几分似你 不醒 等凉生 听见风在跑 堪留 未够 擦肩 几分像我 把酒临风 偷尽 七月半 更漏长 十月至 你一笑 一衫青 枕畔 披衣看 向人秋 和好不如初 惊喜是你 是我脸红 与我玩笑 连风都知道 早厌 孤意在眉 嘴脸 引君归 温良如玉  青山作陪 几度新凉 装欢 苦厄 俗客 难为客 尤未归 几顾惊秋 一舟乘 败了桃柳';
        $arr = explode(' ', str_repeat($str, 5));
        return $arr[$k];
    }

    public function updateRobotAction()
    {
        $redis = PRedis::instance2();
        $redis->select(0);
        $keys = $redis->keys("info@*");
        $keyArr = [];
        foreach ($keys as $key) {
            $key = explode('@', $key)[1];
            $keyArr[] = $key;
            if (count($keyArr) == 1000) {
                $this->DB()->update('ms_user', ['type' => 100], 'where id in (' . implode(',', $keyArr) . ')');
                $keyArr = [];
            }
        }

    }

    /**
     * 推广用户注册接口
     */
    public function proUserAction()
    {
        $request = $this->input()->json_stream();
        $user = new UserModel();
        $userId = $user->register($request);

        return $this->succ(['user_id' => $userId]);
    }

    public function updateUserGoldAction()
    {
        $request = $this->input()->json_stream();
        //签名校验
        if ($request['sign'] && $request['sign'] == md5($request['user_id'] . $request['gold'] . 'MszhangY')) {
            $user = new Data\UserModel($request['user_id']);
            $user->addGold($request['gold']);
            return $this->succ();
        }
    }

    /**
     * 绑定手机号码，密码
     */
    public function upUserAction()
    {
        $input = $this->input();
        $rule = array(
            'phone' => 'required|phone',
            'password' => 'required',
            'repassword' => 'required',
            'code' => 'required',
            'user_id' => 'user_id',
        );

        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }

        if (!checkPassword($input->json_stream('password'))) {
            return $this->failed(_1000007);
        }

        $phone = $input->json_stream("phone");
        $password = $input->json_stream("password");
        $repassword = $input->json_stream("repassword");
        $code = $input->json_stream("code");
        $user_id = $input->json_stream("user_id");

        if ($password !== $repassword) {
            return $this->failed(_1000103);
        }

        //验证码
        $smsModel = new SmsModel();
//        $userModel->sendSms($input->json_stream("phone"));
//        $sms_info = $smsModel->getOne($phone, 1, $code);
//        if (!$sms_info){
//            return $this->failed(_1000202);
//        }
//
//        if ($sms_info['status'] == 0){
//            return $this->failed(_1000202);
//        }
//
//        if ($sms_info['is_used'] == 1){
//            return $this->failed(_1000202);
//        }
//
//        if (time() > intval($sms_info['c_time'] + $sms_info['expire'])){
//            return $this->failed(_1000205);
//        }
//
//        if (!$smsModel->updateUsed($sms_info['id'])){
//            return $this->failed(_1000003);
//        }

        //验证手机号是否注册
        $userModel = new UserModel();
        $userInfo = $userModel->getInfoByPhone($phone);
        if ($userInfo) {
            return $this->failed(_1000102);
        }

        //获取用户数据
        $data = array();
        $data['phone'] = $phone;
        $data['password'] = md5($password);
        $data['type'] = 0;

        if (!$userModel->upUser($user_id, $data)) {
            return $this->failed(_1000104);
        }

        $this->succ();
    }

    /**
     * 获取选择头像
     */
    public function getavatarsAction()
    {
        $data = array(
            'http://api.xihucg.cn:8081/rs/default/ms_1.png',
            'http://api.xihucg.cn:8081/rs/default/ms_2.png',
            'http://api.xihucg.cn:8081/rs/default/ms_3.png',
            'http://api.xihucg.cn:8081/rs/default/ms_4.png',
            'http://api.xihucg.cn:8081/rs/default/ms_5.png',
            'http://api.xihucg.cn:8081/rs/default/ms_6.png',
            'http://api.xihucg.cn:8081/rs/default/ms_7.png',
            'http://api.xihucg.cn:8081/rs/default/ms_8.png',
        );
        return $this->succ($data);
    }
}
