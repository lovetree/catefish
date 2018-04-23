<?php

class SafeController extends \BaseController {

    /**
     * 保险箱存取
     * @type 1存 2取
     * @cmd  gold金币
     */
    public function updateAction() {
        $input = $this->input();
        $rule = array(
            'cmd' => 'required|inside:gold:safe_gold',
            'number' => 'required|number|min:0',
            'password' => 'required',
            'type'  => 'required|inside:1:2'
        );
        
        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }
        
//        if (!checkPassword($input->json_stream('password'))){
//            return $this->failed(_1000007);
//        }
        
        $safe = new SafeModel();
        $safe_info = $safe->getOne($_SESSION['user_id'], $input->json_stream('cmd'));
        if (!$safe_info){
              $safe->password(['password'=>'888888']);
              $safe_info = $safe->getOne($_SESSION['user_id'], $input->json_stream('cmd'));
//            return $this->failed(_1000401);
        }
        
        if (!$safe_info['status'] === 0){
            return $this->failed(_1000402);
        }
        
//        if (md5($input->json_stream('password')) !== $safe_info['password']){
//            return $this->failed(_1000403);
//        }

        //需要获取到用户redis中的值，如果不存在就提示系统繁忙，存在做处理
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        if (!$redis->exists(RK_USER_INFO . $_SESSION['user_id'])) {
            return $this->failed(_1000001);
        }
        $data = $redis->hMget(RK_USER_INFO . $_SESSION['user_id'], array(CURRENCY_GOLD, $input->json_stream('cmd')));
        if ($data == false){
            return $this->failed(_1000001);
        }
        
        switch (intval($input->json_stream('type'))){
            case 1:
                //存
                if (intval($input->json_stream('number')) > $data[CURRENCY_GOLD]){
                    return $this->failed(_1000405);
                }
                break;
            case 2:
                //取
                if (intval($safe_info[$input->json_stream('cmd')]) < intval($input->json_stream('number'))){
                    return $this->failed(_1000404);
                }
                break;
            default :
                return $this->failed(_1000005);
        }
        
        $res = $safe->updateSafe($safe_info, $input->json_stream(), $data);
        if ($res === TRUE){
            $user = new UserModel();
            return $this->succ(array_merge(['type'=>intval($input->json_stream('type'))],
                $user->getInfoByRedis(['gold', 'safe_gold'])));
        }
        
        return $this->failed(_1000001);
    }

    /**
     * 检测用户是否已添加过密码
     */
    public function checkAction(){
        $safe = new SafeModel();
        $safe_info = $safe->getOne($_SESSION['user_id']);
        
        $data = [];
        $data['type'] = $safe_info ? 2 : 1;
        return $this->succ($data);
    }
    
    /**
     * 添加密码
     * @type 1 首次 2重置密码
     */
    public function passwordAction(){
        $input = $this->input();
        $rule = array(
            'password'   => 'required', //新密码
            'type'       => 'required|inside:1:2',
            'repassword' => 'required' //确认密码

        );
        
        if (intval($input->json_stream('type')) == 2){
            $rule['oldpassword'] = 'required'; //原始密码
        }
        
        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }
        
        if (!checkPassword($input->json_stream('password'))){
            return $this->failed(_1000007);
        }
        
        $safe = new SafeModel();
        $safe_info = $safe->getOne($_SESSION['user_id']);
        switch (intval($input->json_stream('type'))){
            case 1:
                //首次添加密码
                if ($input->json_stream('password') != $input->json_stream('repassword')){
                    return $this->failed(_1000103);
                }
                if ($safe_info){
                    return $this->failed(_1000406);
                }
                
                $res = $safe->password($input->json_stream());
                break;
            case 2:
                //第二次修改密码
                if ($input->json_stream('password') != $input->json_stream('repassword')){
                    return $this->failed(_1000103);
                }
                if (!$safe_info){
                    return $this->failed(_1000407);
                }
                
                if (md5($input->json_stream('oldpassword')) != $safe_info['password']){
                    return $this->failed(_1000408);
                }
                
                $res = $safe->password($input->json_stream(), $safe_info['id']);
                break;
            default :
                return $this->failed(_1000005);
        }
        
        if ($res == false){
            return $this->failed(_1000006);
        }
        
        return $this->succ();
    }
    
    //发送修改密码短信
    public function smsAction() {
        $input = $this->input();
        $rule = array(
            'phone' => 'required|phone',
            'terminal'=>'required',
        );
        
        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }
        
        //检测自己是哪种登录，如果是微信登录
        $user = new UserModel();
        $user_info = $user->getUserInfoByMysql($_SESSION['user_id'], "type, phone");
        if (!$user_info){
            return $this->failed(_1000106);
        }
        if ($user_info['type'] != 0){
            return $this->failed(_1000107);
        }
        if ($user_info['phone'] != $input->json_stream('phone')){
            return $this->failed(_1000108);
        }

        $inputdata['login_name']=$input->json_stream("phone");
        //$postdata=getPostData('identifyCode',json_stream("terminal"),$inputdata);
        $result=postData('identifyCode',$input->json_stream("terminal"),$inputdata);
        if($result['code']=='0')
        {
            return $this->succ();
        }
        else
        {
            return $this->failed($result['msg']);
        }

       /* //发送短信
        $smsModel = new SmsModel();
        //$userModel->sendSms($input->json_stream("phone"));
        $res = $smsModel->userSms($input->json_stream('phone'), 3);
        if ($res === TRUE){
            return $this->succ();
        }
        return $this->failed($res);*/
    }
    
    //重置密码
    public function resetSafeAction() {
        $input = $this->input();
        $rule = array(
            'password' => 'required',
            'code' => 'required',
            'phone' => 'required|phone',
            'terminal'=>'required'
        );
        
        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }
        
        //现在短信code
        /*$smsModel = new SmsModel();
        $sms_info = $smsModel->getOne($input->json_stream("phone"), 3, $input->json_stream("code"));
        if (!$sms_info){
            return $this->failed(_1000202);
        }
        
        if ($sms_info['status'] == 0){
            return $this->failed(_1000202);
        }
        
        if ($sms_info['is_used'] == 1){
            return $this->failed(_1000202);
        }
        
        if (time() > intval($sms_info['c_time'] + $sms_info['expire'])){
            return $this->failed(_1000205);
        }
        
        if (!$smsModel->updateUsed($sms_info['id'])){
            return $this->failed(_1000003);
        }*/

        //去A平台校验验证码
        $inputdata['user_id']=$input->json_stream("phone");
        $inputdata['verification_code']=$input->json_stream("code");
        $result=postData('checkVerificationCode',$input->json_stream("terminal"),$inputdata);
        if($result['code']=='0')
        {
            $safe = new SafeModel();
            $safe_info = $safe->getOne($_SESSION['user_id']);
            if (!$safe_info){
                return $this->failed(_1000401);
            }
            $res = $safe->password($input->json_stream(), $safe_info['id']);
            if ($res == false){
                return $this->failed(_1000006);
            }

            return $this->succ();
        }
        else
        {
            return $this->failed($result['msg']);
        }

        

    }

    //验证密码
    public function checkPwdAction(){
        $input = $this->input();
        $rule = array(
            'password' => 'required'  //确认密码
        );
        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }
        if (!checkPassword($input->json_stream('password'))){
            return $this->failed(_1000007);
        }
        $safe = new SafeModel();
        $safe_info = $safe->getOne($_SESSION['user_id']);
        if($safe_info['password']!=md5($input->json_stream('password'))){
            return $this->failed(_1000408);
        }
        return $this->succ();
    }
    
}
