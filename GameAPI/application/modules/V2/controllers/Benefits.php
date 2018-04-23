<?php

class BenefitsController extends \BaseController {

    /**
     * 福利页面信息
     */
    public function listAction() {
        
        $user = new UserModel();
        $user_info = $user->getInfoByRedis(array('gold'));
        if (!$user_info){
            return $this->failed(_1000001);
        }
        
        $return = [];
        $return['gold'] = $user_info['gold'];
        $return['safe_gold'] = 0;
        $safe = new SafeModel();
        $safe_info = $safe->getOne($_SESSION['user_id'], 'gold');
        if ($safe_info){
            $return['safe_gold'] = $safe_info['gold'];
        }
        
        $benefits = new BenefitsModel();
        $data = $benefits->getBenefitsList($user_info['gold']);
        
        $return['list'] = $data ? $data : [];
        
        return $this->succ($return);
    }
    
    /**
     * 福利页面点击事件
     */
    public function clickAction(){
        $input = $this->input();
        $rule = array(
            #'button_type' => 'required|inside:0',
            #'click_type' => 'required|number|min:0',
            #'relation_id' => 'required|number|min:1',
            'benefits_id' => 'required|number|min:1',
        );
        
        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }
        
        $benefits = new BenefitsModel();
        //检测活动是否存在
        $fields = ['id', 'button_type', 'click_type', 'relation_id', 'start_time', 'end_time'];
        $benefits_info = $benefits->getOneBenefits($input->json_stream('benefits_id'), $fields);
        if (!$benefits_info){
            return $this->failed(_1000501);
        }
        
        if ($benefits_info['start_time'] > time()){
            return $this->failed(_1000502);
        }
        
        if ($benefits_info['end_time'] < time()){
            return $this->failed(_1000503);
        }
        
        if ($benefits_info['relation_id'] == null){
            return $this->failed(_1000504);
        }
        
        if ($benefits_info['button_type'] != 0){
            return $this->failed(_1000504);
        }
        
        $user = new UserModel();
        $user_info = $user->getInfoByRedis(array('gold'));
        if (!$user_info){
            return $this->failed(_1000001);
        }
        
        switch (intval($benefits_info['click_type'])){
            case 0:
                //福利补贴,检测该活动是否存在
                $subsidy = $benefits->getOneSubsidy($benefits_info['relation_id']);
                if (!$subsidy){
                    return $this->failed(_1000505);
                }
                
                //检测自己领取的次数
                $subsidy_count = $benefits->getSubsidyCount($_SESSION['user_id'], $benefits_info['id']);
                if (intval($subsidy_count['count']) >= $subsidy['number']){
                    return $this->failed(_1000507);
                }
                
                //检测自己是否能够领取
                if ($user_info['gold'] >= $subsidy['conditions']){
                    return $this->failed(_1000506);
                }
                
                //请求领取补贴
                $insert = [];
                $insert['benefits_id'] = $benefits_info['id'];
                $insert['user_id'] = $_SESSION['user_id'];
                $insert['gold'] = $subsidy['gold'];
                if ($benefits->insertSubsidy($insert) == false){
                    return $this->failed(_1000508);
                }
                
                return $this->succ();
            default :
                return $this->failed(_1000005);
        }
    }
    
    /**
     * 刷新，显示是否含有未领取的金币
     */
    public function refreshAction() {
        $benefits = new BenefitsModel();
        $res = $benefits->refresh();
        $return = [];
        $return['reddot'] = $res ? 1 : 0;
        
        return $this->succ($return);
    }
    
    /**
     * 签到页面信息
     */
    public function signinfoAction() {
        $benefits = new BenefitsModel();
        $res = $benefits->signinfo();
        if (!$res){
            return $this->failed(_1000001);
        }
        return $this->succ($res);
    }
    
    /**
     * 签到
     */
    public function signAction() {
        $benefits = new BenefitsModel();
        $res = $benefits->sign();
        if (!$res){
            return $this->failed(_1000509);
        }
        return $this->succ();
    }
    
    /**
     * 每月累积奖励
     */
    public function extraAction() {
        $input = $this->input();
        $rule = array(
            'number' => 'required|number',
        );
        
        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }
        $number = $input->json_stream('number');
        $benefits = new BenefitsModel();
        $res = $benefits->extra($number);
        if (!$res){
            return $this->failed(_1000510);
        }
        return $this->succ();
    }
    
}
