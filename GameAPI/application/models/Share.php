<?php

class ShareModel extends BaseModel {
    
    public function _shareRule(){
        //从redis中获取分享配置信息
        $redis = PRedis::instance();
        $redis->select(R_BENEFITS_DB);
        $share_rule = $redis->hMget(PK_SHARE_RULE, array('new_gold', 'new_time_limit', 
            'new_match', 'old_share_gold', 'old_share_interval', 'old_share_succ', 'invite_valid_time', 'invite_url'));
        if (!$redis->exists(PK_SHARE_RULE)){
            $share_rule = $this->getShareRule();
            if (!$share_rule){
                return _1000511;
            }
            $redis->hMset(PK_SHARE_RULE, $share_rule);
        }
        return $share_rule;
    }
    
    public function _userShare($key){
        //获取用户分享数据
        $redis = PRedis::instance();
        $redis->select(R_BENEFITS_DB);
        $user_share = $redis->hMget($key, array('id', 'user_id', 'new_get_status', 'share_count',
            'new_gold', 'last_share_time', 'invite_count', 'invite_gold_count', 'share_gold'));
        if (!$redis->exists($key)){
            $user_share = $this->getOneShare($_SESSION['user_id']);
            if ($user_share){
                $redis->hMset($key, $user_share);
            }
        }
        return $user_share;
    }
    
    public function share() {
        //从redis中获取分享配置信息
        $share_rule = $this->_shareRule();
        if (!is_array($share_rule)) return $share_rule;
        
        //获取用户分享数据
        $key = PK_USER_SHARE . $_SESSION['user_id'];
        $user_share = $this->_userShare($key);
        
        //获取用户的创建时间和推广人id号
        $user = new UserModel();
        $user_info = $user->getOneUser($_SESSION['user_id'], array('created_at', 'invite_type', 
            'invite_id', 'invite_time'));
        
        //返回数据
        $return = [];
        $return['new_status'] = 3; //0:隐藏 1绑定可领取 2绑定不可领取 3新用户显示需绑定推荐人
        $return['invite_id'] = $user_info['invite_id'] ?? 0; //邀请人id号码
        $return['new_time_limit'] = $share_rule['new_time_limit']; //可领取新用户奖励的时间限制，单位小时
        $return['new_gold'] = $share_rule['new_gold']; //新玩家首次受邀请领取的金币
        $return['new_match'] = $share_rule['new_match']; //新玩家需要打牌的次数即可领取，如果通过分享链接登录，无需次条件
        $return['old_share_gold'] = $share_rule['old_share_gold']; //老玩家每次分享获取的金币
        $return['old_share_interval'] = 0; //老玩家分享获取金币的时间间隔
        $return['old_share_succ'] = $share_rule['old_share_succ']; //老玩家邀请成功一个获取的金币
        $return['invite_count'] = $user_share ? $user_share['invite_count'] : 0; //邀请的总人数
        $return['invite_gold_count'] = $user_share ? $user_share['invite_gold_count'] : 0; //邀请获取的总金币数
        //邀请分享的url
//        $shareUrl = sprintf(config_item('wxgzh.page_auth_url'), config_item('wxgzh.appid'), 'http%3a%2f%2fgamedq.com%2fWX%2fgzh%2fwxc',
//            'share');
        $return['share_url'] = 'http://www.yueyouqp.com/?from_user=' . $_SESSION['user_id'];
        $return['share_interval'] = $share_rule['old_share_interval'] * 3600;
        
        if ($user_share){
            $return['old_share_interval'] = $share_rule['old_share_interval'] * 3600;
            //$return['old_share_interval'] = $this->timeToString($share_rule['old_share_interval']);
            //计算上次领取的时间，以及计算剩余时间
            $last_time = intval($user_share['last_share_time']) + intval($share_rule['old_share_interval']) * 3600 - time();
            if ($last_time > 0){
                //折算成时间
                //$return['old_share_interval'] = $this->timeToString($last_time);
                $return['old_share_interval'] = $last_time;
            }
        }
        
        //判断自己是否是新用户，还是老用户，新用户的定义为：限制时间范围以内，且没有绑定邀请人的关系
        if ($user_info['invite_id']){
            //推荐用户
            $return['new_status'] = 2; //推荐用户,状态不可领取
            if ($user_info['invite_type'] == 1){
                //手动添加推荐人的用户需要游戏满足多少次数之后才能领取
                 $return['new_match'] = GamelogModel::getGameCount($_SESSION['user_id']);
                 if($return['new_match'] >=4){
                     $return['new_status'] = 1;
                 }
            }else{
                $return['new_status'] = 1;
            }
        }
        
        if ($return['new_status'] == 1){
            //判断自己是否领取过，如果领取了需隐藏
            if ($user_share && $user_share['new_get_status']){
                $return['new_status'] = 0; //隐藏
            }
        }
        
        if (time() > ($user_info['created_at'] + $share_rule['new_time_limit'] * 3600)){
            //过期需隐藏
            $return['new_status'] = 0; //隐藏
        }
        
        return $return;
    }
    
    /**
     * 新用户领取邀请奖励
     */
    public function newreward(){
        //从redis中获取分享配置信息
        $share_rule = $this->_shareRule();
        if (!is_array($share_rule)) return $share_rule;
        
        //获取用户分享数据
        $key = PK_USER_SHARE . $_SESSION['user_id'];
        $user_share = $this->_userShare($key);
        
        //获取用户的创建时间和推广人id号
        $user = new UserModel();
        $user_info = $user->getOneUser($_SESSION['user_id'], array('created_at', 'invite_type', 
            'invite_id', 'invite_time'));
        
        if (!$user_info['invite_id']){
            return _1000515;
        }
        
        if (time() > ($user_info['created_at'] + $share_rule['new_time_limit'] * 3600)){
            //过期需隐藏
            return _1000513;
        }
        
        //判断自己是否领取过，如果领取了需隐藏
        if ($user_share && $user_share['new_get_status']){
            return _1000516;
        }
        
        //判断自己是否是新用户，还是老用户，新用户的定义为：限制时间范围以内，且没有绑定邀请人的关系
//        if ($user_info['invite_type'] == 1){
//            //手动添加推荐人的用户需要游戏满足多少次数之后才能领取
//            return _1000517;
//        }
        
        //开始领取,开始之前先删除掉redis中的key值
        if (!$this->baseDelRedis(R_BENEFITS_DB, $key)){
            return _1000518;
        }
        
//        $redis = PRedis::instance();
//        $redis->select(R_GAME_DB);
//        if (!$redis->exists(RK_USER_INFO . $_SESSION['user_id'])) {
//            return _1000001;
//        }
//        $user_info = $redis->hMget(RK_USER_INFO . $_SESSION['user_id'], array('gold','vip'));
        
        $db = $this->DB();
        $db->beginTransaction();
        
        $share = [];
        $share['new_get_status'] = 1;
        $share['new_gold'] = $share_rule['new_gold'];
        $user = new \Data\UserModel($_SESSION['user_id']);
        $share['gold_be_new'] = $user->get('gold');
        $share['user_id'] = $_SESSION['user_id'];
        if ($user_share){
            //更新
            $share['u_time'] = time();
            try{
                $db->update('ms_benefits_share', $share, 'id = ' . $user_share['id']);
            } catch (Exception $ex){
                $db->rollBack();
                logMessage($ex->getTraceAsString());
                return _1000001;
            }
        }else{
            //插入
            $share['c_time'] = time();
            if ($db->insert('ms_benefits_share', $share) == false){
                $db->rollBack();
                logMessage('ms_benefits_share 插入失败;' . var_export($share, true));
                return _1000001;
            }
        }
        
        if (!$this->onChangeUserGold($_SESSION['user_id'], $share_rule['new_gold'])){
            $db->rollBack();
            return _1000001;
        }
        
//        $redis->select(R_GAME_DB);
//        $res = $redis->hMset(RK_USER_INFO . $_SESSION['user_id'], array('gold'=>($user_info['gold'] + $share_rule['new_gold'])));
//        if ($res != TRUE){
//            $db->rollBack();
//            return _1000001;
//        }
        
        $db->commit();

        $detail = [];
        $detail['user_id'] = $_SESSION['user_id'];
        $detail['count'] = $share_rule['new_gold'];
        $detail['why'] = "新用户领取邀请奖励";
        $this->_addUserLog($_SESSION['user_id'], LOG_ACT_SHARE, json_encode($detail));

        return true;
    }
    /**
     * 分享成功
     */
    public function shareSucc() {
        $share_rule = $this->_shareRule();
        if (!is_array($share_rule)) return $share_rule;
        
        //获取用户分享数据
        $key = PK_USER_SHARE . $_SESSION['user_id'];
        $user_share = $this->_userShare($key);
        
        if ($user_share){
            //检查该用户是否能领取分享数据
            //计算上次领取的时间，以及计算剩余时间
            $last_time = intval($user_share['last_share_time']) + intval($share_rule['old_share_interval']) * 3600 - time();
            if ($last_time > 0){
                //折算成时间
                return _1000519;
            }
        }

        //首次分享才奖励金币
        if($user_share['share_count'] > 1){
            return '初次分享奖励已领取';
        }

        //开始领取金币，领取之前，先删除掉key值
        //开始领取,开始之前先删除掉redis中的key值
        if (!$this->baseDelRedis(R_BENEFITS_DB, $key)){
            return _1000518;
        }
        
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        if (!$redis->exists(RK_USER_INFO . $_SESSION['user_id'])) {
            return _1000001;
        }
        $user_info = $redis->hMget(RK_USER_INFO . $_SESSION['user_id'], array('gold','vip'));
        
        $db = $this->DB();
        $db->beginTransaction();
        
        $share = [];
        $share['last_share_time'] = time();
        $share['share_gold'] = $share_rule['old_share_gold'];
        $user = new \Data\UserModel($_SESSION['user_id']);
        $share['gold_be_share'] = $user->get('gold');
        $share['user_id'] = $_SESSION['user_id'];
        $share['share_count'] = 1;
        if ($user_share){
            //更新
            $share['u_time'] = time();
            $share['share_gold'] += $user_share['share_gold'];
            $share['share_count'] += $user_share['share_count'];
            try{
                $db->update('ms_benefits_share', $share, 'id = ' . $user_share['id']);
            } catch (Exception $ex){
                $db->rollBack();
                logMessage($ex->getTraceAsString());
                return _1000001;
            }
        }else{
            //插入
            $share['c_time'] = time();
            if ($db->insert('ms_benefits_share', $share) == false){
                $db->rollBack();
                logMessage('ms_benefits_share 插入失败;' . var_export($share, true));
                return _1000001;
            }
        }
        
        if (!$this->onChangeUserGold($_SESSION['user_id'], $share_rule['old_share_gold'])){
            $db->rollBack();
            return _1000001;
        }
        
//        $redis->select(R_GAME_DB);
//        $res = $redis->hMset(RK_USER_INFO . $_SESSION['user_id'], array('gold'=>($user_info['gold'] + $share_rule['old_share_gold'])));
//        if ($res != TRUE){
//            $db->rollBack();
//            return _1000001;
//        }
        
        $db->commit();
        
        $detail = [];
        $detail['user_id'] = $_SESSION['user_id'];
        $detail['count'] = $share_rule['old_share_gold'];
        $detail['why'] = "老用户分享成功";
        $this->_addUserLog($_SESSION['user_id'], LOG_ACT_SHARE, json_encode($detail));
        
        return true;
    }
    
    /**
     * 老用户点击领取成功邀请的奖励
     * return string=错误信息， true成功
     */
    public function oldreward(int $id) {
        $reward = $this->baseFind('ms_benefits_share_log', 'id = ? and invite_id = ?', array($id, $_SESSION['user_id']));
        if (!$reward || $reward['status']){
            return _1000520;
        }
        
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        if (!$redis->exists(RK_USER_INFO . $_SESSION['user_id'])) {
            return _1000001;
        }
        $user_info = $redis->hMget(RK_USER_INFO . $_SESSION['user_id'], array('gold','vip'));
        
        $db = $this->DB();
        $db->beginTransaction();
        
        try{
            $db->update('ms_benefits_share_log', array('status'=>1, 'u_time'=> time(), 'ip'=>$this->input()->ip_address()), 
                    'id = ' . $reward['id']);
        } catch (Exception $ex){
            $db->rollBack();
            logMessage($ex->getTraceAsString());
            return _1000518;
        }
        
        if (!$this->onChangeUserGold($_SESSION['user_id'], $reward['gold'])){
            $db->rollBack();
            return _1000001;
        }
        
//        $res = $redis->hMset(RK_USER_INFO . $_SESSION['user_id'], array('gold'=>($user_info['gold'] + $reward['gold'])));
//        if ($res != TRUE){
//            $db->rollBack();
//            return _1000001;
//        }
        
        $db->commit();
        
        $detail = [];
        $detail['benefits_share_log_id'] = $id;
        $detail['count'] = $reward['gold'];
        $detail['why'] = "老用户领取邀请成功的奖励";
        $this->_addUserLog($_SESSION['user_id'], LOG_ACT_SHARE, json_encode($detail));
        
        return TRUE;
    }
    
    /**
     * 检测是否具有绑定关系
     */
    public function checkInvite(string $openId) {
        $redis = PRedis::instance();
        $redis->select(R_BENEFITS_DB);
        $key = PK_USER_SHARE_INVITE . $openId;
        if ($redis->exists($key)){
            $invite_id = $redis->get($key);
            //查询该推广人是否存在
            $db = $this->DB();
            $user_count = $db->rowCount('ms_user', 'id=' . $invite_id);
            if ($user_count['count']){
                return $invite_id;
            }
        }
        return false;
    }
    
    /**
     * 插入邀请记录
     */
    public function insertInvite(int $invite_id, int $user_id) : bool{
        $share_rule = $this->_shareRule();
        if (is_array($share_rule)){
            $db = $this->DB();
            $share_log = [];
            $share_log['invite_id'] = $invite_id;
            $share_log['user_id'] = $user_id;
            $share_log['gold'] = $share_rule['old_share_succ'];
            $share_log['ip'] = $this->input()->ip_address();
            $share_log['c_time'] = time();
            if ($db->insert('ms_benefits_share_log', $share_log) == false){
                logMessage('ms_benefits_share_log 插入失败;' . var_export($share, true));
                return false;
            }
            
            //更新自己的信息
            $key = PK_USER_SHARE . $invite_id;
            $user_share = $this->_userShare($key);
            $share = [];
            $share['user_id'] = $invite_id;
            $share['invite_count'] = 1;
            $share['invite_gold_count'] = 0;
            if ($user_share){
                //更新
                $share['u_time'] = time();
                $share['invite_count'] += $user_share['invite_count'];
                $share['invite_gold_count'] += $user_share['invite_gold_count'];
                try{
                    $db->update('ms_benefits_share', $share, 'id = ' . $user_share['id']);
                } catch (Exception $ex){
                    logMessage($ex->getTraceAsString());
                    return false;
                }
            }else{
                //插入
                $share['c_time'] = time();
                if ($db->insert('ms_benefits_share', $share) == false){
                    logMessage('ms_benefits_share 插入失败;' . var_export($share, true));
                    return false;
                }
            }
            return true;
        }
        
        return false;
    }
    
    public function inviteList(int $user_id){
        $sql = <<<SQL
                select s.id,s.gold, s.status,from_unixtime(s.c_time) as date, u.nickname from ms_benefits_share_log as s 
                    left join ms_user as u on s.user_id = u.id where s.invite_id = ? order by id DESC
SQL;
        return $this->DB()->query($sql, array($user_id));
    }
    
    private function timeToString(int $time) : string{
        $hour = intval($time/3600);
        $i = intval(($time - $hour*3600)/60);
        $s = $time - $hour*3600 - $i*60;
        return sprintf("%02d",$hour) . ":" . sprintf("%02d",$i) . ":" . sprintf("%02d",$s);
    }
    
    public function getOneShare(int $user_id) {
        $sql = <<<SQL
                select id,user_id,new_get_status,new_gold,last_share_time,invite_count,invite_gold_count,share_count  
                    from ms_benefits_share where user_id = ? limit 1
SQL;
        return $this->DB()->query_fetch($sql, array($user_id));
    }
    
    public function getShareRule() {
        $sql = <<<SQL
                select id,new_gold,new_time_limit,new_match,old_share_gold,
                    old_share_interval,old_share_succ,invite_valid_time,invite_url 
                    from ms_benefits_share_rule limit 1
SQL;
        return $this->DB()->query_fetch($sql);
    }

    /**
     *获取新手奖励金币
     */
    public function getNewrewardLog($page, $pageSize,$userId, $startDate, $endDate){
        $offset = ($page-1) * $pageSize;
        $sql = "select 
                    new_gold,
                    gold_be_new,
                    c_time
                from ms_benefits_share 
                where user_id = ?
                and c_time >= ? 
                and c_time < ?
                limit $offset, $pageSize";

        $newRewardLog = self::DB()->query($sql, [$userId, $startDate, $endDate]);

        $flowArr = [];
        if(!empty($newRewardLog)){
            foreach ($newRewardLog as $item){
                $flowArr[] = [
                    'desc' => '新手金币奖励',
                    'created_at' => $item['c_time'],
                    'change_gold' => $item['new_gold'],
                    'total_gold' => $item['new_gold'] + $item['gold_be_new']
                ];
            }
        }
        return $flowArr;
    }

    /**
     *获取初次分享奖励金币
     */
    public function getSharerewardLog($page, $pageSize,$userId, $startDate, $endDate){
        $offset = ($page-1) * $pageSize;
        $sql = "select 
                    share_gold,
                    gold_be_share,
                    last_share_time
                from ms_benefits_share 
                where user_id = ?
                and last_share_time >= ? 
                and last_share_time < ?
                limit $offset, $pageSize";

        $shareRewardLog = self::DB()->query($sql, [$userId, $startDate, $endDate]);

        $flowArr = [];
        if(!empty($shareRewardLog)){
            foreach ($shareRewardLog as $item){
                $flowArr[] = [
                    'desc' => '首次分享金币奖励',
                    'created_at' => $item['last_share_time'],
                    'change_gold' => $item['share_gold'],
                    'total_gold' => $item['share_gold'] + $item['gold_be_share']
                ];
            }
        }

        return $flowArr;
    }
}
