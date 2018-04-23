<?php

class UserController extends BaseController {

    /**
     * 用户登录
     */
    public function loginAction() {
        $input = $this->input();
        //验证参数
        if (!$this->validation($input->post(), array(
                    'user' => 'required',
                    'pass' => 'required',

                ))) {
            return false;
        }
        /*if($input->post('user')=='admin'&&!$input->post('code')){
            return $this->failed('admin账户需要确认手机验证码');
            exit;
        }*/
        $user = new UserModel();
        if ($user->isLogin()) {
            return $this->succ();
        }
        /*if($input->post('user')=='admin'){
            $phone = $user->getphone($input->post('user'));

            $result = $user->checkCode($phone,$input->post('code'));

            if($result['result']!=200){
                return $this->failed($result['msg']);
                exit;
            }
        }*/

        if(!$user->login($input->post('user'), $input->post('pass'))){
            return $this->failed('用户名或密码不存在');
            exit;
        }

        
        return $this->succ();
    }
    
    /**
     * 用户登出
     */
    public function logoutAction(){
        $user = new UserModel();
        $user->logout();
        return $this->succ();
    }
    
    /**
     * 获取信息
     * 包含用户信息，导航模块等
     */
    public function infoAction(){
        $user = new UserModel();
        $info = $user->getInfo();
        $navs = $user->getNav();
        return $this->succ([
            'info' => $info,
            'nav' => $navs
        ]);
    }
    /**
     * 发送短信
     */
    public function sendSmsAction(){
        $input = $this->input();
        //验证参数
        if (!$this->validation($input->request(), array(
            'user' => 'required',
        ))) {
            return false;
        }
        $admin = new UserModel();
        $phone = $admin->getphone($input->request()['user']);
        if(!$phone){
            $this->failed('未绑定手机号');
            exit;
        }
        $sms = new SmsModel();

        return $this->succ([$sms->userSms($phone,4)]);
    }



}
