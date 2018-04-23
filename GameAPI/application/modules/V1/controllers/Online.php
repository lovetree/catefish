<?php

class OnlineController extends \BaseController {

    /**
     * 在线奖励配置信息
     */
    public function ruleAction() {
        $online = new OnlineModel();
        $this->succ($online->onlineConfig());
    }
    public function keeponlineAction(){
       return $this->succ();
    }
    /*
     * 领取在线奖励
     */
    public function onlineAction() {
        $input = $this->input();
        $rule = array(
            'coin' => 'required|number'
        );
        
        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }
        
        $coin = $input->json_stream('coin');
        $online = new OnlineModel();
        $res = $online->online($coin);
        if ($res === true){
            //获取金币数
            $redis = PRedis::instance();
            $redis->select(R_GAME_DB);
            $user_info = $redis->hMget(RK_USER_INFO . $_SESSION['user_id'], array('gold'));
            $rule = $online->onlineConfig();
            $return = [];
            $return['gold'] = $user_info['gold'];
            if ($rule['status']){
                $return['coin'] = $rule['coin'];
                $return['time'] = $rule['time'];
            }
            return $this->succ($return);
        }
        return $this->failed($res);
    }

}
