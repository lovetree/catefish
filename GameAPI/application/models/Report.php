<?php

class ReportModel extends BaseModel {

    /**
     * 用户信息上报
     */
    public function reportUserinfo(int $user_id) {
        //获取用户信息
        $u = new UserModel();
        $user = $u->getOneUser($user_id);
        $userinfo = $u->getOneUserInfo($user_id);
        $return = [];
        $return = array_merge($user, $userinfo);
        
        //获取redis中的数据
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        if ($redis->exists(RK_USER_INFO . $user_id)) {
            $data = $redis->hMget(RK_USER_INFO . $user_id, 
                    array('MaxBulletMul', 'gold', 'credit', 'emerald', 
                        'BulletUnlockSituation', 'BulletUpSuccessRate', 'BulletLv', 'DoubleBulletUnlocked'));
            $return = array_merge($return, $data);
        }
        
        //这里发送请求
        $url = "";
        $res = $this->http_post_request($url, $return);
        if ($res['status']){
            return;
        }
        //如果请求失败
        $log = [];
        $log['c_time'] = time();
        $log['err'] = "fasdfs";
        $log['user_id'] = $user_id;
        $log['type'] = 1;  //上报类型 1用户信息上报
        $this->baseInsert('ms_report_log', $log);
        
        //插入数据库日志
        $list = [];
        $list['times'] = 2;
        $list['user_id'] = $user_id;
        $list['info'] = $return;
        $redis->select(R_REPORT);
        //压入list表中
        $redis->lPush(PK_REPORT_USERINFO, json_encode($list));
    }
    
}
