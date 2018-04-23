<?php

class SafeModel extends BaseModel {
    
    /**
     * 更新保险箱
     * @param $params
     * @return bool；true=成功
     */
    public function updateSafe(array $safe_info, array $params, array $user_redis) : bool {
        $safe_update = [];
        switch (intval($params['type'])) {
            case 1:
                //存入
                $user_redis[CURRENCY_GOLD] = intval($user_redis[CURRENCY_GOLD]) - intval($params['number']);
                $user_redis[$params['cmd']] = intval($user_redis[$params['cmd']]) + intval($params['number']);
                $safe_update[$params['cmd']] = intval($safe_info[$params['cmd']]) + intval($params['number']);
                $safeType = 11;
                $number = 0-intval($params['number']);
                break;
            case 2:
                //取出
                $user_redis[CURRENCY_GOLD] = intval($user_redis[CURRENCY_GOLD]) + intval($params['number']);
                $user_redis[$params['cmd']] = intval($user_redis[$params['cmd']]) - intval($params['number']);
                $safe_update[$params['cmd']] = intval($safe_info[$params['cmd']]) - intval($params['number']);
                $safeType = 12;
                $number = intval($params['number']);
                break;
        }
        
        $db = $this->DB();
        $db->beginTransaction();
        try{
            $db->update('ms_safe', $safe_update, 'id = ' . $safe_info['id']);
        } catch (Exception $ex){
            $db->rollBack();
            logMessage($ex->getTraceAsString());
            return false;
        }
        
        //插入日志
        $safe_log = [];
        $safe_log['safe_id'] = $safe_info['id'];
        $safe_log['user_id'] = $safe_info['user_id'];
        $safe_log['cmd'] = $params['cmd'];
        $safe_log['type'] = $params['type'];
        $safe_log['number'] = $params['number'];
        $safe_log['before_modify_count'] = $safe_info[$params['cmd']]?$safe_info[$params['cmd']]:'0';
        $safe_log['after_modify_count'] = $safe_update[$params['cmd']];
        $safe_log['ip'] = $this->input()->ip_address();
        $safe_log['c_time'] = time();
        if ($db->insert('ms_safe_log', $safe_log) == false){
            $db->rollBack();
            logMessage('ms_safe_log 插入失败;' . var_export($safe_log, true));
            return false;
        }
        
        //修改用户redis中的数据
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $res = $redis->hMset(RK_USER_INFO . $_SESSION['user_id'], $user_redis);
        $newData = [
            'user_id'=>$_SESSION['user_id'],
            'gold_change'=>$number,
            'gold_after'=>$user_redis[CURRENCY_GOLD],
            'type'=>$safeType,
            'create_time'=>time()
        ];
        $db->insert('ms_gold_log',$newData);
        if ($res === TRUE){
            $db->commit();
            return true;
        }
        
        $db->rollBack();
        return false;
    }
    
    public function getOne(int $user_id, string $cmd = null){
        $field = $cmd ? $cmd . ',' : "";
        $sql = <<<SQL
                select id,user_id,password,$field status from ms_safe where user_id = ? limit 1
SQL;
        return $this->DB()->query_fetch($sql, array($user_id));
    }
    
    /**
     * 更新密码
     * @param $params
     * @return bool true=成功
     */
    public function password(array $params, int $safe_id = 0) {
        $safe_info = [];
        $safe_info['password'] = md5($params['password']);
        $db = $this->DB();
        if ($safe_id){
            try{
                $db->update('ms_safe', $safe_info, 'id = ' . $safe_id);
            } catch (Exception $ex){
                logMessage($ex->getTraceAsString());
                return false;
            }
        }else{
            $safe_info['user_id'] = $_SESSION['user_id'];
            $safe_info['c_time'] = time();
            if (!$db->insert('ms_safe', array_fetch($safe_info, array('user_id', 'password', 'c_time')))) {
                return false;
            }
        }
        return true;
    }
    
}
