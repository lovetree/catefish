<?php

class WxServer {

    public $app_id;
    public $app_secret;
    public $access_token;
    public $refresh_token;
    public $openid;
    private $_last_output;

    public function __construct($app_id, $app_secret) {
        $this->app_id = $app_id;
        $this->app_secret = $app_secret;
    }

    /**
     * @param string $code 授权码
     */
    public function getToken(string $code) {
        logMessage('wx server[getToken] start');

        $params = array(
            'appid' => $this->app_id,
            'secret' => $this->app_secret,
            'code' => $code,
            'grant_type' => 'authorization_code',
        );
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token';
        $content = $this->getApi($url, $params);
        if ($this->error() || !$content || !$this->is_json($content)) {
            return false;
        }
        $data = json_decode($content, true);
        $this->access_token = $data['access_token'];
        $this->refresh_token = $data['refresh_token'];
        $this->openid = $data['openid'];

        logMessage('wx server[getToken] end');
        return $data;
    }

    /**
     * 刷新令牌
     */
    public function refreshToken(){
        logMessage('wx server[refreshToken] start');
        
        if (empty($this->refresh_token)) {
            logMessage('wx server: empty refresh_token');
            return false;
        }
        $params = array(
            'appid' => $this->app_id,
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->refresh_token
        );
        $url = 'https://api.weixin.qq.com/sns/oauth2/refresh_token';
        $content = $this->getApi($url, $params);
        if ($this->error() || !$content || !$this->is_json($content)) {
            return false;
        }
        $data = json_decode($content, true);
        $this->access_token = $data['access_token'];
        $this->refresh_token = $data['refresh_token'];
        $this->openid = $data['openid'];
        
        logMessage('wx server[refreshToken] end');
        return $data;
    }    
    
    /**
     * 获取用户信息
     * @return boolean
     */
    public function getUserInfo() {
        logMessage('wx server[getUserInfo] start');
        if (empty($this->access_token)) {
            logMessage('wx server: empty access_token');
            return false;
        }
        $params = array(
            'access_token' => $this->access_token,
            'openid' => $this->openid,
            'lang' => 'zh_CN'
        );
        $url = 'https://api.weixin.qq.com/sns/userinfo';
        $content = $this->getApi($url, $params);
        if ($this->error() || !$content || !$this->is_json($content)) {
            return false;
        }
        $data = json_decode($content, true);
        logMessage('wx server[getUserInfo] end');
        return $data;
    }

    /**
     * 下单
     */
    public function placeOrder(){
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    }
    
    /**
     * 获取接口响应的数据
     * @param string $url
     * @param array $data
     */
    protected function getApi(string $url, array $data = array()) {
        try {
            $api_url = $url . '?' . http_build_query($data);
            logMessage('wx req: ' . $api_url);
            //发情请求
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    // 跳过证书检查 
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);        // 从证书中检查SSL加密算法是否存在
            $content = curl_exec($ch);
            curl_close($ch);
            //请求结束
            logMessage('wx resp: ' . $content);
            if(false === $content){
                return false;
            }
            $this->_last_output = $content;
            return $content;
        } catch (Exception $ex) {
            logMessage($ex->getMessage(), LOG_ERR);
        }
        return false;
    }

    /**
     * 判断是否json
     * @param string $string
     * @return bool
     */
    private function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * 判断响应的内容是否有错误消息
     * @return boolean
     */
    public function error() {
        //判断是否包含错误吗
        if (preg_match('/"errcode":\s?[^0]/', $this->_last_output)) {
            return true;
        }
        return false;
    }

}
