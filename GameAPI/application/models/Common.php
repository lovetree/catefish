<?php

class CommonModel extends BaseModel {
    
    /**
     * 通用发送邮件
     * user_id 用户id
     * data 包含title， content， from_id
     */
    public function sendEmail(int $user_id, array $data) {
        if (!$user_id) return false;
        $redis = PRedis::instance();
        $redis->select(R_EMAIL_DB);
        //score保存的是u_id，也就是offset的值，获取大于score的邮件
        $key = PK_USER_EMAIL . $user_id;
        
        $i_redis = [];
        $i_redis['m_title'] = $data['title'];
        $i_redis['m_content'] = $data['content'];
        $i_redis['m_from'] = $data['from_id'];
        $i_redis['m_from_name'] = "系统";
        $i_redis['m_time'] = date('Y-m-d H:i:s');
        $i_redis['m_status'] = 0;
        
        $i_mysql = [];
        $i_mysql['title'] = $data['title'];
        $i_mysql['content'] = $data['content'];
        $i_mysql['from_id'] = $data['from_id'];
        $i_mysql['to_id'] = $user_id;
        $i_mysql['from_name'] = "系统";
        $i_mysql['type'] = 1;
        if ($data['from_id']){
            $user = new UserModel();
            $userInfo = $user->getOneUserInfo($data['from_id'], ['nickname']);
            $i_redis['m_from_name'] = $userInfo['nickname'];
        }
        
        $db = $this->DB();
        if ($db->insert('ms_game_email', $i_mysql) == FALSE){
            logMessage('ms_game_email 插入失败;' . var_export($i_mysql, true));
            return false;
        }
        
        $i_redis['m_id'] = $db->lastInsertId();
        
        $redis->zAdd($key, intval($i_redis['m_id']), json_encode($i_redis));
        
        return true;
    }
    
    /**
     * 广播通用接口
     *  $data = array
     *  $data['content'] //广播内容
        $data['source'] //来源0大厅广播
        $data['times'] //发送刺死 0表示无限制
        $data['intervals'] //时间间隔 0表示无间隔
        $data['start_time'] //开始时间
        $data['end_time'] = //结束时间
     */
    public function sendBroadcast(array $data) {
        //添加广播,正对所有人
        $redis = PRedis::instance();
        $redis->select(R_MESSAGE_DB);
        //score保存的是有效时间，获取time()值以下的都算是能开始广播
        $key = RK_BROADCAST_LIST_1;

        $data['type'] = 1;
        $db = $this->DB();
        if ($db->insert('ms_game_message', $data) == FALSE){
            logMessage('ms_game_message 插入失败;' . var_export($data, true));
            return false;
        }
        
        $data['id'] = $db->lastInsertId();
        $redis->zAdd($key, $data['start_time'], json_encode($data));
        return true;
    }
    
    /**
     * 通用公告
     * array $data
     * data['title'] 公告标题
     * data['content'] 公告内容
     */
    public function sendNotice(array $data) {
        //添加广播,正对所有人
        $redis = PRedis::instance();
        $redis->select(R_MESSAGE_DB);
        //score保存的是u_id，也就是offset的值，获取大于score的邮件
        $key = RK_NOTICE_LIST;

        $data['type'] = 0;
        $db = $this->DB();
        if ($db->insert('ms_game_message', $data) == FALSE){
            logMessage('ms_game_message 插入失败;' . var_export($data, true));
            return false;
        }
        
        $data['id'] = $db->lastInsertId();
        $data['c_time'] = date('Y-m-d H:i:s');
        $redis->zAdd($key, intval($data['id']), json_encode($data));
        return true;
    }
    
    /**
     * 获取敏感字
     */
    public function getSensitiveWord() {
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $key = PK_SENSITIVE_WORD;
        if (!$redis->exists($key)){
            $sql = <<<SQL
                    select name from ms_sensitive_word where status != -1
SQL;
            $data = $this->DB()->query($sql);
            if ($data){
                $r = [];
                foreach ($data as $v){
                    $r[] = $v['name'];
                }
                $redis->set($key, json_encode($r));
            }
        }
        
        $res = $redis->get($key);
        return $res ? json_decode($res, TRUE) : [];
    }
    
    /**
     * 过滤信息
     */
    public function filterSensitiveWord(string $content) : string{
        $badword = $this->getSensitiveWord();
        if (!$badword) return $content;
        $badword1 = array_combine($badword,array_fill(0,count($badword),'*'));
        return strtr($content, $badword1);
    }
}
