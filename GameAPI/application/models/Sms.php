<?php

class SmsModel extends BaseModel {

    const DB = R_SMS_DB;
    
    const SMS_TYPE_REGISTER = 1;       //注册
    const SMS_TYPE_RESET    = 2;          //重置
    const SMS_TYPE_SAFE_RESET    = 3;          //找回保险箱密码
    const SMS_CODE_NUMBER   = 6;                //短信验证码个数
    const SMS_RULE_NUMBER   = 1;//数字
    const SMS_RULE_NUMBER_INFO = "0123456789";
    const SMS_RULE_CHAR     = 2;//字母
    const SMS_RULE_CHAR_INFO = "qwertyuiopasdfghjklzxcvbnm";
    const SMS_RULE_BLEND    = 3;//数字字母混合
    const SMS_RULE_BLEND_INFO = "0123456789qwertyuiopasdfghjklzxcvbnm";
    
    const SMS_CODE = [
        200  =>  '操作成功',
        201  =>  '客户端版本不对，需升级sdk',
        301  =>  '被封禁',
        302  =>  '用户名或密码错误',
        315  =>  'IP限制',
        403  =>  '非法操作或没有权限',
        404  =>  '对象不存在',
        405  =>  '参数长度过长',
        406  =>  '对象只读',
        408  =>  '客户端请求超时',
        413  =>  '验证失败(短信服务)',
        414  =>  '参数错误',
        415  =>  '客户端网络问题',
        416  =>  '频率控制',
        417  =>  '重复操作',
        418  =>  '通道不可用(短信服务)',
        419  =>  '数量超过上限',
        422  =>  '账号被禁用',
        431  =>  'HTTP重复请求',
        500  =>  '服务器内部错误',
        503  =>  '服务器繁忙',
        508  =>  '消息撤回时间超限',
        509  =>  '无效协议',
        514  =>  '服务不可用',
        998  =>  '解包错误',
        999  =>  '打包错误',
    ];
    /**
     * 用户发送短信
     * @param $phone
     * @param string $type
     * @return string=失败信息；true=成功
     */
    public function userSms($phone, $type){
        $redis = PRedis::instance();
        $redis->select(SELF::DB);
        //检查该账户单天发送短信的次数是否超出限制
        $score = $redis->zScore(PK_USER_SMS . date('Y-m-d'), $phone);
        switch ($score){
            case $this->config()->get('sms.limit'):
                //发送短信超出限制，提示用户单天短信已使用完
                return _1000201;
            default :
                //发送短信,获取模板信息
//                $codeNumber = $this->config()->get('sms.number') ?? SELF::SMS_CODE_NUMBER;
//                switch ($this->config()->get('sms.rule')){
//                    case SELF::SMS_RULE_CHAR:
//                        //纯字母
//                        $rule = SELF::SMS_RULE_CHAR_INFO;
//                        break;
//                    case SELF::SMS_RULE_BLEND:
//                        //混合
//                        $rule = SELF::SMS_RULE_BLEND_INFO;
//                        break;
//                    default :
//                        //默认纯数字
//                        $rule = SELF::SMS_RULE_NUMBER_INFO;
//                }
//                $code = $this->getCode($codeNumber, $rule);
                //$content = sprintf($this->config()->get('sms.type_' . $type), $code, $this->config()->get('sms.expire'));
                $templateid = $this->config()->get('sms.type_' . $type);
                //插入一条数据
                $db = $this->DB();
                $db->beginTransaction();
                $data = array();
                $data['phone'] = $phone;
                $data['type'] = $type;
                $data['content'] = $templateid;
                $data['code'] = 0;
                $data['expire'] = $this->config()->get('sms.expire');
                $data['c_time'] = time();
                if (!$db->insert('ms_sms', array_fetch($data, array('phone', 'type', 'content', 'code', 'expire', 'c_time')))) {
                    logMessage('ms_sms 插入失败;' . var_export($data, true));
                    $db->rollback();
                    return _1000001;
                }
                
                $status = $this->sendSms($phone, $templateid);
                if (!$status['status']){
                    logMessage('sms 发送短信失败;' . $status['msg']);
                    //更新数据错误信息
                    try{
                        $db->update('ms_sms', array('errMsg'=>$status['msg']), 'id = ' . $db->lastInsertId());
                        $db->commit();
                    } catch (Exception $ex){
                        logMessage($ex->getTraceAsString());
                        $db->rollback();
                    }
                    return $status['msg'];
                }
                
                try{
                    $db->update('ms_sms', array('status'=>1, 'code'=>$status['obj']), 'id = ' . $db->lastInsertId());
                    $db->commit();
                } catch (Exception $ex){
                    logMessage($ex->getTraceAsString());
                    $db->rollback();
                    return _1000001;
                }
                
                if ($score == NULL){
                    $redis->zAdd(PK_USER_SMS . date("Y-m-d"), 1, $phone);
                }else{
                    $redis->zIncrBy(PK_USER_SMS . date("Y-m-d"), +1, $phone);
                }
                
                return true;
        }
    }
    
    public function getOne($phone, $type, $code){
        $sql = <<<SQL
                select id,is_used,status,c_time,expire from ms_sms where phone = ? and type = ? and code = ?  order by id desc limit 1
SQL;
        return $this->DB()->query_fetch($sql, array($phone, $type, $code));
    }
    
    public function updateUsed($id){
        $db = $this->DB();
        try{
            $db->update('ms_sms', array('is_used'=>1, 'u_time'=>time()), 'id = ' . $id);
        } catch (Exception $ex){
            logMessage($ex->getTraceAsString());
            return false;
        }
        return true;
    }
    
    private function getCode($number, $rule){
        $code = "";
        for ($i=0;$i<$number;$i++){
            $code .= $rule[mt_rand(0, strlen($rule)-1)];
        }
        return $code;
    }
    
    /**
     * 发送短信
     * @param $phone
     * @param string $content
     */
    private function sendSms($phone, $templateid){
        $AppKey = $this->config()->get('sms.AppKey');
        $url = $this->config()->get('sms.url');
        $Secret = $this->config()->get('sms.Secret');
        $time = time();
        $Nonce = $this->getNonceStr();
        
        $params = [];
        $params['mobile'] = $phone;
        $params['templateid'] = $templateid;
        $params['codeLen'] = $this->config()->get('sms.number');
        
        $header = [];
        $header[] = "AppKey:$AppKey";
        $header[] = "CurTime:$time";
        $CheckSum = sha1($Secret . $Nonce . $time);
        $header[] = "CheckSum:$CheckSum";
        $header[] = "Nonce:$Nonce";
        $header[] = "Content-Type:application/x-www-form-urlencoded;charset=utf-8";
        
        $res = $this->http_post_request($url, $params, $header);
        if (!$res) return ['status'=>false];
        $res = json_decode($res, TRUE);
        if ($res['code'] != 200){
            return ['status'=>false, 'msg'=>$res['msg']];
        }
        return ['status'=>true, 'obj'=>$res['obj']];
    }
    
    public static function getNonceStr($length = 32) {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    
}
