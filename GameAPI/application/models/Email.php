<?php

class EmailModel extends BaseModel {

    const DB = R_EMAIL_DB;
    
    /**
     * 获取邮件列表
     */
    public function getMailList(int $user_id, int $offset = 0, int $length = 10){
        //获取系统邮件
        $last_id = $offset;
        $sys_list = self::_getSysMailList();
        if ($sys_list){
            foreach ($sys_list as $k=>$v){
                $email = json_decode($v, TRUE);
                if (isset($email['expired_time']) && time() > intval($email['expired_time'])){
                    unset($sys_list[$k]);
                    continue;
                }
                
                unset($email['content']);
                $email['m_time'] = substr($email['m_time'], 0, 10);
                $email['is_new'] = 0;
                $url = $this->config()->get('image.ip');
                $email['m_image'] = $url . $email['m_image'];
                if ($offset < $email['m_id']){
                    $email['is_new'] = 1;
                }
                $sys_list[$k] = json_encode($email);
                $last_id = $email['m_id'];
            }
        }
        
        $data_list = self::_getMailList($user_id, $offset, $length);
        if ($data_list){
            //去除过期时间的数据
            foreach ($data_list as $k=>$v){
                $email = json_decode($v, TRUE);
                if (isset($email['expired_time']) && time() > intval($email['expired_time'])){
                    unset($data_list[$k]);
                    continue;
                }
                
                unset($email['content']);
                $email['m_time'] = substr($email['m_time'], 0, 10);
                $email['is_new'] = 0;
                $url = $this->config()->get('image.ip');
                $email['m_image'] = $url . $email['m_image'];
                if ($offset < $email['m_id']){
                    $email['is_new'] = 1;
                }
                $data_list[$k] = json_encode($email);
                $last_id = $email['m_id'];
            }
        }
        
        $data_list = [
            'last_id' => $last_id,
            'list' => array_values(array_merge($sys_list, $data_list))
        ];
        
        return $data_list;
        
//        $sql = <<<SQL
//            select 
//                id as m_id,
//                title as m_title,
//                from_id as from_id,
//                from_name as m_frome_name,
//                created_time as m_time,
//                `status` as m_status
//            from ms_game_email
//            where `to_id` = ? and `status` >= 0 and (expired_time is null or expired_time > ?) and id > ?
//                limit {$length}
//SQL;
//        $data_list = $this->DB()->query($sql, array($user_id, time(), $offset));
//        return $data_list;
    }
    
    /**
     * redis获取邮件列表
     */
    private function _getMailList(int $user_id, int $offset = 0, int $length = 10){
        $redis = PRedis::instance();
        $redis->select(self::DB);
        //score保存的是u_id，也就是offset的值，获取大于score的邮件
        $key = PK_USER_EMAIL . $user_id;
        //判断是否存在
        if (!$redis->exists($key)){
            return [];
        }
        return $redis->zRangeByScore($key, "(0", "+inf", array('limit' => array(0, $length)));
        //return $redis->zRangeByScore($key, "($offset", "+inf", array('limit' => array(0, $length)));
    }
    
    private function _getSysMailList(){
        $redis = PRedis::instance();
        $redis->select(self::DB);
        //score保存的是u_id，也就是offset的值，获取大于score的邮件
        $key = PK_USER_SYS_EMAIL;
        //判断是否存在
        if (!$redis->exists($key)){
            return [];
        }
        return $redis->zRangeByScore($key, "(0", "+inf");
    }
    
    /**
     * 获取一条邮件数据
     * @param int $user_id
     * @param int $mail_id
     * @return mixed
     */
    public function getOne(int $user_id, int $mail_id){
        $sql = <<<SQL
            select 
                id as m_id,
                title as m_title,
                content as m_content,
                from_id as from_id,
                from_name as m_frome_name,
                created_time as m_time,
                type as m_type,
                `status` as m_status
            from ms_game_email
            where `to_id` = ? and `status` >= 0 and (expired_time is null or expired_time > ?) and id = ? limit 1
SQL;
        $data = $this->DB()->query_fetch($sql, array($user_id, time(), $mail_id));
        return $data;
    }
    
    /**
     * 读取一条游戏数据
     * @param int $user_id
     * @param int $mail_id
     * @return mixed
     */
    public function readOne(int $user_id, int $mail_id){
        $redis = PRedis::instance();
        $redis->select(self::DB);
        //score保存的是u_id，也就是offset的值，获取大于score的邮件
        $key = PK_USER_EMAIL . $user_id;
        $info = $redis->zRangeByScore($key, "$mail_id", "$mail_id", array('limit' => array(0, 1)));
        
        if (!$info){
            $info = $redis->zRangeByScore(PK_USER_SYS_EMAIL, "$mail_id", "$mail_id", array('limit' => array(0, 1)));
        }
        return $info;
        
//        $data = $this->getOne($user_id, $mail_id);
//        if(!empty($data) && $data['m_status'] == 0){
//            try{
//                $this->DB()->update('ms_game_email', array('status' => 1), 'id = ' . $mail_id);
//            } catch (Exception $ex){
//                logMessage($ex->getTraceAsString());
//            }
//            //设置邮件已读
//            $data['m_status'] = 1;
//        }
//        return $data;
    }

}
