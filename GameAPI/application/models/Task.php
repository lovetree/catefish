<?php

class TaskModel extends BaseModel {
    
    private $log_array = [];
    
    public function getTaskRule() {
        $sql = <<<SQL
                select id,t_type,source,reward,reward_type,conditions from ms_task_rule 
                    where status = 1 order by t_sort ASC
SQL;
        return $this->DB()->query($sql);
    }
    
    public function _getTaskDay() {
        $redis = PRedis::instance();
        $redis->select(R_BENEFITS_DB);
        $day = $redis->hMget(PK_TASK_DAY, [$_SESSION['user_id']]);
        $day = $day[$_SESSION['user_id']];
        if (!$day){
            $day = $this->baseFind('ms_task_day', 'user_id=?', array($_SESSION['user_id']), 
                    ['id','day_date','data']);
            if ($day) $redis->hMset(PK_TASK_DAY, [$_SESSION['user_id'] => json_encode($day)]);
        }else{
            $day = json_decode($day, TRUE);
        }
        return $day;
    }
    
    public function _getTaskRule() {
        $redis = PRedis::instance();
        $redis->select(R_BENEFITS_DB);
        $rule = $redis->get(PK_TASK_RULE);
        if (!$redis->exists(PK_TASK_RULE)){
            $rule = $this->getTaskRule();
            if ($rule){
                $redis->set(PK_TASK_RULE, json_encode($rule));
            }
        }else{
            $rule = json_decode($rule, TRUE);
        }
        
        if ($rule){
            //拼接信息
            foreach ($rule as $k=>$v){
                $info = '';
                $reward = '奖:';
                switch ($v['source']){
                    case 1:
                        $info .= '成功捕获' . $v['conditions'] . '鱼';
                        break;
                    case 2:
                        $info .= '在商城进行' . $v['conditions'] . '次消费';
                        break;
                    case 3:
                        $info .= '完成' . $v['conditions'] . '次炮数解锁';
                        break;
                    case 4:
                        $info .= '完成' . $v['conditions'] . '次分享';
                        break;
                    default :
                        return [];
                }
                switch ($v['reward_type']){
                    case "gold":
                        $reward .= $v['reward'] . "金币";
                        break;
                    case "credit":
                        $reward .= $v['reward'] . "钻石";
                        break;
                    case "emerald":
                        $reward .= $v['reward'] . "绿宝石";
                        break;
                    case "frozen":
                        $reward .= $v['reward'] . "冰封道具";
                        break;
                    case "eagleeye":
                        $reward .= $v['reward'] . "鹰眼道具";
                        break;
                    default :
                        return [];
                }
                
                $rule[$k]['info'] = $info;
                $rule[$k]['info_reward'] = $reward;
                $rule[$k]['status'] = 0;  //0表示不可领取 1表示可以领取
            }
        }
        return $rule;
    }
    
    /**
     * 任务列表
     */
    public function task() {
        //获取规则
        $rule = $this->_getTaskRule();
        if (!$rule) return [];

        //判断自己是否已领取
        $day = $this->_getTaskDay();
        $ids = [];
        if ($day){
            if (date('Y-m-d') == $day['day_date']){
                $ids = json_decode($day['data'], TRUE);
            }
        }
        
        //获取redis中信息
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $user_info = $redis->hMget(RK_USER_INFO . $_SESSION['user_id'], 
                array('fishTask', 'batteryTask', 'shareTask', 'shopTask'));
        foreach ($rule as $k=>$v){
            if ($ids && in_array($v['id'], $ids)){
                unset($rule[$k]);
                continue;
            }
            
            $task = [];
            switch ($v['source']){
                case 1:
                    //捕鱼
                    $task = $user_info['fishTask'] ? json_decode($user_info['fishTask'], TRUE) : [];
                    break;
                case 2:
                    //商城消费
                    $task = $user_info['shopTask'] ? json_decode($user_info['shopTask'], TRUE) : [];
                    break;
                case 3:
                    //炮台解锁
                    $task = $user_info['batteryTask'] ? json_decode($user_info['batteryTask'], TRUE) : [];
                    if ($task){
                        $task['num'] -= 1;
                    }
                    break;
                case 4:
                    //分享
                    $task = $user_info['shareTask'] ? json_decode($user_info['shareTask'], TRUE) : [];
                    break;
            }
            
            if ($task && date('Y-m-d', time()) == $task['date'] && $task['num'] >= $v['conditions']){
                $rule[$k]['status'] = 1;
            }
        }
        return $rule ? array_values($rule) : [];
    }
    
    /**
     * 每日特权奖励
     */
    public function day(int $id) {
        //获取规则
        $rule = $this->_getTaskRule();
        if (!$rule) return _1000901;
        $rule_info = [];
        foreach ($rule as $v){
            if (intval($v['id']) == $id){
                $rule_info = $v;
            }
        }
        if (!$rule_info) return _1000901;
        //判断自己是否已经领取过
        $day = $this->_getTaskDay();
        $ids = [];
        if ($day){
            if (date('Y-m-d') == $day['day_date']){
                $ids = json_decode($day['data'], TRUE);
                if (in_array($id, $ids)){
                    return _1000902;
                }
                $ids[] = $id;
            }
        }
        if (!$ids) $ids[] = $id;
        //判断能否领取条件
        //获取redis中信息
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $user_info = $redis->hMget(RK_USER_INFO . $_SESSION['user_id'], 
                array('fishTask', 'batteryTask', 'shareTask', 'shopTask'));
        $task = [];
        switch ($rule_info['source']){
            case 1:
                //捕鱼
                $task = $user_info['fishTask'] ? json_decode($user_info['fishTask'], TRUE) : [];
                break;
            case 2:
                //商城消费
                $task = $user_info['shopTask'] ? json_decode($user_info['shopTask'], TRUE) : [];
                break;
            case 3:
                //炮台解锁
                $task = $user_info['batteryTask'] ? json_decode($user_info['batteryTask'], TRUE) : [];
                if ($task){
                        $task['num'] -= 1;
                    }
                break;
            case 4:
                //分享
                $task = $user_info['shareTask'] ? json_decode($user_info['shareTask'], TRUE) : [];
                break;
        }
        
        if (!($task && date('Y-m-d', time()) == $task['date'] && $task['num'] >= $rule_info['conditions'])){
            return _1000903;
        }
        
        //开始领取奖励
        $log = [];
        $log['t_id'] = $id;
        $log['user_id'] = $_SESSION['user_id'];
        $log['reward'] = $rule_info['reward'];
        $log['reward_type'] = $rule_info['reward_type'];
        $log['ip'] = ip2long($this->input()->ip_address());
        $log['c_time'] = time();
        
        //删除Key值
        if (!$this->baseDelRedis(R_BENEFITS_DB, PK_TASK_DAY)){
            return _1000904;
        }
        
        $db = $this->DB();
        $db->beginTransaction();
        
        if ($this->baseInsert($this->_getDayLogTable(), $log) == false){
            $db->rollBack();
            logMessage($this->_getDayLogTable() . ' 插入失败;' . var_export($log, true));
            return _1000904;
        }
        
        $data = [];
        $data['user_id'] = $_SESSION['user_id'];
        $data['day_date'] = date('Y-m-d');
        $data['data'] = json_encode($ids);
        $data['u_time'] = time();
        if ($day){
            try{
                $db->update('ms_task_day', $data, 'id = ' . $day['id']);
            } catch (Exception $ex){
                $db->rollBack();
                logMessage($ex->getTraceAsString());
                return _1000904;
            }
        }else{
            if ($this->baseInsert('ms_task_day', $data) == false){
                $db->rollBack();
                logMessage('ms_task_day 插入失败;' . var_export($data, true));
                return _1000904;
            }
        }
        
        $detail = [];
        $detail['user_id'] = $_SESSION['user_id'];
        $detail['count'] = $rule_info['reward'];
        
        $count = $rule_info['reward'];
        switch ($rule_info['reward_type']){
            case "gold":
                if (!$this->onChangeUserGold($_SESSION['user_id'], $count)){
                    $db->rollBack();
                    return _1000904;
                }
                $detail['why'] = "每日任务金币奖励";

                //记录金币流水
                GoldModel::log([
                    'user_id'=>$_SESSION['user_id'],
                    'gold_change'=>$count,
                    'gold_after'=>(new data\UserModel($_SESSION['user_id']))->get('gold'),
                    'type'=>GOLD_FLOW_TYPE_TASK,
                    'created_at'=>time()
                ],$this->DB());
                break;
            case "emerald":
                if (!$this->onChangeUserEmerald($_SESSION['user_id'], $count)){
                    $db->rollBack();
                    return _1000904;
                }
                $detail['why'] = "每日任务绿宝石奖励";
                break;
            case "credit":
                if (!$this->onChangeUserCredit($_SESSION['user_id'], $count)){
                    $db->rollBack();
                    return _1000904;
                }
                $detail['why'] = "每日任务钻石奖励";
                break;
            case "eagleeye":
                if (!$this->onChangeUserEagleeye($_SESSION['user_id'], $count)){
                    $db->rollBack();
                    return _1000904;
                }
                $detail['why'] = "每日任务鹰眼道具奖励";
                break;
            case "frozen":
                if (!$this->onChangeUserFrozen($_SESSION['user_id'], $count)){
                    $db->rollBack();
                    return _1000904;
                }
                $detail['why'] = "每日任务冰封道具奖励";
                break;
        }
        
        $db->commit();
        $this->_addUserLog($_SESSION['user_id'], LOG_ACT_TASK, json_encode($detail));
        return true;
    }
    
    protected function _getDayLogTable() {
        $table_name = 'ms_task_log' . date('Ym');
        //初始化数据表，如果不存在则新建
        $sql = 'create table if not exists ' . $table_name . ' like ms_task_log';
        $this->DB()->exec($sql);
        return $table_name;
    }
}
