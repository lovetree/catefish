<?php

class SignModel extends BaseModel {
    
    public function _getFishSign(){
        $redis = PRedis::instance();
        $redis->select(R_BENEFITS_DB);
        $sign = $redis->hMget(PK_FISH_SIGN, [$_SESSION['user_id']]);
        $sign = $sign[$_SESSION['user_id']];
        if (!$sign){
            $sign = $this->baseFind('ms_fish_sign', 'user_id=?', array($_SESSION['user_id']), 
                    ['id','last_sign_date','online_day_times']);
            if ($sign) $redis->hMset(PK_FISH_SIGN, [$_SESSION['user_id'] => json_encode($sign)]);
        }else{
            $sign = json_decode($sign, TRUE);
        }
        return $sign;
    }
    
    public function _initFishSign(){
        $redis = PRedis::instance();
        $redis->select(R_BENEFITS_DB);
        $redis->hDel(PK_FISH_SIGN, $_SESSION['user_id']);
        if ($redis->hExists(PK_FISH_SIGN, $_SESSION['user_id'])){
            return false;
        }
        return true;
    }
    
    /**
     * 获取捕鱼签到信息，每7天一个循环
     */
    public function fishSignInfo(){
        $vip = new VipModel();
        $rule = $vip->_getVipRule(0);
        if (!$rule) return [];
        
        $return = [];
        $return['sign_status'] = 1; //不可领取 1可领取
        //获取自己现在在第几天，标记已领取的记录
        $sign = $this->_getFishSign();
        $online_day_times = 0;
        if ($sign){
            if (date('Y-m-d') == $sign['last_sign_date']){
                $return['sign_status'] = 0;
            }
            $online_day_times = $sign['online_day_times'];
            if (date('Y-m-d') != $sign['last_sign_date'] && $online_day_times == 7){
                $online_day_times = 0;
            }
        }
        foreach ($rule as $k => $v){
            $rule[$k]['status'] = 1;
            if ($v['vip_level'] > $online_day_times){
                $rule[$k]['status'] = 0;
            }
        }
        
        $return['list'] = $rule;
        return $return;
    }
    
    /**
     * 捕鱼签到
     */
    public function fishSign() {
        $vip = new VipModel();
        $rule = $vip->_getVipRule(0);

        var_dump($rule);
        if (!$rule) return _1000509;
        $sign = $this->_getFishSign();
        $online_day_times = 0;
        if ($sign){
            if (date('Y-m-d') == $sign['last_sign_date']){
                return _1000509;
            }
            $online_day_times = $sign['online_day_times'];
            if (date('Y-m-d') != $sign['last_sign_date'] && $online_day_times == 7){
                $online_day_times = 0;
            }
        }
        $online_day_times += 1;
        $user = new UserModel();
        $userInfo = $user->getOneUserInfo($_SESSION['user_id'], ['user_level']);
        $gold_times = 1;
        $gold = 0;
        //vip奖励倍数
        foreach ($rule as $v){
            if ($online_day_times == $v['vip_level']){
                if ($userInfo['user_level'] >= $online_day_times){
                    $gold_times = intval($v['gold_times']);
                }
                $gold = intval($v['sign_gold']);
            }
        }

        //先删除掉记录
        if ($this->_initFishSign() == false){
            return _1000509;
        }

        $db = $this->DB();
        $db->beginTransaction();
        $log = [];
        $log['user_id'] = $_SESSION['user_id'];
        $log['user_level'] = intval($userInfo['user_level']);
        $log['ip'] = ip2long($this->input()->ip_address());
        $log['days'] = $online_day_times;
        $log['gold'] = $gold * $gold_times;
        $log['c_time'] = time();
        if ($this->baseInsert($this->_getFishSignLogTable(), $log) == false){
            $db->rollBack();
            logMessage($this->_getFishSignLogTable() . ' 插入失败;' . var_export($log, true));
            return _1000509;
        }

        //插入记录
        $beans = [];
        $beans['last_sign_date'] = date('Y-m-d');
        $beans['online_day_times'] = $online_day_times;
        $beans['user_id'] = $_SESSION['user_id'];
        if ($sign){
            //更新
            $beans['u_time'] = time();
            try{
                $db->update('ms_fish_sign', $beans, 'id = ' . $sign['id']);
            } catch (Exception $ex){
                $db->rollBack();
                logMessage($ex->getTraceAsString());
                return _1000509;
            }
        }else{
            //插入
            $beans['c_time'] = time();
            if ($this->baseInsert('ms_fish_sign', $beans) == false){
                $db->rollBack();
                logMessage('ms_fish_sign 插入失败;' . var_export($beans, true));
                return _1000509;
            }
        }

        //修改用户金币变化
        if (!$this->onChangeUserGold($_SESSION['user_id'], $gold * $gold_times)){
            $db->rollBack();
            return _1000001;
        }
        
        $db->commit();
        
        $detail = [];
        $detail['user_id'] = $_SESSION['user_id'];
        $detail['count'] = $gold * $gold_times;
        $detail['why'] = "领取捕鱼每日签到奖励";
        $this->_addUserLog($_SESSION['user_id'], LOG_ACT_SIGN, json_encode($detail));
        
        return true;
    }
    
    protected function _getFishSignLogTable() {
        $table_name = 'ms_fish_sign_log' . date('Ym');
        //初始化数据表，如果不存在则新建
        $sql = 'create table if not exists ' . $table_name . ' like ms_fish_sign_log';
        $this->DB()->exec($sql);
        return $table_name;
    }
}
