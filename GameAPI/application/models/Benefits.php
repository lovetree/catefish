<?php

class BenefitsModel extends BaseModel {
    
    /**
     * 更新天天福利信息
     * @return bool；true=成功
     */
    public function getBenefitsList(int $gold) {
        $data = $this->getList(array('id','icon','title','info','button_name','button_type','click_type','relation_id'));
        if ($data){
            foreach ($data as $k=>$v){
                $data[$k]['button_status'] = 1;
                if ($v['button_type'] == 1){
                    continue;
                }
                switch (intval($v['click_type'])){
                    case 0:
                        //补助，查询补助的信息
                        $benefits_subsidy = $this->getOneSubsidy($v['relation_id']);
                        if (!$benefits_subsidy){
                            return [];
                        }
                        
                        //获取补助了多少次
                        $subsidy_count = $this->getSubsidyCount($_SESSION['user_id'], $v['id']);
                        $subsidy_count = intval($subsidy_count['count']);
                        $data[$k]['title'] = sprintf($v['title'] . "（剩余%s次，共%s次）", 
                                $benefits_subsidy['number'] - $subsidy_count, $benefits_subsidy['number']);
                        //是否能够点击
                        if ($gold >= $benefits_subsidy['conditions']){
                            $data[$k]['button_status'] = 0;
                        }
                        
                        break;
                    case 1:
                        break;
                }
            }
        }
        
        return $data;
    }
    
    /**
     * 领取福利补贴
     */
    public function insertSubsidy(array $params) {
        $db = $this->DB();
        $db->beginTransaction();
        
        //插入日志
        $log = [];
        $log['user_id'] = $params['user_id'];
        $log['benefits_id'] = $params['benefits_id'];
        $log['gold'] = $params['gold'];
        $log['ip'] = ip2long($this->input()->ip_address());
        $log['c_time'] = time();
        if ($db->insert('ms_benefits_subsidy_log', $log) == false){
            $db->rollBack();
            logMessage('ms_benefits_subsidy_log 插入失败;' . var_export($log, true));
            return false;
        }
        
        //修改用户redis中的数据
        if (!$this->onChangeUserGold($params['user_id'], $params['gold'])){
            $db->rollBack();
            return false;
        }
        
//        $redis = PRedis::instance();
//        $redis->select(R_GAME_DB);
//        $user_info = $redis->hMget(RK_USER_INFO . $params['user_id'], array('gold'));
//        $res = $redis->hMset(RK_USER_INFO . $params['user_id'], array('gold'=>($user_info['gold'] + $params['gold'])));
//        if ($res === TRUE){
//            $db->commit();
//            return true;
//        }
        
        $db->commit();
        
        $detail = [];
        $detail['benefits_id'] = $params['benefits_id'];
        $detail['count'] = $params['gold'];
        $detail['why'] = "领取破产补助金币";
        $this->_addUserLog($params['user_id'], LOG_ACT_SUBSIDY, json_encode($detail));
        
        return true;
    }
    
    /**
     * 检测用户是否有新的福利没有领取
     */
    public function refresh() {
        //第一步获取所有的福利信息
        //第二步遍历处理所有的福利规则，存在含有福利未领取，返回true，否则返回false
        $data = $this->getList(array('id','icon','title','info','button_name','button_type','click_type','relation_id'));
        if (!$data){
            return false;
        }
        
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $user_info = $redis->hMget(RK_USER_INFO . $_SESSION['user_id'], array('gold'));
        
        $db = $this->DB();
        foreach ($data as $v){
            switch ($v['button_type']){
                case 0:
                    //触发事件
                    switch ($v['click_type']){
                        case 0:
                            //补助福利
                            if (!$user_info){
                                continue;
                            }
                            $subsidy = $this->getOneSubsidy($v['relation_id']);
                            if (!$subsidy){
                                continue;
                            }

                            //检测自己是否能够领取
                            if ($user_info['gold'] >= $subsidy['conditions']){
                                continue;
                            }
                            
                            //检测自己领取的次数
                            $subsidy_count = $this->getSubsidyCount($_SESSION['user_id'], $v['id']);
                            if (intval($subsidy_count['count']) >= $subsidy['number']){
                                continue;
                            }
                            
                            return true;
                        default :
                    }
                    break;
                case 1:
                    //跳转页面
                    switch ($v['click_type']){
                        case 0:
                            //邀请好友送金币
                            $count = $db->rowCount('ms_benefits_share_log', 'status=0 and invite_id=' . $_SESSION['user_id']);
                            if ($count['count']){
                                return true;
                            }
                            break;
                        case 1:
                            //每日签到奖励
                            $signInfo = $this->signinfo();
                            if ($signInfo && $signInfo['sign_extra']){
                                $status = array_column($signInfo['sign_extra'], 'status');
                                if (in_array(1, array_values($status))){
                                    return true;
                                }
                            }
                            break;
                        case 2:
                            //参与活动
                            break;
                        default :
                    }
                    break;
                default :
            }
        }
        
        return false;
    }
    
    /**
     * 获取签到信息
     * 已redis数据为基准
     */
    public function signinfo() {
        $redis = PRedis::instance();
        $redis->select(R_BENEFITS_DB);
        $sign_rule = $redis->hMget(PK_SIGN_RULE, array('day_gold', 'vip', 'extra_rule', 'sign_desc'));
        if (!$redis->exists(PK_SIGN_RULE)){
            $sign_rule = $this->getSignRule();
            if (!$sign_rule){
                return false;
            }
            $redis->hMset(PK_SIGN_RULE, $sign_rule);
        }
        
        $date = date("Y-m");
        $key = PK_USER_SIGN . $_SESSION['user_id'];
        $user_sign = $redis->hMget($key, array('sign_date','accumulate_count','sign_last_date','record','reward'));
        if ($user_sign && ($user_sign['sign_date'] != $date)){
            $user_sign = [];
            $redis->del($key);
        }
        
        //返回数据
        $return = [];
        $return['is_sign'] = 0; //今天是否签到0未签到 1已签到
        $return['sign_count'] = $user_sign ? $user_sign['accumulate_count'] : 0; //本月签到次数
        $return['sign_desc'] = $sign_rule['sign_desc']; //签到说明
        $return['sign_date'] = $user_sign ? $user_sign['record'] : ""; //本月签到日期，字符串显示，已，号分割
        $return['sign_extra'] = [];
        
        //需要重构规则中的数据，规则列表，阔别次数，是否可领取状态
        $sign_extra = [];
        $rule = json_decode($sign_rule['extra_rule'], TRUE);
        foreach ($rule as $v){
            $sign_extra[] = [
                'number' => $v['number'],
                'status' => 0  //可领取状态0不可领取1可领取
            ];
        }
        
        $reward = $user_sign ? $user_sign['reward'] : "";
        
        if (!$user_sign){
            //从数据库中查询单天是否签到，累积签到次数，领取奖励情况，单月历史签到记录
            $date_sign = $this->getOneSign($_SESSION['user_id']);
            if ($date_sign){
                if (date('Y-m-d') == $date_sign['sign_last_date']){
                    $return['is_sign'] = 1;
                }
                $return['sign_count'] = $date_sign['accumulate_count'];
                $return['sign_date'] = $date_sign['record'];
                $reward = $date_sign['reward'];
                $redis->hMset($key, $date_sign);
            }
        }
        
        if ($reward){
            $reward = json_decode($reward, TRUE);
            $reward_number = [];
            foreach ($reward as $v){
                $reward_number[] = $v['number'];
            }
            $reward = $reward_number;
        }
        foreach ($sign_extra as $k=>$v){
            if ($reward){
                if (in_array($v['number'], $reward)){
                    $sign_extra[$k]['status'] = 0;
                    continue;
                }
            }
            //获取本月最大日期
            $firstday = date('Y-m-01', time());
            $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
            if ($lastday <= $return['sign_count']){
                $sign_extra[$k]['status'] = 1;
                continue;
            }
            
            if ($v['number'] <= $return['sign_count']){
                $sign_extra[$k]['status'] = 1;
                continue;
            }
        }
        $return['sign_extra'] = $sign_extra;
        return $return;
    }
    
    /**
     * 签到
     * 已mysql数据为基准
     * @return false 失败， true成功
     */
    public function sign() {
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        if (!$redis->exists(RK_USER_INFO . $_SESSION['user_id'])) {
            return false;
        }
        $user_info = $redis->hMget(RK_USER_INFO . $_SESSION['user_id'], array('gold','vip'));
        
        $redis->select(R_BENEFITS_DB);
        $sign_rule = $redis->hMget(PK_SIGN_RULE, array('day_gold', 'vip', 'extra_rule', 'sign_desc'));
        if (!$redis->exists(PK_SIGN_RULE)){
            $sign_rule = $this->getSignRule();
            if (!$sign_rule){
                return false;
            }
            $redis->hMset(PK_SIGN_RULE, $sign_rule);
        }
        
        //数据读取mysql数据为准,必须先删除掉redis的信息，签到之后同步即可
        $date = date("Y-m");
        $key = PK_USER_SIGN . $_SESSION['user_id'];
        if ($redis->exists($key)){
            $res = $redis->del($key);
            if ($res != TRUE){
                return false;
            }
        }
        $user_sign = $this->getOneSign($_SESSION['user_id']);
//        $user_sign = $redis->hMget($key, array('sign_date','sign_last_date','id'));
//        if ($user_sign){
//            if ($user_sign['sign_date'] != $date){
//                $user_sign = [];
//                $redis->del($key);
//            }
//        }
//        
//        if (!$user_sign){
//            $user_sign = $this->getOneSign($_SESSION['user_id']);
//            if ($user_sign){
//                $redis->hMset($key, $user_sign);
//            }
//        }
        
        if ($user_sign){
            //计算今天是否可以签到
            if (date('Y-m-d') == $user_sign['sign_last_date']){
                return false;
            }
        }
        
        $gold = 0;
        $gold = $sign_rule['day_gold'];
        if ($user_info['vip']){
            $gold = $sign_rule['day_gold'] * $sign_rule['vip'];
        }
        
        $db = $this->DB();
        $db->beginTransaction();
        
        //插入每天签到日志
        $log = [];
        $log['user_id'] = $_SESSION['user_id'];
        $log['gold'] = $gold;
        $log['date'] = date("Y-m-d");
        $log['date'] = ip2long($this->input()->ip_address());
        $log['c_time'] = time();
        //log文件月份分表
        $table_name = $this->_getSignLogTable();
        if ($db->insert($table_name, $log) == false){
            $db->rollBack();
            logMessage("$table_name 插入失败;" . var_export($log, true));
            return false;
        }
        
        $sign = [];
        $sign['sign_date'] = date('Y-m-d');
        $sign['user_id'] = $_SESSION['user_id'];
        $sign['sign_last_date'] = date('Y-m-d');
        if ($user_sign){
            //更新本月签到信息
            $sign['record'] = implode(',', array_merge([intval(date('d'))], explode(',', $user_sign['record'])));
            $sign['accumulate_count'] = 1 + $user_sign['accumulate_count'];
            $sign['u_time'] = time();
            try{
                $db->update('ms_benefits_sign', $sign, 'id = ' . $user_sign['id']);
            } catch (Exception $ex){
                $db->rollBack();
                logMessage($ex->getTraceAsString());
                return false;
            }
            $sign['id'] = $user_sign['id'];
        }else{
            //插入本月签到日志
            $sign['c_time'] = time();
            $sign['record'] = implode(',', [intval(date('d'))]);
            $sign['accumulate_count'] = 1;
            if ($db->insert('ms_benefits_sign', $sign) == false){
                $db->rollBack();
                logMessage('ms_benefits_sign 插入失败;' . var_export($log, true));
                return false;
            }
            $sign['id'] = $db->lastInsertId();
        }
        $sign['reward'] = $user_sign ? $user_sign['reward'] : "";
        
        if (!$this->onChangeUserGold($_SESSION['user_id'], $gold)){
            $db->rollBack();
            return false;
        }

        //记录金币流水
        GoldModel::log([
            'user_id'=>$_SESSION['user_id'],
            'gold_change'=>$gold,
            'gold_after'=>(new data\UserModel($_SESSION['user_id']))->get('gold'),
            'type'=>GOLD_FLOW_TYPE_SIGN,
            'created_at'=>time()
        ],$this->DB());

//        $redis->select(R_GAME_DB);
//        $res = $redis->hMset(RK_USER_INFO . $_SESSION['user_id'], array('gold'=>($user_info['gold'] + $gold)));
//        if ($res != TRUE){
//            $db->rollBack();
//            return false;
//        }
        $db->commit();
        
        $detail = [];
        $detail['user_id'] = $_SESSION['user_id'];
        $detail['count'] = $gold;
        $detail['why'] = "用户签到奖励";
        $this->_addUserLog($_SESSION['user_id'], LOG_ACT_SIGN, json_encode($detail));
        
        //更新用户签到数据，更新失败的时候，删除redis的数据
        $redis->select(R_BENEFITS_DB);
        $redis->hMset($key, $sign);
        
        return true;
    }
    /**
     * 记录金币改动
     */
    protected function _getSignLogTable() {
        $table_name = 'ms_benefits_sign_log' . date('Ym');
        //初始化数据表，如果不存在则新建
        $sql = 'create table if not exists ' . $table_name . ' like ms_benefits_sign_log';
        $this->DB()->exec($sql);
        return $table_name;
    }
    
    /**
     * 累积奖励
     * 取mysql得值，然后更新redis的值
     * return bool true false
     */
    public function extra(int $number, bool $check = false) {
        $sign_rule = $this->getSignRule();
        $user_sign = $this->getOneSign($_SESSION['user_id']);
        if (!$sign_rule || !$user_sign){
            return false;
        }
        //判断用户redis是否存在
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        if (!$redis->exists(RK_USER_INFO . $_SESSION['user_id'])) {
            return false;
        }
        $user_info = $redis->hMget(RK_USER_INFO . $_SESSION['user_id'], array('gold','vip'));
        
        //先判断该number是否在活动内
        $extra_rule = json_decode($sign_rule['extra_rule'], TRUE);
        $exists_rule = false;
        $extra_gold = 0;
        foreach ($extra_rule as $v){
            if ($v['number'] == $number){
                $exists_rule = true;
                $extra_gold = $v['gold'];
            }
        }
        
        if (!$exists_rule){
            return false;
        }
        
        if ($user_info){
            $extra_gold = $extra_gold * $sign_rule['vip'];
        }
        
        //判断自己是否符合领取的条件
        $firstday = date('Y-m-01', time());
        $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
        if (($user_sign['accumulate_count'] < $lastday) && ($user_sign['accumulate_count'] < $number)){
            return false;
        }
        
        //判断自己是否已经领取过了
        if ($user_sign['reward']){
            $reward = json_decode($user_sign['reward'], TRUE);
            foreach ($reward as $v){
                if ($v['number'] == $number){
                    return false;
                }
            }
        }
        
        if ($check){
            return true;
        }
        
        $reward[] = [
            'number' => $number,
            'gold' => $extra_gold,
            'time' => date('Y-m-d H:i:s')
        ];
        
        //开始领取，领取之前，先删除掉redis的个人信息
        $redis->select(R_BENEFITS_DB);
        $key = PK_USER_SIGN . $_SESSION['user_id'];
        if ($redis->exists($key)){
            $res = $redis->del($key);
            if ($res != TRUE){
                return false;
            }
        }
        
        //开始领取
        $db = $this->DB();
        $db->beginTransaction();
        
        $sign = [];
        $sign['u_time'] = time();
        $sign['reward'] = json_encode($reward);
        try{
            $db->update('ms_benefits_sign', $sign, 'id = ' . $user_sign['id']);
        } catch (Exception $ex){
            $db->rollBack();
            logMessage($ex->getTraceAsString());
            return false;
        }
        
        //给账户添加金币
        if (!$this->onChangeUserGold($_SESSION['user_id'], $extra_gold)){
            $db->rollBack();
            return false;
        }

        //记录金币流水
        GoldModel::log([
            'user_id'=>$_SESSION['user_id'],
            'gold_change'=>$gold,
            'gold_after'=>(new data\UserModel($_SESSION['user_id']))->get('gold'),
            'type'=>GOLD_FLOW_TYPE_SIGN,
            'created_at'=>time()
        ],$this->DB());

//        $redis->select(R_GAME_DB);
//        $res = $redis->hMset(RK_USER_INFO . $_SESSION['user_id'], array('gold'=>($user_info['gold'] + $extra_gold)));
//        if ($res != TRUE){
//            $db->rollBack();
//            return false;
//        }
        
        $db->commit();
        
        $detail = [];
        $detail['user_id'] = $_SESSION['user_id'];
        $detail['count'] = $extra_gold;
        $detail['why'] = "用户签到奖励";
        $this->_addUserLog($_SESSION['user_id'], LOG_ACT_SIGN, json_encode($detail));
        
        $redis->select(R_BENEFITS_DB);
        $user_sign['reward'] = json_encode($reward);
        $redis->hMset($key, $user_sign);
        return true;
    }
    
    public function getOneSign(int $user_id) {
        $date = date("Y-m");
        $sql = <<<SQL
                select * from ms_benefits_sign where user_id = ? and sign_date = ? limit 1
SQL;
        return $this->DB()->query_fetch($sql, array($user_id, $date));
    }
    
    public function getSignRule() {
        $sql = <<<SQL
                select * from ms_benefits_sign_rule limit 1
SQL;
        return $this->DB()->query_fetch($sql);
    }
    
    public function getOneSubsidy(int $id) {
        $sql = <<<SQL
                select id,number,conditions,gold from ms_benefits_subsidy where id = ? limit 1
SQL;
        return $this->DB()->query_fetch($sql, array($id));
    }
    
    public function getOneBenefits(int $id, array $fields = ['*']) {
        $fields = empty($fields) ? '*' : implode(',', $fields);
        $sql = <<<SQL
                select $fields from ms_benefits where id = ? and status = 1 limit 1
SQL;
        return $this->DB()->query_fetch($sql, array($id));
    }
    
    public function getSubsidyCount(int $user_id, int $id) {
        return $this->DB()->rowCount("ms_benefits_subsidy_log", "user_id = $user_id and benefits_id = $id");
    }
    
    public function getList(array $fields = ['*'], int $status = 1){
        $data = null;
        try {
            $fields = empty($fields) ? '*' : implode(',', $fields);
            $sql = <<<SQL
                select $fields from ms_benefits where status = ? and start_time <= ? and end_time >= ? order by sort ASC
SQL;
            $data = $this->DB()->query($sql, [$status, time(), time()]);
        } catch (Exception $e) {
            logMessage($e->getTraceAsString(), LOG_DEBUG);
        }
        return $data;
    }
    
}
