<?php
/**
 * 通用捕鱼签到
 */

class SignController extends \BaseController {

    /**
     * 捕鱼签到界面
     */
    public function fishSignInfoAction() {
        $sign = new SignModel();
        $this->succ($sign->fishSignInfo());
    }
    
    /**
     * 捕鱼签到
     */
    public function fishSignAction() {
        $sign = new SignModel();
        $res = $sign->fishSign();
        if ($res === true){
            //获取金币数
            $redis = PRedis::instance();
            $redis->select(R_GAME_DB);
            $user_info = $redis->hMget(RK_USER_INFO . $_SESSION['user_id'], array('gold'));
            return $this->succ(['gold'=>$user_info['gold']]);
        }
        return $this->failed($res);
    }
}
