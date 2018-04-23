<?php

class SmsController extends \BaseController {
    
    /**
     * 用户发送短信
     */
    public function sendSmsAction(){
        $input = $this->input();
        $rule = array(
            'phone' => 'required|phone',
            'type'  => 'required|inside:1:2'
        );
        
        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }
        
        $phone = $input->json_stream("phone");
        $type = $input->json_stream("type");
        $userModel = new UserModel();
        $userInfo = $userModel->getInfoByPhone($phone);
        switch ($type){
            case SmsModel::SMS_TYPE_RESET:
                if (!$userInfo){
                    //账号未注册，请先注册
                    return $this->failed(_1000101);
                }
                //判断账户情况
                switch ($userInfo['status']){
                    case -1:
                        //已删除
                        break;
                    case 0:
                        //冻结
                        return $this->failed(_1000105, RCODE_ACCOUNT_FREEZING);
                    case 1:
                        //正常
                        break;
                    case 2:
                        //测试员
                        break;
                }
                break;
            case SmsModel::SMS_TYPE_REGISTER:
                if ($userInfo){
                    //账号已注册
                    return $this->failed(_1000102);
                }
                break;
            default :
                
        }
        
        $smsModel = new SmsModel();
        //$userModel->sendSms($input->json_stream("phone"));
        $res = $smsModel->userSms($phone, $type);
        if ($res === TRUE){
            return $this->succ();
        }
        return $this->failed($res);
    }
    
    /**
     * 验证短信信息
     */
    public function validSmsAction(){
        $input = $this->input();
        $rule = array(
            'phone' => 'required|phone',
            'type'  => 'required|inside:1:2:3',
            'code'  => 'required',
        );
        
        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }
        
        $phone = $input->json_stream("phone");
        $type = $input->json_stream("type");
        $code = $input->json_stream("code");
        
        $smsModel = new SmsModel();
        //$userModel->sendSms($input->json_stream("phone"));
        $sms_info = $smsModel->getOne($phone, $type, $code);
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
        }
        
        return $this->succ();
    }
}
