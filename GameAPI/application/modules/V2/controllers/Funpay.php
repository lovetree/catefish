<?php

class FunpayController extends \BaseController {
    /**
     * 回调
     */
    public function preturnAction(){
        $this->getView()->assign('pkey', config_item('funpay.leying_key'));
        $this->display('preturn');
    }

    /**
     *通知
     */
    public function pnoticeAction(){
        $orderID = $_REQUEST["orderID"];
        $resultCode = $_REQUEST["resultCode"];
        $stateCode = $_REQUEST["stateCode"];
        $orderAmount = $_REQUEST["orderAmount"];
        $payAmount = $_REQUEST["payAmount"];
        $acquiringTime = $_REQUEST["acquiringTime"];
        $completeTime = $_REQUEST["completeTime"];
        $orderNo = $_REQUEST["orderNo"];
        $partnerID = $_REQUEST["partnerID"];
        $remark = $_REQUEST["remark"];
        $charset = $_REQUEST["charset"];
        $signType = $_REQUEST["signType"];
        $signMsg = $_REQUEST["signMsg"];

        $src = "orderID=".$orderID
            ."&resultCode=".$resultCode
            ."&stateCode=".$stateCode
            ."&orderAmount=".$orderAmount
            ."&payAmount=".$payAmount
            ."&acquiringTime=".$acquiringTime
            ."&completeTime=".$completeTime
            ."&orderNo=".$orderNo
            ."&partnerID=".$partnerID
            ."&remark=".$remark
            ."&charset=".$charset
            ."&signType=".$signType;

        if($_REQUEST["charset"] == 1)
            $charset = "1";

        if(2 == $signType) //md5验签
        {
            $pkey = config_item('funpay.leying_key');
            $src = $src."&pkey=".$pkey;
            $ret2 = ($signMsg == md5($src));
        }

        //记录接收到的参数到服务器本地文件
        error_log('['.date('Y-m-d H:i:s').']receive notice:'.$src. $ret2 ."\n", 3, config_item('funpay.order_log'));
        //验签失败不处理
        if(!$ret2) {
            exit('fail');
        }

        //订单去重处理
        $redis = PRedis::instance();
        $redis->select(0);
        $result = $redis->incr(RK_GS_ORDER_FLAG.$orderID);
        if($result > 0){
            exit('重复订单');
        }

        //用户id
        $userId = explode('=', explode(',', $remark)[0])[1];
        //业务类型
        $serviceType = explode('=', explode(',', $remark)[1])[1];
        //变更用户金币
        $userModel = new \Data\UserModel($userId);
        $userModel->addGold($payAmount);

        //入库
        $rechargeLog = [
            'recharge_time' => $acquiringTime,
            'service_type' => $serviceType,
            'user_id' => $userId,
            'order_id' => $orderID,
            'order_flow' => $orderNo,
            'order_amount' => $orderAmount,
            'payed_amount' => $payAmount,
            'recharge_type' => 'funpay',
            'gold_recharge' => $payAmount,
            'gold_after' => $userModel->get('gold'),
            'created_at' => time()
        ];

        $status = $this->DB()->insert('ms_recharge_log', $rechargeLog);

        if($status){
            error_log('['.date('Y-m-d H:i:s').']orderId--'."$orderID 入库成功\n", 3, config_item('funpay.order_log'));
            //推送数据
            $pushData = [
                'cmd' => 'gold',
                'data' => [
                    'change' => $payAmount,
                    'total' => $userModel->get('gold'),
                    'o_time' => time()
                ]
            ];
            //向客户端推送金币变更消息
            $result = curl_get(config_item('push.ip').'/phppushtouser?userid=' . $userId . '&phpdata=' . json_encode($pushData));
            if($result['code'] == 0 && $result['data'] == 'success'){
                error_log('['.date('Y-m-d H:i:s').']orderId--'."$orderID 推送客户端成功\n", 3, config_item('funpay.order_log'));
            }else{
                error_log('['.date('Y-m-d H:i:s').']orderId--'."$orderID 推送客户端失败\n", 3, config_item('funpay.order_log'));
            }
        }else{
            error_log('['.date('Y-m-d H:i:s').']orderId--'."$orderID 入库失败\n", 3, config_item('funpay.order_log'));
        }

        echo 'success';
    }

    /**
     * 下单
     */
    public function porderAction(){
        if(!isset($_GET['user_id']) || !isset($_GET['goods_count'])){
            exit('支付失败，请联系客服');
        }

        /**
         * 订单信息生产
         */
        //订单号
        $orderId = date('YmdHis').$_GET['user_id'];
        //商户显示
        $displayName = '东晴';
        //商品名称
        $goodsName = 'gold';
        //商品数量
        $goodsCount = $_GET['goods_count'];
        //订单金额
        $totalAmount = $goodsCount * 100;

        $version = '1.0';
        $serialID = $orderId;
        $submitTime = date('YmdHis');
        $failureTime = date('YmdHis', strtotime('+1 year'));
        $customerIP = '';
        $orderDetails = "$orderId,$totalAmount,$displayName,$goodsName,$goodsCount";
        $totalAmount = $totalAmount;
        $type = '1000';
        $buyerMarked = '';
        $payType = 'ALL';
        $orgCode = '';
        $currencyCode = '1';
        $directFlag = '0';
        $borrowingMarked = '0';
        $couponFlag = '1';
        $platformID = '';
        $returnUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/v1/funpay/preturn';
        $noticeUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/v1/funpay/pnotice';
        $partnerID = config_item('funpay.partner_id');
        $remark = 'user_id='.$_GET['user_id'] . ',service_type=' . (isset($_GET['type']) ? $_GET['type'] : 'player');
        $charset = "1";
        $signType = "2";

        $signMsg = "version=".$version.
            "&serialID=".$serialID.
            "&submitTime=".$submitTime.
            "&failureTime=".$failureTime.
            "&customerIP=".$customerIP.
            "&orderDetails=".$orderDetails.
            "&totalAmount=".$totalAmount.
            "&type=".$type.
            "&buyerMarked=".$buyerMarked.
            "&payType=".$payType.
            "&orgCode=".$orgCode.
            "&currencyCode=".$currencyCode.
            "&directFlag=".$directFlag.
            "&borrowingMarked=".$borrowingMarked.
            "&couponFlag=".$couponFlag.
            "&platformID=".$platformID.
            "&returnUrl=".$returnUrl.
            "&noticeUrl=".$noticeUrl.
            "&partnerID=".$partnerID.
            "&remark=".$remark.
            "&charset=".$charset.
            "&signType=".$signType;

        if(2 == $signType)
        {
            $pkey = config_item('funpay.partner_key');
            $signMsg = $signMsg."&pkey=".$pkey;
            $signMsg =  md5($signMsg);
        }else{
            echo "暂不支持RSA";
            return;
        }

        $this->getView()->assign('version', $version);
        $this->getView()->assign('serialID', $serialID);
        $this->getView()->assign('submitTime', $submitTime);
        $this->getView()->assign('failureTime', $failureTime);
        $this->getView()->assign('customerIP', $customerIP);
        $this->getView()->assign('orderDetails', $orderDetails);
        $this->getView()->assign('totalAmount', $totalAmount);
        $this->getView()->assign('type', $type);
        $this->getView()->assign('buyerMarked', $buyerMarked);
        $this->getView()->assign('payType', $payType);
        $this->getView()->assign('orgCode', $orgCode);
        $this->getView()->assign('currencyCode', $currencyCode);
        $this->getView()->assign('directFlag', $directFlag);
        $this->getView()->assign('borrowingMarked', $borrowingMarked);
        $this->getView()->assign('couponFlag', $couponFlag);
        $this->getView()->assign('platformID', $platformID);
        $this->getView()->assign('returnUrl', $returnUrl);
        $this->getView()->assign('noticeUrl', $noticeUrl);
        $this->getView()->assign('partnerID', $partnerID);
        $this->getView()->assign('remark', $remark);
        $this->getView()->assign('charset', $charset);
        $this->getView()->assign('signMsg', $signMsg);
        $this->getView()->assign('signType', $signType);
        $this->getView()->assign('URL', 'https://www.funpay.com/website/pay.htm');

        $this->display('order2');

    }
}
