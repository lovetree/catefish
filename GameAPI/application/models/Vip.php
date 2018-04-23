<?php

class VipModel extends BaseModel {
    
    private $log_array = [];
    
    public function getVipRule(int $vip_level, bool $one = false) {
        if ($one){
            $sql = <<<SQL
                select vip_level,vip_pay,sign_gold,gold_times,day_gold,emerald,frozen,eagleeye from ms_vip_rule 
                    where vip_level = $vip_level order by vip_level ASC
SQL;
            return $this->DB()->query_fetch($sql);
        }else{
            $sql = <<<SQL
                select vip_level,vip_pay,sign_gold,gold_times,day_gold,emerald,frozen,eagleeye from ms_vip_rule 
                    where vip_level > $vip_level order by vip_level ASC
SQL;
            return $this->DB()->query($sql);
        }
    }
    
    public function _initVipDay(){
        $redis = PRedis::instance();
        $redis->select(R_BENEFITS_DB);
        $redis->hDel(PK_VIP_DAY, $_SESSION['user_id']);
        if ($redis->hExists(PK_VIP_DAY, $_SESSION['user_id'])){
            return false;
        }
        return true;
    }
    
    public function _getVipDay() {
        $redis = PRedis::instance();
        $redis->select(R_BENEFITS_DB);
        $day = $redis->hMget(PK_VIP_DAY, [$_SESSION['user_id']]);
        $day = $day[$_SESSION['user_id']];
        if (!$day){
            $day = $this->baseFind('ms_vip_day', 'user_id=?', array($_SESSION['user_id']), 
                    ['id','day_date']);
            if ($day) $redis->hMset(PK_VIP_DAY, [$_SESSION['user_id'] => json_encode($day)]);
        }else{
            $day = json_decode($day, TRUE);
        }
        return $day;
    }
    
    public function _getVipRule(int $vip = 0, bool $one = false) {
        $redis = PRedis::instance();
        $redis->select(R_BENEFITS_DB);
        $rule = $redis->get(PK_VIP_RULE);
        if (!$redis->exists(PK_VIP_RULE)){
            $rule = $this->getVipRule(0);
            if ($rule){
                $redis->set(PK_VIP_RULE, json_encode($rule));
            }
        }else{
            $rule = json_decode($rule, TRUE);
        }
        
        if ($rule && $vip){
            foreach ($rule as $k=>$v){
                if ($v['vip_level'] == $vip && $one){
                    return $v;
                }
                if ($v['vip_level'] <= $vip){
                    unset($rule[$k]);
                }
            }
        }
        return $rule;
    }
    
    /*
     * 升级vip
     */
    public function upgradeVip(int $user_id) {
        $user = new UserModel();
        $user = $user->getOneUserInfo($user_id, ['user_level','user_pay','level_upgrade']);
        if (!$user || !($user['user_pay'] - $user['level_upgrade'])) return false;
        $vip = $user['user_level'];
        //获取vip配置信息
        $rule = $this->_getVipRule($vip);
        if (!$rule) return false;
        
        //$pay = $user['user_pay'] - $user['level_upgrade']; //剩余可升级
        $pay = $user['user_pay'];
        $vip_level = 0;
        $pay_total = 0;
        foreach ($rule as $v){
            if ($v['vip_pay'] > $pay){
                //$pay_total -= $v['vip_pay'];
                break;
            }
            $pay_total = $v['vip_pay'];
            $vip_level = $v['vip_level'];
        }

        if (!$vip_level) return false;
        $db = $this->DB();
        $db->beginTransaction();
        $log = [];
        $log['user_id'] = $user_id;
        $log['last_vip_level'] = $vip;
        $log['vip_level'] = $vip_level;
        $log['vip_upgrade_pay'] = $pay_total;
        $log['ip'] = ip2long($this->input()->ip_address());
        $log['c_time'] = time();
        if ($this->baseInsert('ms_vip_upgrade_log', $log) == false){
            $db->rollBack();
            return false;
        }


        //修改用户vip等级
        $update = [];
        $update['user_level'] = $vip_level;
        $update['level_upgrade'] = $user['level_upgrade'] + $pay_total;
        try{
            $db->update('ms_user_info', $update, 'user_id = ' . $user_id);
        } catch (Exception $ex){
            $db->rollBack();
            logMessage($ex->getTraceAsString());
            return false;
        }
        
        //修改redis中的vip等级
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $redis->hMset(RK_USER_INFO . $user_id, array('vip'=>$vip_level));
        $db->commit();
        return true;
    }
    
    /**
     * vip信息
     */
    public function vipInfo() {
        $user = new UserModel();
        $userInfo = $user->getOneUserInfo($_SESSION['user_id'], ['user_level','user_pay','level_upgrade']);
        $vip = $userInfo ? ($userInfo['user_level'] + 1) : 1;
        $vip = $vip > 9 ? 9 : $vip;
        //获取vip配置信息
        $rule = $this->_getVipRule($vip, TRUE);
        if (!$rule) return [];
        
        $return = [];
        $return['user_level'] = $userInfo['user_level'];
        $return['next_vip_level'] = $vip;
        $return['vip_pay'] = $rule['vip_pay'];
        $return['user_pay'] = $userInfo['user_pay'] - $userInfo['level_upgrade'];
        return $return;
    }
    
    /**
     * 获取特权信息
     * status 状态 0隐藏 1显示
     * gold 金币
     * user_level 用户vip等级
     */
    public function privilege() {
        $return = [];
        $return['status'] = 0;
        $user = new UserModel();
        $userInfo = $user->getOneUserInfo($_SESSION['user_id'], ['user_level']);
        $return['user_level'] = $userInfo['user_level'];

        if (!$userInfo || $userInfo['user_level'] <=0){
            return $return;
        }
        
        //获取规则
        $rule = $this->_getVipRule($userInfo['user_level'], TRUE);

        if (!$rule) return $return;
        
        $return['gold'] = $rule['day_gold'];
        $return['emerald'] = $rule['emerald'];
        $return['frozen'] = $rule['frozen'];
        $return['hawkeye'] = $rule['eagleeye'];
        
        //判断自己是否已领取

        $day = $this->_getVipDay();
        if ($day){
            if (date('Y-m-d') == $day['day_date']){
                return $return;
            }
        }
        
        //可以领取
        $return['status'] = 1;
        return $return;
    }
    
    /**
     * 每日特权奖励
     */
    public function day() {
        $user = new UserModel();
        $userInfo = $user->getOneUserInfo($_SESSION['user_id'], ['user_level']);
        if (!$userInfo || $userInfo['user_level'] <= 0){
            return _1000701;
        }
        
        //获取规则
        $rule = $this->_getVipRule($userInfo['user_level'], TRUE);
        if (!$rule) return _1000702;
        
        //判断自己是否已领取
        $day = $this->_getVipDay();
        if ($day){
            if (date('Y-m-d') == $day['day_date']){
                return _1000703;
            }
        }
        
        if ($this->_initVipDay() == FALSE){
            return _1000704;
        }
        
        $db = $this->DB();
        $db->beginTransaction();
        
        $log = [];
        $log['user_id'] = $_SESSION['user_id'];
        $log['gold'] = $rule['day_gold'];
        $log['ip'] = ip2long($this->input()->ip_address());
        $log['c_time'] = time();
        if ($this->baseInsert($this->_getDayLogTable(), $log) == false){
            $db->rollBack();
            logMessage($this->_getDayLogTable() . ' 插入失败;' . var_export($log, true));
            return _1000603;
        }
        
        $data = [];
        $data['user_id'] = $_SESSION['user_id'];
        $data['day_date'] = date('Y-m-d');
        if ($day){
            //更新
            $data['u_time'] = time();
            try{
                $db->update('ms_vip_day', $data, 'id = ' . $day['id']);
            } catch (Exception $ex){
                $db->rollBack();
                logMessage($ex->getTraceAsString());
                return _1000704;
            }
        }else{
            //插入
            $data['c_time'] = time();
            if ($this->baseInsert('ms_vip_day', $data) == false){
                $db->rollBack();
                logMessage('ms_vip_day 插入失败;' . var_export($data, true));
                return _1000704;
            }
        }
        
        $r_p = [];
        $r_v = [];
        if ($rule['day_gold']){
            $r_p[] = 'gold';
            $r_v[] = $rule['day_gold'];
            
            $detail = [];
            $detail['user_id'] = $_SESSION['user_id'];
            $detail['count'] = $rule['day_gold'];
            $detail['why'] = "vip每日金币奖励";
            $this->log_array[] = $detail;
        }
        if ($rule['emerald']){
            $r_p[] = 'emerald';
            $r_v[] = $rule['emerald'];
            
            $detail = [];
            $detail['user_id'] = $_SESSION['user_id'];
            $detail['count'] = $rule['emerald'];
            $detail['why'] = "vip每日绿宝石奖励";
            $this->log_array[] = $detail;
        }
        if ($rule['frozen']){
            $r_p[] = 'frozen';
            $r_v[] = $rule['frozen'];
            
            $detail = [];
            $detail['user_id'] = $_SESSION['user_id'];
            $detail['count'] = $rule['frozen'];
            $detail['why'] = "vip每日冰封道具奖励";
            $this->log_array[] = $detail;
        }
        if ($rule['eagleeye']){
            $r_p[] = 'eagleeye';
            $r_v[] = $rule['eagleeye'];
            
            $detail = [];
            $detail['user_id'] = $_SESSION['user_id'];
            $detail['count'] = $rule['eagleeye'];
            $detail['why'] = "vip每日鹰眼道具奖励";
            $this->log_array[] = $detail;
        }
        
        if ($r_p && $r_v){
            if ($this->onChangeUserData($_SESSION['user_id'], $r_p, $r_v) == FALSE){
                $db->rollBack();
                return _1000001;
            }
        }
        
        $db->commit();
        
        
        if ($this->log_array){
            foreach ($this->log_array as $v){
                $this->_addUserLog($_SESSION['user_id'], LOG_ACT_VIP, json_encode($v));
            }
        }
        
        return true;
    }
    
    /**
     * 首次充值礼包
     */
    public function firstrechargeinfo(int $type) {
        $return = [];
        $return['status'] = 0;
        $return['gold'] = 0;
        $return['credit'] = 0;
        $return['emerald'] = 0;
        
        $packageInfo = $this->baseFind('ms_first_recharge_package', 'status = 1 and type=?', [$type], ['package']);
        if (!$packageInfo){
            return $return;
        }
        $package = json_decode($packageInfo['package'], TRUE);
        foreach ($package as $v){
            $return[$v['source']] = $v['count'];
        }
        
        $user_id = $_SESSION['user_id'];
        $user = new UserModel();
        $userInfo = $user->getOneUserInfo($user_id, ['user_pay']);
        if (!$userInfo['user_pay']){
            return $return;
        }
        
        //查询自己是否已经领取过
        $row = $this->DB()->rowCount('ms_first_recharge_package_log', 'user_id = ' . $user_id . ' and type = ' . $type);
        if ($row['count']){
            $return['status'] = 2; //领取过，需隐藏
            return $return;
        }
        
        $return['status'] = 1;
        return $return;
    }
    
    /**
     * 领取首次充值礼包
     */
    public function firstrecharge(int $type) {
        $user_id = $_SESSION['user_id'];
        $user = new UserModel();
        $userInfo = $user->getOneUserInfo($user_id, ['user_pay']);
        if (!$userInfo['user_pay']){
            return _1000705;
        }
        
        //查询自己是否已经领取过
        $row = $this->DB()->rowCount('ms_first_recharge_package_log', 'user_id = ' . $user_id . ' and type = ' . $type);
        if ($row['count']){
            return _1000706;
        }
        
        //开始领取
        //第一步获取首次礼包信息
        //第二步添加日志
        //给予
        $packageInfo = $this->baseFind('ms_first_recharge_package', 'status = 1 and type=?', [$type], ['package']);
        if (!$packageInfo){
            return _1000707;
        }
        
        $log = [];
        $log['user_id'] = $user_id;
        $log['type'] = $type;
        $log['package'] = $packageInfo['package'];
        $log['ip'] = ip2long($this->input()->ip_address());
        $log['c_time'] = time();
        
        $db = $this->DB();
        $db->beginTransaction();
        if ($db->insert('ms_first_recharge_package_log', $log) == FALSE){
            $db->rollBack();
            logMessage('ms_first_recharge_package_log 插入失败;' . var_export($log, true));
            return _1000708;
        }
        
//        if ($this->baseInsert('ms_first_recharge_package_log', $log) == FALSE){
//            return _1000708;
//        }
        
        $package = json_decode($packageInfo['package'], TRUE);
        
        $r_p = [];
        $r_v = [];
        foreach ($package as $v){
            $detail = [];
            $detail['user_id'] = $_SESSION['user_id'];
            $detail['count'] = $v['count'];
            $r_v[] = $v['count'];
            switch ($v['source']){
                case "gold":
                    $r_p[] = 'gold';
                    //$this->onChangeUserGold($user_id, $v['count']);
                    $detail['why'] = "首充金币奖励";
                    //$this->_addUserLog($_SESSION['user_id'], LOG_ACT_VIP, json_encode($detail));
                    break;
                case "credit":
                    $r_p[] = 'credit';
                    //$this->onChangeUserCredit($user_id, $v['count']);
                    //$detail = [];
                    //$detail['user_id'] = $_SESSION['user_id'];
                    //$detail['count'] = $v['count'];
                    $detail['why'] = "首充钻石奖励";
                    //$this->_addUserLog($_SESSION['user_id'], LOG_ACT_VIP, json_encode($detail));
                    break;
                case "emerald":
                    $r_p[] = 'emerald';
                    //$this->onChangeUserEmerald($user_id, $v['count']);
                    //$detail = [];
                    //$detail['user_id'] = $_SESSION['user_id'];
                    //$detail['count'] = $v['count'];
                    $detail['why'] = "首充绿宝石奖励";
                    //$this->_addUserLog($_SESSION['user_id'], LOG_ACT_VIP, json_encode($detail));
                    break;
            }
            $this->log_array[] = $detail;
        }
        
        if ($this->onChangeUserData($_SESSION['user_id'], $r_p, $r_v) == FALSE){
            $db->rollBack();
            return _1000708;
        }
        $db->commit();
        
        if ($this->log_array){
            foreach ($this->log_array as $v){
                $this->_addUserLog($_SESSION['user_id'], LOG_ACT_VIP, json_encode($v));
            }
        }
        
        return true;
    }
    
    protected function _getDayLogTable() {
        $table_name = 'ms_vip_day_log' . date('Ym');
        //初始化数据表，如果不存在则新建
        $sql = 'create table if not exists ' . $table_name . ' like ms_vip_day_log';
        $this->DB()->exec($sql);
        return $table_name;
    }
}
