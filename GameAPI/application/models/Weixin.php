<?php

class WeixinModel extends BaseModel {
    
    public function getWxUserInfo() : string {
        $input = $this->input();
        $code = $input->get('code');
        $wxInfo = [];
        $wxInfo['wx_appid'] = $this->config()->get('client.wx_appid');
        $wxInfo['wx_secret'] = $this->config()->get('client.wx_secret');
        if (!$code){
            $this->oauth($wxInfo);
        }
        if ($openId = $this->getOpenID($wxInfo, $code)) {
            return $openId;
        }
        return "";
    }

    public function oauth(array $wxInfo) {
        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if (isset($_SERVER['HTTPS'])){
            $url = $_SERVER['HTTPS'] == 'on' ? 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] : $url;
        }
        header('Location:https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$wxInfo['wx_appid'].'&redirect_uri='.urlencode($url).'&response_type=code&scope=snsapi_base&state=state#wechat_redirect');
        exit;
    }
    
    public function getOpenID(array $wxInfo, string $code) {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$wxInfo['wx_appid']."&secret=".$wxInfo['wx_secret']."&code=".$code."&grant_type=authorization_code";
        $result = $this->http_get_request($url);
        if(empty($result)){
            return false;
        }
        $result = json_decode($result,true);
        if(!empty($result['openid'])){
            return $result["openid"];
        }
        return false;
    }
    
    function http_get_request($url, $param = NULL, $header = NULL) {
        if(!empty($param)) {
                if(strpos($url, "?") ===false) $url .= "?".http_build_query($param);
                else $url .= "&".http_build_query($param);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if(is_array($header)) curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $res = curl_exec( $ch );
        curl_close( $ch );
        return $res;
    }
}
