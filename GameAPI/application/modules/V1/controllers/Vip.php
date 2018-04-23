<?php
/**
 * vip
 */

class VipController extends \BaseController {

    /**
     * 获取vip信息
     */
    public function vipAction() {
        $vip = new VipModel();
        $this->succ($vip->vipInfo());
    }
    
    /**
     * vip特权
     */
    public function privilegeAction() {
        $vip = new VipModel();
        $this->succ($vip->privilege());
    }
    
    /**
     * 每日特权奖励
     */
    public function dayAction() {
        $vip = new VipModel();
        $res = $vip->day();
        if ($res === TRUE){
            //获取金币数
            $redis = PRedis::instance();
            $redis->select(R_GAME_DB);
            $user_info = $redis->hMget(RK_USER_INFO . $_SESSION['user_id'], array('gold','emerald','credit'));
            return $this->succ($user_info);
        }
        return $this->failed($res);
    }
    
    /**
     * 首次充值信息
     * call c_type 类型 1表示捕鱼首充
     * return status 0不可领取 1可领取
     */
    public function firstrechargeinfoAction() {
        $input = $this->input();
        $rule = array(
            'p_type'  => 'required|inside:1',
        );
        
        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }
        $type = $input->json_stream('p_type');
        $vip = new VipModel();
        $this->succ($vip->firstrechargeinfo($type));
    }
    
    /**
     * 领取首次充值奖励
     */
    public function firstrechargeAction() {
        $input = $this->input();
        $rule = array(
            'p_type'  => 'required|inside:1',
        );
        
        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }
        $type = $input->json_stream('p_type');
        
        $vip = new VipModel();
        $res = $vip->firstrecharge($type);
        if ($res === TRUE){
            //获取金币数
            $redis = PRedis::instance();
            $redis->select(R_GAME_DB);
            $user_info = $redis->hMget(RK_USER_INFO . $_SESSION['user_id'], array('gold', 'credit', 'emerald'));
            return $this->succ($user_info);
        }
        return $this->failed($res);
    }
    
}
