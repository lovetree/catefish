<?php

class OnlineModel extends BaseModel {
    
    private function _getOnlineRule(){
        $redis = PRedis::instance();
        $redis->select(R_BENEFITS_DB);
        $config = $redis->get(PK_ONLINE_RULE);
        if (!$redis->exists(PK_ONLINE_RULE)){
            $config = $this->getOnlineRule();
            if ($config){
                $config = $config['online_rule'];
                $redis->set(PK_ONLINE_RULE, $config);
            }
        }
        
        return $config;
    }
    
    private function _getOnline(){
        $redis = PRedis::instance();
        $redis->select(R_BENEFITS_DB);
        $log = $redis->get(PK_USER_ONLINE . $_SESSION['user_id']);
        if ($log && $log['online_date'] != date('Y-m-d')){
            $log = [];
        }
        
        if (!$log){
            $log = $this->getOnline($_SESSION['user_id']);
        }
        
        return $log;
    }
    
    /**
     * 获取online配置信息
     */
    public function onlineConfig(){
        $config = $this->_getOnlineRule();
        if ($config){
            $reward = json_decode($config, TRUE);
            
            $record_coin = [];
            //查询自己单天领取了没有
            $info = $this->baseFind('ms_online', 'user_id=?', [$_SESSION['user_id']], ['online_date','record']);
            if ($info && $info['online_date'] == date('Y-m-d')) {
                $record = json_decode($info['record'], TRUE);
                foreach ($record as $v){
                    $record_coin[$v['coin']] = $v['times'];
                }
            }
            if ($record_coin){
                foreach ($reward as $k=>$v){
                    if (isset($record_coin[$v['coin']])){
                        $times = $v['times'] - $record_coin[$v['coin']];
                        if ($times == 0){
                            unset($reward[$k]);
                            continue;
                        }
                        $reward[$k]['times'] = $times;
                    }
                }
            }
            array_walk($reward, function(&$item){
                $data = $item;
                $item = [];
                for ($i = 1; $i <= $data['times']; $i ++){
                    $item[] = [
                        'time' => $data['time'] * 60,
                        'coin' => $data['coin']
                    ];
                }
            });
            $return = [];
            foreach ($reward as $v){
                $return = array_merge($return, $v);
            }
        }
        
        //修改成自取一条数据
        //return $config ? $return : [];
        return $config ? ($return ? ['status'=>1,'time'=>$return[0]['time'],'coin'=>$return[0]['coin']] : ['status'=>0]) : ['status'=>0];
    }
    
    /**
     * 领取奖励
     * redis中获取数据，如果数据不存在，取redis中的值，如果存在，更新之前先删除
     * string返回错误信息，  true 成功
     */
    public function online(int $coin) {
        $rule = $this->_getOnlineRule();
        if (!$rule) return _1000601;
        $online_rule = json_decode($rule, TRUE);
        $coins = array_column($online_rule, 'coin');
        if (!in_array($coin, $coins)){
            return _1000602;
        }
        $times = 0;
        foreach ($online_rule as $v){
            if ($v['coin'] == $coin){
                $times = $v['times'];
                break;
            }
        }
        $online = $this->getOnline($_SESSION['user_id']);
        //判断能否领取奖励
        $new_record = [];
        if ($online && ($online['online_date'] == date('Y-m-d'))){
            $record = json_decode($online['record'], TRUE);
            $is_exist = false;
            foreach ($record as $k=>$v){
                if ($v['coin'] == $coin){
                    if ($v['times'] >= $times){
                        //说明已经领取达标，直接提示成功，让app系统自己下一个
                        return true;
                    }else{
                        $record[$k]['times'] = $v['times'] + 1;
                    }
                    $is_exist = true;
                }
            }
            if (!$is_exist){
                $record[] = [
                    'coin' => $coin,
                    'times' => 1
                ];
            }
            $new_record = $record;
        }else{
            $new_record[] = [
                'coin' => $coin,
                'times' => 1
            ];
        }
        
        //开始领取,开始之前先删除掉redis中的key值
        if (!$this->baseDelRedis(R_BENEFITS_DB, PK_USER_ONLINE . $_SESSION['user_id'])){
            return _1000518;
        }
        
        $db = $this->DB();
        $db->beginTransaction();
        //领取每日奖励
        $log = [];
        $log['user_id'] = $_SESSION['user_id'];
        $log['gold'] = $coin;
        $log['ip'] = ip2long($this->input()->ip_address());
        $log['c_time'] = time();
        if ($this->baseInsert($this->_getOnlineLogTable(), $log) == false){
            $db->rollBack();
            logMessage($this->_getOnlineLogTable() . ' 插入失败;' . var_export($log, true));
            return _1000603;
        }
        
        $data = [];
        $data['user_id'] = $_SESSION['user_id'];
        $data['online_date'] = date('Y-m-d');
        $data['record'] = json_encode($new_record);
        if ($online){
            //更新
            $data['u_time'] = time();
            try{
                $db->update('ms_online', $data, 'id = ' . $online['id']);
            } catch (Exception $ex){
                $db->rollBack();
                logMessage($ex->getTraceAsString());
                return _1000603;
            }
        }else{
            //插入
            $data['c_time'] = time();
            if ($this->baseInsert('ms_online', $data) == false){
                $db->rollBack();
                logMessage('ms_online 插入失败;' . var_export($data, true));
                return _1000603;
            }
        }
        
        //修改用户金币变化
        if (!$this->onChangeUserGold($_SESSION['user_id'], $coin)){
            $db->rollBack();
            return _1000001;
        }

        //记录金币流水
        GoldModel::log([
            'user_id'=>$_SESSION['user_id'],
            'gold_change'=>$coin,
            'gold_after'=>(new data\UserModel($_SESSION['user_id']))->get('gold'),
            'type'=>GOLD_FLOW_TYPE_OLREWARD,
            'created_at'=>time()
        ],$this->DB());

        $db->commit();
        
        $detail = [];
        $detail['user_id'] = $_SESSION['user_id'];
        $detail['count'] = $coin;
        $detail['why'] = "领取每日在线奖励";
        $this->_addUserLog($_SESSION['user_id'], LOG_ACT_ONLINE, json_encode($detail));
        
        return true;
    }
    
    public function getOnlineRule(){
        $sql = <<<SQL
                select online_rule from ms_online_rule limit 1
SQL;
        return $this->DB()->query_fetch($sql);
    }
    
    public function getOnline(int $user_id) {
        $sql = <<<SQL
                select id,online_date,record from ms_online where user_id = $user_id limit 1
SQL;
        return $this->DB()->query_fetch($sql);
    }
    
    protected function _getOnlineLogTable() {
        $table_name = 'ms_online_log' . date('Ym');
        //初始化数据表，如果不存在则新建
        $sql = 'create table if not exists ' . $table_name . ' like ms_online_log';
        $this->DB()->exec($sql);
        return $table_name;
    }
    
    public function getOnlineLog(int $user_id) {
        $table_name = $this->_getOnlineLogTable();
        $date = date("md");
        $sql = <<<SQL
                select id,record from $table_name where user_id = ? and online_date = ? limit 1
SQL;
        return $this->DB()->query_fetch($sql, array($user_id, $date));
    }

    /**
     * 获取房间在线人数
     */
    public  function getOLPlayerNum($room_num){
        $sql = <<<SQL
          select gametype, gamemod, onlinecount, gamecount from online_log order by time desc limit $room_num
SQL;

        return $this->DB_GameLog()->query($sql);
    }
}
