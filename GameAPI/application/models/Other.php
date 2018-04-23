<?php

class OtherModel extends BaseModel {
    
    private $_payBroadcase = 10000;
    
    /**
     * 获取广播数据
     */
    public function getBroadcastList(int $source) {
        $return = [];
        //首先获取用户发送的广播，如果存在，不发送系统广播
//        $user_broadcast = self::_getUserBroadcastList();
//        if ($user_broadcast){
//            foreach ($user_broadcast as $v){
//                $res = json_decode($v, TRUE);
//                $return[] = [
//                    'id' => $res['id'],
//                    'content' => $res['content']
//                ];
//            }
//
//            return $return;
//        }
        
        //获取redis中所有符合条件的广播数据
        $data_list = self::_getBroadcastList();
        if ($data_list){
            //梳理出source的数据
            $source_list = [];
            $del_ids = [];
            $redis = PRedis::instance();
            $redis->select(R_MESSAGE_DB);
            foreach ($data_list as $k=>$v){
                $message = json_decode($v, TRUE);
                if (intval($message['end_time']) < time()){
                    $redis->zRem(RK_BROADCAST_LIST, $v);
                    $del_ids[] = $message['id'];
                    unset($data_list[$k]);
                    continue;
                }

                if ($message['source'] == $source){
                    $source_list[] = $message;
                }
            }

            if ($source_list){
                //获取redis自己的发送记录，梳理出能发送的数据，包括间隔，次数
                $key = RK_USER_BROADCAST . $_SESSION['user_id'];
                $user_broadcast = $redis->hGet($key, $source);
                $broadcast = [];
                if ($user_broadcast){
                    //数组格式，已id号为键值
                    $broadcast = json_decode($user_broadcast, $v);
                    if ($del_ids){
                        foreach ($del_ids as $v){
                            if (isset($broadcast[$v])){
                                unset($broadcast[$v]);
                            }
                        }
                    }
                }
                foreach ($source_list as $k=>$v){
                    if ($broadcast && isset($broadcast[$v['id']])){
                        if (isset($v['intervals']) && $v['intervals'] > 0){
                            //发送时间间隔有限制
                            if (($broadcast[$v['id']]['last_time'] + $v['intervals']) >= time()){
                                unset($source_list[$k]);
                                continue;
                            }
                            $broadcast[$v['id']]['last_time'] = time();
                        }
                        if (isset($v['times']) && $v['times'] > 0){
                            //发送次数有限制
                            if ($broadcast[$v['id']]['times'] >= $v['times']){
                                unset($source_list[$k]);
                                continue;
                            }
                            $broadcast[$v['id']]['times'] ++;
                        }
                    }else{
                        $broadcast[$v['id']]['times'] = 0;
                        if ($v['times'] > 0){
                            $broadcast[$v['id']]['times'] = 1;
                        }
                        $broadcast[$v['id']]['last_time'] = 0;
                        if ($v['intervals'] > 0){
                            $broadcast[$v['id']]['last_time'] = time();
                        }
                    }
                }

                //重新设置
                $redis->hSet($key, $source, json_encode($broadcast));
            }

            if ($source_list){
                foreach ($source_list as $v){
                    $return[] = [
                        'id' => $v['id'],
                        'content' => $v['content']
                    ];
                }
            }

            $data_list = $return;
        }
        
        return $data_list ? $data_list : [];
    }
    
    public function _getBroadcastList() {
        $redis = PRedis::instance();
        $redis->select(R_MESSAGE_DB);
        //score保存的是有效时间，获取time()值以下的都算是能开始广播
        $key = RK_BROADCAST_LIST_1;
        $time = time();
        return $redis->zRangeByScore($key, "(0", "$time");
    }
    
    public function _getUserBroadcastList() {
        $redis = PRedis::instance();
        $redis->select(R_MESSAGE_DB);
        $key = RK_USER_BROADCAST . $_SESSION['user_id'];
        $info = $redis->hMget($key, array('-1'));
        $start = time();
        if (!$info['-1']){
            $info['-1'] = $start;
        }
        
        $start = $info['-1'];
        //$end = $start + 5;
        $end = time();
        $redis->hMset($key, array('-1' => $end));
        //获取score值到score+5秒以内的广播
        return $redis->zRangeByScore(RK_USER_BROADCAST_LIST, "($start", "$end");
    }
    
    /**
     * 发送广播
     */
    public function sendBroadcast(string $content, int $b_type, string $code = null) {
        //获取用户信息，确保金币足够支付
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $user_info = $redis->hMget(RK_USER_INFO . $_SESSION['user_id'], array('gold', 'emerald', 'credit', 'nickname'));
        if ($b_type == 0){
            if (!$user_info['gold'] || $user_info['gold'] < $this->_payBroadcase){
                return _1000008;
            }
        }
        
        if ($b_type == 1){
            //需要验证code码
            if (!$code) return _1000009;
            $code = $_SESSION['user_id'] . $code;
            if (!$redis->sIsMember(RK_USER_BROADCAST_CODE, $code)){
                return _1000009;
            }
            //判断时间是否在30秒以内
            if ((time() - $code) > 30){
                return _1000009;
            }
            
            $redis->sRem(RK_USER_BROADCAST_CODE, $code);
            if ($redis->sIsMember(RK_USER_BROADCAST_CODE, $code)){
                return _1000009;
            }
        }
        
        $table = $this->_getBroadcastLogTable();
        
        $db = $this->DB();
        $db->beginTransaction();
        
        //插入广播发送信息
        //扣除金币
        //发送广播到redis中
        $log = [];
        $log['user_id'] = $_SESSION['user_id'];
        $log['pay_gold'] = $this->_payBroadcase;
        $log['content'] = $user_info['nickname'] . "：" . $content;
        $log['ip'] = ip2long($this->input()->ip_address());
        $log['c_time'] = time();
        if ($this->baseInsert($table, $log) == false){
            $db->rollBack();
            logMessage($table . ' 插入失败;' . var_export($log, true));
            return _1001001;
        }
        
        if ($b_type == 0){
            if (!$this->onChangeUserGold($_SESSION['user_id'], -$this->_payBroadcase)){
                $db->rollBack();
                return _1001001;
            }
        }
        
        $id = $db->lastInsertId();
        $db->commit();
        
        $common = new CommonModel();
        $content = $common->filterSensitiveWord($content);
        //发送redis中,sorted有序集合，score保存当天时间戳值
        $redis->select(R_MESSAGE_DB);
        $r_data = [];
        $r_data['id'] = $id;
        $r_data['content'] = $user_info['nickname'] . "：" . $content;
        $r_data['time'] = $log['c_time'];
        $redis->zAdd(RK_USER_BROADCAST_LIST, $log['c_time'], json_encode($r_data));
        
        //清除上一天的记录
        $last_time = strtotime(date("Y-m-d"));
        $redis->zDeleteRangeByScore(RK_USER_BROADCAST_LIST, 0, "$last_time");


        return true;

    }
    
    protected function _getBroadcastLogTable() {
        $table_name = 'ms_game_broadcast_log' . date('Ym');
        //初始化数据表，如果不存在则新建
        $sql = 'create table if not exists ' . $table_name . ' like ms_game_broadcast_log';
        $this->DB()->exec($sql);
        return $table_name;
    }
    
    //重置当前用户登录时间戳
    public function initLoginTime(int $user_id) {
        $redis = PRedis::instance();
        $redis->select(R_MESSAGE_DB);
        $key = RK_USER_BROADCAST . $user_id;
        $redis->hMset($key, array('-1' => time()));
    }
    
}
