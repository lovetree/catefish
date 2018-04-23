<?php

class ShareController extends \BaseController {

    /**
     * 分享页面信息
     */
    public function shareAction() {
        $share = new ShareModel();
        $res = $share->share();
        if (!is_array($res)){
            return $this->failed($res);
        }
        return $this->succ($res);
    }
    
    /**
     * 新用户领取邀请奖励
     */
    public function newrewardAction() {
        $share = new ShareModel();
        $res = $share->newreward();
        
        if ($res === TRUE){
            return $this->succ();
        }
        return $this->failed($res);
    }
    
    /**
     * 个人绑定邀请人id号 
     */
    public function bindAction() {
        $input = $this->input();
        $rule = array(
            'id' => 'required',
            'terminal' => 'required',
        );

        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }
        $id = $input->json_stream('id');
        $is_guest=false;
        $userModel=new UserModel();
        $info=$userModel->getOneUser($_SESSION['user_id']);
        //检查绑定的id是否为自己
        if($info['wx_unionid'] == $id){
            return $this->failed("不可以绑定本人");
        }

        if(!$info)
        {
            $is_guest=true;
        }

        $result=[];
        if(!$is_guest)
        {
        $inputdata['user_id']=$info['wx_unionid'];
        $inputdata['recommender']=$input->json_stream('id');
        //$postdata=getPostData('setUserCenter',$input->json_stream('terminal'),$inputdata);

        $result=postData('setUserInfo',$input->json_stream('terminal'),$inputdata);
        }

        if($is_guest || ($result['code']==0 && $result['data']['result_code']==0))
        {
            //修改成功
            $redis = PRedis::instance();
            $redis->select(R_BENEFITS_DB);

            $update = [];
            $update['invite_id'] = $id;
            $update['invite_type'] = 1;
            $update['invite_time'] = time();
            $res = $this->baseUpdate('ms_user', $update, 'id='.$_SESSION['user_id']);
            if (!$res){
                return $this->failed(_1000514);
            }
            //添加到redis
            $redis->sAdd(RK_BZ_INVITE_USERS, $_SESSION['user_id'] . ':' . $id );

            return $this->succ();

        }
        else
        {
            if($result['code']==0)
            {
                return $this->failed($result['data']['result_desc']);
            }
            else
            {
                return $this->failed($result['msg']);
            }
        }

/*
        //先检查自己是否绑定过邀请
        $user = new UserModel();
        $user_info = $user->getOneUser($_SESSION['user_id'], array('created_at', 'invite_type', 
            'invite_id', 'invite_time'));
        if ($user_info['invite_id']){
            return $this->failed(_1000512);
        }

        
        //检查该Id用户是否存在
        if (!$user->getOneUser($id, array('id'))){
            return $this->failed(_1000106);
        }
        
        $redis = PRedis::instance();
        $redis->select(R_BENEFITS_DB);
        $share_rule = $redis->hMget(PK_SHARE_RULE, array('new_time_limit'));
        
        $share = new ShareModel();
        if (!$redis->exists(PK_SHARE_RULE)){
            $share_rule = $share->getShareRule();
            if (!$share_rule){
                return $this->failed(_1000511);
            }
            $redis->hMset(PK_SHARE_RULE, $share_rule);
        }
        //检查该用户创建时间是否已过限制时间
        if (time() > ($user_info['created_at'] + $share_rule['new_time_limit'] * 3600)){
            return $this->failed(_1000513);
        }
        
        $update = [];
        $update['invite_id'] = $id;
        $update['invite_type'] = 1;
        $update['invite_time'] = time();
        $res = $this->baseUpdate('ms_user', $update, 'id='.$_SESSION['user_id']);
        if (!$res){
            return $this->failed(_1000514);
        }
        //添加到redis
        $redis->sAdd(RK_BZ_INVITE_USERS, $_SESSION['user_id'] . ':' . $id );*/

        return $this->succ();
    }
    
    /**
     * 分享成功
     */
    public function succAction() {
        $share = new ShareModel();
        $res = $share->shareSucc();
        
        if ($res === TRUE){
            //返回用户总金币数
            $userData = new Data\UserModel($_SESSION['user_id']);
            return $this->succ(['gold'=>$userData->get('gold')]);
        }
        return $this->failed($res);
    }
    
    /**
     * 邀请记录
     */
    public function listAction() {
        $share = new ShareModel();
        $res = $share->inviteList($_SESSION['user_id']);
        $return = [];
        $return['list'] = $res ? $res : [];
        $this->succ($return);
    }
    
    /**
     * 老用户点击领取邀请的奖励
     */
    public function oldrewardAction() {
        $input = $this->input();
        $rule = array(
            'id' => 'required|number|min:0',
        );
        
        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }
        $id = $input->json_stream('id');
        $share = new ShareModel();
        $res = $share->oldreward($id);
        if ($res === true){
            return $this->succ();
        }
        return $this->failed($res);
    }
    
    /**
     * 邀请关联数据
     */
    public function inviteAction() {
        $wx = new WeixinModel();
        $openId = $wx->getWxUserInfo();
        if ($openId){
            //开始绑定关系
            $input = $this->input();
            $id = $input->get('id');
            $share = new ShareModel();
            $rule = $share->_shareRule();
            if (is_array($rule)){
                $redis = PRedis::instance();
                $redis->select(R_BENEFITS_DB);
                $key = PK_USER_SHARE_INVITE . $openId;
                $redis->set($key, $id);
                if ($rule['invite_valid_time']){
                    $redis->expire($key, $rule['invite_valid_time'] * 3600);
                }
            }
        }
        
        //跳转到指定的页面
        header('Location:' . $rule['invite_url']);
        exit;
    }
}
