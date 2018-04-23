<?php

class AibeiModel extends BaseModel {
    private $_iapppayCpUrl;
    private $_orderUrl;
    private $_appid;
    private $_appkey;
    private $_platpkey;
    private $_notifyurl;
    public $_transid;
    private $_appuserid;
    private $_log_file;
    
    public function __construct() {
        $aibei = $this->config()->get("aibei");
        $this->_iapppayCpUrl = $aibei['iapppayCpUrl'];
        $this->_orderUrl = $this->_iapppayCpUrl . $aibei['orderUrl'];
        //应用编号
        $this->_appid = $aibei['appid'];
        //应用私钥
        $this->_appkey = $aibei['appkey'];
        //平台公钥
        $this->_platpkey = $aibei['platpkey'];
        //支付成功后的回调地址
        $this->_notifyurl = toUrl($aibei['notifyurl']);
        //标识符
        $this->_appuserid = $aibei['appuserid'];
        //日志文件
        $this->_log_file = $aibei['order_log'];
    }

    private function logMessage($msg) {
        $logmsg = sprintf("[%s] %s\r\n", date('Y/m/d H:i:s'), $msg);
        error_log($logmsg, 3, $this->_log_file);
    }

    //支付成功后回调
    public function notify() {
        $this->logMessage('收到爱贝回调：'. json_encode($_POST));

        $string = $_POST; //接收post请求数据
        if ($string == null) {
            
            $this->logMessage("请使用post方式提交数据");
            return "请使用post方式提交数据";
        }
        
        $transdata = $string['transdata'];
        if (stripos("%22", $transdata)) {
            //判断接收到的数据是否做过 Urldecode处理，如果没有处理则对数据进行Urldecode处理
            $string = array_map('urldecode', $string);
        }
        
        //把数据组装成验签函数要求的参数格式
        $respData = 'transdata=' . $string['transdata'] . '&sign=' . $string['sign'] . '&signtype=' . $string['signtype'];
        //验签函数parseResp（） 中 只接受明文数据。数据如：transdata={"appid":"3003686553","appuserid":"10123059","cporderid":"1234qwedfq2as123sdf3f1231234r","cpprivate":"11qwe123r23q232111","currency":"RMB","feetype":0,"money":0.12,"paytype":403,"result":0,"transid":"32011601231456558678","transtime":"2016-01-23 14:57:15","transtype":0,"waresid":1}&sign=jeSp7L6GtZaO/KiP5XSA4vvq5yxBpq4PFqXyEoktkPqkE5b8jS7aeHlgV5zDLIeyqfVJKKuypNUdrpMLbSQhC8G4pDwdpTs/GTbDw/stxFXBGgrt9zugWRcpL56k9XEXM5ao95fTu9PO8jMNfIV9mMMyTRLT3lCAJGrKL17xXv4=&signtype=RSA
        if (!$this->parseResp($respData, $this->_platpkey, $respJson)) {
            //验签失败
            $this->logMessage("parseResp failed");
            return "parseResp failed";
        }
        $this->logMessage("验签正确\n");
        //以下是 验签通过之后 对数据的解析。
        $transdata = $string['transdata'];
        $arr = json_decode($transdata);
        $cporderid = $arr->cporderid;
        $money = $arr->money;
        $result = $arr->result;
        $transid = $arr->transid;
        $paytype = $arr->paytype;

        if ($result != 0){
            $this->logMessage("$cporderid 订单未完成支付");
            return "订单未完成支付";
        }
        $this->logMessage("返回数据获取\n");
        //交易成功，开始订单
        $sql = <<<SQL
                select id,goods_id,totalf_fee,status,user_id from ms_order where transaction_id = "$transid" and order_id = "$cporderid"
SQL;
        $order = $this->DB()->query_fetch($sql);$this->logMessage("订单校验中途1\n");
        if (!$order){
            $this->logMessage("$cporderid 订单不存在");
            return "订单不存在";
        }

        if ($order['status'] == 1){
            $this->logMessage("$cporderid 订单已完成");
            return "订单已完成";
        }
        $this->logMessage("订单校验中途\n");
        if ($order['status'] == 2){
            $this->logMessage("parseResp failed1");
            return "$cporderid 订单已退款";
        }
        
        if ($money*100 != intval($order['totalf_fee'])){
            $this->logMessage("parseResp failed2");
            return "$cporderid 订单金额和支付金额不一致";
        }

        $this->logMessage("订单校验成功\n");
        //合法交易，开始执行交易后逻辑，暂定义为购买钻石
        $db = $this->DB();
        //获取商品信息
        $sql = <<<SQL
                select present,worth from ms_goods where id = ?
SQL;
        $goods = $db->query_fetch($sql, [$order['goods_id']]);
        $credit = 0;
        if ($goods){
            $credit = intval($goods['present']) + intval($goods['worth']);
        }
        $this->logMessage("准备入库\n");
        $db->beginTransaction();
        
        if (!$db->update('ms_order', ['status'=>1,'update_date'=>time(), 'paytype'=>$paytype], 'id = ' . $order['id'])){
            $db->rollBack();
            $this->logMessage("parseResp failed");
            return "$cporderid 修改订单状态失败";
        }
        $this->logMessage("入库成功\n");
        if ($credit){
            if (!$this->onChangeUserCredit($order['user_id'], $credit)){
                $db->rollBack();
                $this->logMessage("parseResp failed");
                return "$cporderid 增加钻石失败";
            }

            $userData = new Data\UserModel($order['user_id']);
            $this->logMessage("准备推送\n" );
            $pusRes = realTimePush($order['user_id'], json_encode(['type'=>'credit', 'data'=>['total'=>$userData->get('credit'), 'o_time'=>time()]]));
            $this->logMessage("推送结果：$pusRes\n" );
        }
        $this->logMessage("加钻成功$credit\n" );

        $user = new UserModel();
        $user_info = $user->getOneUserInfo($order['user_id'], ['id', 'user_pay', 'nickname']);
        $user_pay=$order['totalf_fee'] / 100 + $user_info['user_pay'];
        try {
            $db->update('ms_user_info', ['user_pay' => ($user_pay)],
                'id = ' . $user_info['id']);
        } catch (Exception $ex) {
            $db->rollBack();
            logMessage($ex->getTraceAsString());
            return _1000603;
        }
        $this->logMessage("更新累计充值金额成功$user_pay\n" );
        $db->commit();
        WebApp::inform(WebApp::UPGRADE_VIP, [$order['user_id']]);

        //推送数据
        $pushData = [
            'cmd' => 'credit',
            'data' => [
                'change' => $goods['worth'],
                'total'  =>  $credit,
                'o_time' => time()
            ]
        ];
        //向客户端推送金币变更消息
        curl_get(config_item('push.ip').'/phppushtouser?userid=' . $order['user_id'] . '&phpdata=' . json_encode($pushData));

        $detail = [];
        $detail['gid'] = $order['goods_id'];
        $detail['count'] = $credit;
        $detail['why'] = "商城购买钻石";
        $this->_addUserLog($order['user_id'], LOG_ACT_SHOP, json_encode($detail));
        $this->logMessage("写日志成功$credit\n" );
        return "success";
    }
    
    //提交预支付订单
    public function order(int $g_id,$user_id) {
        //获取商品信息
        //查询商品是否在售
        $shopModel = new ShopModel();
        $goods = $shopModel->getActiveGoods($g_id);
        if (false === $goods) {
            //商品未上架或已经下架
            return _1000801;
        }
        
        //购买钻石
        if ($goods['source'] != 0 && $goods['type'] != 0){
            return _1000804;
        }
        
        //先生成订单
        //开始下单
        $goods_detail = [
            'items' => $goods['items'] ?? '',
            'version' => $goods['version'] ?? '',
            'name' => $goods['show_name'] ?? ''
        ];
        //生成订单ID
        $order_id = $this->getcporderid();
        $data = array(
            'order_id' => $order_id,
            'user_id' => $user_id?$user_id:$_SESSION['user_id'],
            'goods_id' => $g_id,
            'goods_detail' => json_encode($goods_detail),
            'totalf_fee' => $goods['total_fee'],
            'actually_fee' => $goods['actually_fee'],
            'coupon_fee' => $goods['coupon_fee'] ?? 0,
            'coupon_detail' => $goods['coupon_detail'] ?? '',
            'ip' => $this->input()->ip_address(),
            'trade_type' => 'APP',
            'created_date' => time(),
            'expired_date' => strtotime('+2 hour'),
            'update_date' => time(),
            'status' => 0
        );
        
        if (!$this->DB()->insert('ms_order', $data)) {
            //订单创建失败
            return _1000803;
        }
        $id = $this->DB()->lastInsertId();
        
        $order = [];
        $order['appid'] = $this->_appid;     //应用编号
        $order['waresid'] = intval($goods['aibei_waresid']);               //商品编号
        $order['cporderid'] = $order_id;     //商户订单号,确保该参数每次 都不一样。否则下单会出问题。
        $order['price'] = $goods['total_fee'] / 100;   //单位：元
        $order['currency'] = 'RMB';  //货币类型以及单位：RMB – 人民币（单位：元）
        //用户在商户应用的唯一标识，建议为用户帐号。对于游戏，需要区分到不同区服，#号分隔；比如游戏帐号abc在01区，则传入“abc#01”
        $order['appuserid'] = $this->_appuserid .'#'. ($user_id?$user_id:$_SESSION['user_id']);  //用户在商户应用的唯一标识
        //$order['cpprivateinfo'] = '11qwe123r23q232111';  //商户私有信息，支付完成后发送支付结果通知时会透传给商户
        $order['notifyurl'] = $this->_notifyurl;

        
        //组装请求报文  对数据签名
        $reqData = $this->composeReq($order, $this->_appkey);
        $this->logMessage("下单参数：".$reqData);
        //发送到爱贝服务后台请求下单
        $respData = $this->request_by_curl($this->_orderUrl, $reqData, 'order test');

        //验签数据并且解析返回报文
        if (!$this->parseResp($respData, $this->_platpkey, $respJson)) {
            return _1000803;
        }
        
        //修改订单
        if (!$this->DB()->update('ms_order', array('transaction_id' => $respJson->transid), 'id = ' . $id)){
            return _1000803;
        }

        $this->_transid = $respJson->transid;
        return true;
    }
    
    private function getcporderid() {
        return 'APP' . date('YmdHis') . bin2hex(random_bytes(4));
    }
    
    /*     * RSA签名
     * $data待签名数据
     * $priKey商户私钥
     * 签名用商户私钥
     * 使用MD5摘要算法
     * 最后的签名，需要用base64编码
     * return Sign签名
     */

    private function sign($data, $priKey) {
        //转换为openssl密钥
        $res = openssl_get_privatekey($priKey);

        //调用openssl内置签名方法，生成签名$sign
        openssl_sign($data, $sign, $res, OPENSSL_ALGO_MD5);

        //释放资源
        openssl_free_key($res);

        //base64编码
        $sign = base64_encode($sign);
        return $sign;
    }

    /*     * RSA验签
     * $data待签名数据
     * $sign需要验签的签名
     * $pubKey爱贝公钥
     * 验签用爱贝公钥，摘要算法为MD5
     * return 验签是否通过 bool值
     */

    private function verify($data, $sign, $pubKey) {
        //转换为openssl格式密钥
        $res = openssl_get_publickey($pubKey);

        //调用openssl内置方法验签，返回bool值
        $result = (bool) openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_MD5);

        //释放资源
        openssl_free_key($res);

        //返回资源是否成功
        return $result;
    }

    /**
     * 解析response报文
     * $content  收到的response报文
     * $pkey     爱贝平台公钥，用于验签
     * $respJson 返回解析后的json报文
     * return    解析成功TRUE，失败FALSE
     */
    private function parseResp($content, $pkey, &$respJson) {
        $arr = array_map(create_function('$v', 'return explode("=", $v);'), explode('&', $content));
        foreach ($arr as $value) {
            $resp[($value[0])] = $value[1];
        }

        //解析transdata
        if (array_key_exists("transdata", $resp)) {
            $respJson = json_decode($resp["transdata"]);
        } else {
            return FALSE;
        }

        //验证签名，失败应答报文没有sign，跳过验签
        if (array_key_exists("sign", $resp)) {
            //校验签名
            $pkey = $this->formatPubKey($pkey);
            return $this->verify($resp["transdata"], $resp["sign"], $pkey);
        } else if (array_key_exists("errmsg", $respJson)) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * curl方式发送post报文
     * $remoteServer 请求地址
     * $postData post报文内容
     * $userAgent用户属性
     * return 返回报文
     */
    private function request_by_curl($remoteServer, $postData, $userAgent) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remoteServer);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        $data = urldecode(curl_exec($ch));
        curl_close($ch);

        return $data;
    }

    /**
     * 组装request报文
     * $reqJson 需要组装的json报文
     * $vkey  cp私钥，格式化之前的私钥
     * return 返回组装后的报文
     */
    private function composeReq($reqJson, $vkey) {
        //获取待签名字符串
        $content = json_encode($reqJson);
        //格式化key，建议将格式化后的key保存，直接调用
        $vkey = $this->formatPriKey($vkey);

        //生成签名
        $sign = $this->sign($content, $vkey);

        //组装请求报文，目前签名方式只支持RSA这一种
        $reqData = "transdata=" . urlencode($content) . "&sign=" . urlencode($sign) . "&signtype=RSA";

        return $reqData;
    }

    /*     * 格式化公钥
     * $pubKey PKCS#1格式的公钥串
     * return pem格式公钥， 可以保存为.pem文件
     */
    private function formatPubKey($pubKey) {
        $fKey = "-----BEGIN PUBLIC KEY-----\n";
        $len = strlen($pubKey);
        for ($i = 0; $i < $len;) {
            $fKey = $fKey . substr($pubKey, $i, 64) . "\n";
            $i += 64;
        }
        $fKey .= "-----END PUBLIC KEY-----";
        return $fKey;
    }

    /*     * 格式化公钥
     * $priKey PKCS#1格式的私钥串
     * return pem格式私钥， 可以保存为.pem文件
     */

    private function formatPriKey($priKey) {
        $fKey = "-----BEGIN RSA PRIVATE KEY-----\n";
        $len = strlen($priKey);
        for ($i = 0; $i < $len;) {
            $fKey = $fKey . substr($priKey, $i, 64) . "\n";
            $i += 64;
        }
        $fKey .= "-----END RSA PRIVATE KEY-----";
        return $fKey;
    }

}
