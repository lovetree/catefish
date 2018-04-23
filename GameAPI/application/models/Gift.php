<?php

class GiftModel extends BaseModel {
    
    public function getGiftRule() {
        $sql = <<<SQL
                select exchange from ms_gift_rule
SQL;
        return $this->DB()->query_fetch($sql);
    }
    
    /**
     * 获取礼物列表
     */
    public function gift() {
        $list = $this->getGift();
        if (!$list) return [];
        ksort($list);
        return array_values($list);
    }
    
    public function getPopularity(int $user_id) {
        $sql = <<<SQL
                select popularity from ms_gift_popularity where user_id = $user_id
SQL;
        return $this->DB()->query_fetch($sql);
    }
    
    /**
     * 礼物信息
     */
    public function info() {
        $user = new UserModel();
        $user_info = $user->getInfoByRedis(['popularity']);
        $user_info['popularity'] = intval($user_info['popularity']);
        
        $rule = $this->getGiftRule();
        if (!$rule) return [];
        $user_info['exchange'] = $rule['exchange'];
        
        $user_info['to'] = $this->getRecord(1);
        $user_info['get'] = $this->getRecord(2);
        
        return $user_info;
    }
    
    /*
     * 赠送礼物
     */
    public function getGift(int $g_type = null) {
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        if (!$redis->exists(PK_GIFT_LIST)){
            $sql = "select name,unit,icon,gold,popularity,g_type from ms_gift where status = 1 order by g_sort ASC";
            $list = $this->DB()->query($sql);
            if ($list){
                $r = [];
                foreach ($list as $v){
                    $r[$v['g_type']] = json_encode($v);
                }
                $redis->hMset(PK_GIFT_LIST, $r);
            }
        }
        
        if (!$g_type){
            return $redis->hGetAll(PK_GIFT_LIST);
        }
        return $redis->hMGet(PK_GIFT_LIST, array($g_type));
    }
    
    public function giveGift(array $params) {
        $user_id = $_SESSION['user_id'];
        $touid = $params['touid'];
        $count = $params['count'];
        $g_type = $params['g_type'];
        if ($user_id == $touid){
            return _1001108;
        }
        $db = $this->DB();
        //判断用戶是否注册
        $user_type = $db->query_fetch('select `type` from ms_user WHERE id = '.$user_id)['type'];
        if($user_type==1){

            return _1001109;
        }
        if(isset($_SESSION['give'.$touid]) && (time()-$_SESSION['give'.$touid])<30){
            return _1001106;
        }
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        
        $gift_info = $this->getGift($g_type);
        if (!$gift_info[$g_type]){
            return _1001103;
        }
        $gift = json_decode($gift_info[$g_type], TRUE);
        
        $user_info = $redis->hMget(RK_USER_INFO . $user_id, array('gold', 'nickname'));
        if ($gift['gold'] * $count > $user_info['gold']){
            return _1000008;
        }
        
        //插入msyql
        //减少金币，且给对方增加人气或者减少人气
        //通知客户端
        $log = [];
        $log['user_id'] = $user_id;
        $log['to_user_id'] = $touid;
        $log['g_type'] = $g_type;
        $log['popularity'] = $gift['popularity'];
        $log['gold'] = $gift['gold'];
        $log['count'] = $count;
        $log['ip'] = ip2long($this->input()->ip_address());
        $log['c_time'] = time();
        
        //插入日志

        
        $last = $db->query_fetch('select 1 from ms_gift_give_log where user_id = ? and c_time >= ? order by c_time desc limit 1', [$user_id, (time() - 3)]);
        if($last){
            return _1001106;
        }
        
        //数据库中取出人气值
        $touid_info = $this->getPopularity($touid);
        
        if (!$touid_info){
            //插入信息
            $params = [];
            $params['user_id'] = $touid;
            $params['popularity'] = 0;
            $params['u_time'] = time();
            if (!$this->insertPopularity($params)){
                return _1001105;
            }
            $touid_info['popularity'] = 0;
        }
        
        $db->beginTransaction();
        if ($db->insert('ms_gift_give_log', $log) == FALSE){
            logMessage('ms_gift_give_log 插入失败;' . var_export($log, true));
            $db->rollBack();
            return _1001104;
        }
        
        //修改人气
        $popularity = $touid_info['popularity'] + $gift['popularity'] * $count;
        $popularity = $popularity >= 0 ? $popularity : 0;
        $touid_info['popularity'] = $popularity;
        try{
            $db->update('ms_gift_popularity', $touid_info, 'user_id = ' . $touid);
        } catch (Exception $ex){
            $db->rollBack();
            logMessage($ex->getTraceAsString());
            return _1001104;
        }
        
        //修改金币
        if (!$this->onChangeUserGold($user_id, -$gift['gold']*$count)){
            $db->rollBack();
            return _1001104;
        }
        
        $db->commit();
        //同步人气信息
        $redis->hMset(RK_USER_INFO . $touid, $touid_info);
        //同步排行
        $redis->zRem(PK_RANK_POPULARITY, $touid);
        $redis->zAdd(PK_RANK_POPULARITY, $touid_info['popularity'], $touid);
        //插入记录
        $record = [];
        $record['user_id'] = $user_id;
        $record['unit'] = $gift['unit'];
        $record['name'] = $gift['name'];
        $record['g_type'] = $gift['g_type'];
        $record['time'] = time();
        $touid_info = $redis->hMget(RK_USER_INFO . $touid, array('nickname'));
        $record['to_nickname'] = $touid_info['nickname'];
        $record['to_user_id'] = $touid;
        $record['nickname'] = $user_info['nickname'];
        $record['count'] = $count;
        $record['popularity'] = $gift['popularity'];
        
        $gift_record = $redis->hMget(PK_GIFT_RECORD, array($user_id, $touid));
        $record['type'] = 1; //赠送者
        $u_redis = [];
        $u_redis[] = $record;
        if ($gift_record[$user_id]){
            $u_record = json_decode($gift_record[$user_id], TRUE);
            $u_redis = array_merge($u_record, $u_redis);
        }
        $redis->hMset(PK_GIFT_RECORD, array($user_id => json_encode($u_redis)));
        
        $record['type'] = 2; //被赠送者
        $t_redis = [];
        $t_redis[] = $record;
        if ($gift_record[$touid]){
            $t_record = json_decode($gift_record[$touid], TRUE);
            $t_redis = array_merge($t_record, $t_redis);
        }
        $redis->hMset(PK_GIFT_RECORD, array($touid => json_encode($t_redis)));
        $newData = [
            'user_id'=>$_SESSION['user_id'],
            'gold_change'=>$gift['gold'],
            'gold_after'=>(new data\UserModel($_SESSION['user_id']))->get('gold'),
            'type'=>10,
            'create_time'=>time()
        ];
        $db->insert('ms_gold_log',$newData);
        //通知对方礼物收到了
        $data = [];
        $data['type'] = "gift";
        $data['data'] = [
            'g_type' => $g_type,
            'FromName' => $user_info['nickname'],
            'ToName' => $touid_info['nickname'],
            'count'=>$count
        ];
        $content = json_encode($data, JSON_UNESCAPED_UNICODE);
        $url = $this->config()->get('push.ip') . "/phppushtouser?userid=$touid&phpdata=$content";
        $res = $this->http_get_request($url);
        if ($res != "success"){
            //需要保存，已方便下次使用
            $redis->hMset(PK_GIFT_NOSEND, [$touid=> json_encode($data['data'])]);
        }
        $_SESSION['give'.$touid] = time();
        //保存记录
//        $log['unit'] = $gift['unit'];
//        $log['name'] = $gift['name'];
//        $redis->zAdd('gift_give', $user_id, json_encode($log));
        
        //返回数据
        return [
            'gold' => $user_info['gold'] - $gift['gold'] * $count,
            'g_type' => $g_type,
            'FromName' => $user_info['nickname'],
            'ToName' => $touid_info['nickname'],
            'count' => $count,
        ];
    }

    public function insertPopularity($params) {
        if ($this->DB()->insert('ms_gift_popularity', $params) == FALSE){
            logMessage('ms_gift_popularity 插入失败;' . var_export($params, true));
            return false;
        }
        return true;
    }
    
    /**
     * 领取首次充值礼包
     */
    public function exchange(int $popularity) {
        $user_id = $_SESSION['user_id'];
        
        //数据库中取出人气值
        $user_info = $this->getPopularity($user_id);
        if (!$user_info){
            //插入信息
            $params = [];
            $params['user_id'] = $user_id;
            $params['popularity'] = 0;
            $params['u_time'] = time();
            $this->insertPopularity($params);
            $user_info['popularity'] = 0;
        }
        
        if ($popularity <= 0 || $popularity > $user_info['popularity']){
            return _1001101;
        }
        
        //获取rule
        $rule = $this->getGiftRule();
        if (!$rule) return _1001102;
        
        $log = [];
        $log['user_id'] = $user_id;
        $log['popularity'] = $popularity;
        $log['exchange'] = $rule['exchange'];
        $log['before_modify_count'] = $user_info['popularity'];
        $log['after_modify_count'] = $user_info['popularity'] - $popularity;
        $log['ip'] = ip2long($this->input()->ip_address());
        $log['c_time'] = time();
        
        $db = $this->DB();
        $db->beginTransaction();
        
        //插入日志
        if ($db->insert('ms_gift_exchange', $log) == FALSE){
            logMessage('ms_gift_exchange 插入失败;' . var_export($log, true));
            $db->rollBack();
            return _1001107;
        }
        
        //修改人气
        $user_info['popularity'] = $user_info['popularity'] - $popularity;
        try{
            $db->update('ms_gift_popularity', $user_info, 'user_id = ' . $user_id);
        } catch (Exception $ex){
            $db->rollBack();
            logMessage($ex->getTraceAsString());
            return _1001107;
        }
        
        //修改金币
        if (!$this->onChangeUserGold($user_id, ($popularity * $rule['exchange']))){
            $db->rollBack();
            return _1001107;
        }

        //记录金币流水
        GoldModel::log([
            'user_id'=>$user_id,
            'gold_change'=>$popularity * $rule['exchange'],
            'gold_after'=>(new data\UserModel($user_id))->get('gold'),
            'type'=>GOLD_FLOW_TYPE_POPU,
            'created_at'=>time()
        ],$this->DB());
        GoldModel::renqi([
                'user_id'=>$user_id,
                'renqi_change'=>$popularity,
                'renqi_after'=>$user_info['popularity'],
                'create_time'=>time()
            ],$this->DB());
        $db->commit();
        
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        //同步人气信息
        $redis->hMset(RK_USER_INFO . $user_id, $user_info);
        //同步排行
        $redis->zRem(PK_RANK_POPULARITY, $user_id);
        $redis->zAdd(PK_RANK_POPULARITY, $user_info['popularity'], $user_id);
        
        return true;
    }
    
    /**
     * 获取记录
     */
    public function getRecord(int $r_type) {
        $user_id = $_SESSION['user_id'];
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $gift_record = $redis->hMGet(PK_GIFT_RECORD, array($user_id));
        if (!$gift_record[$user_id]){
            return [];
        }
        
        $record = json_decode($gift_record[$user_id], TRUE);
        $return = [];
        foreach ($record as $k=>$v){
            $bean = [];
            if ($v['time'] < strtotime("-1 month")){
                unset($record[$k]);
                continue;
            }
            if ($v['type'] == $r_type){
                $time = time() - $v['time'];
                if ($time <= 60*3){
                    $time = "刚刚";
                }else if($time < 3600){
                    $time = intval($time/60) . "分钟前";
                }else if($time < 3600*24){
                    $time = intval($time/3600) . "小时前";
                }else{
                    $time = intval($time/(3600*24)) . "天前";
                }
                
                switch ($r_type){
                    case 1:
                        if(!isset($v['to_user_id'])){
                            $bean['head'] = $time . "给" . $v['to_nickname']  . "送去了";
                        }else{
                            $bean['head'] = $time . "给" . $v['to_nickname'] . '('. $v['to_user_id'] . ")送去了";
                        }
                        break;
                    case 2:
                        if(isset($v['user_id'])){
                            $bean['head'] = $v['nickname'] .'( '.$v['user_id'] . ') ' . $time . "送来了";
                        }else{
                            $bean['head'] = $v['nickname'] . $time . "送来了";
                        }

                        break;
                }
                
                $bean['foot'] = $v['unit'] . $v['name'] . "！";
                $bean['count'] = $v['count'];
                $bean['g_type'] = $v['g_type'];
                $bean['popularity'] = $v['popularity'];
                $return[] = $bean;
            }
        }
        
        $redis->hMset(PK_GIFT_RECORD, array($user_id=> json_encode($record)));
        return array_reverse($return);
    }
    
    protected function _getGiveLogTable() {
        $table_name = 'ms_gift_give_log' . date('Ym');
        //初始化数据表，如果不存在则新建
        $sql = 'create table if not exists ' . $table_name . ' like ms_vip_day_log';
        $this->DB()->exec($sql);
        return $table_name;
    }
}
