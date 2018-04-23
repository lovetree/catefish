<?php

require_once APP_PATH . '/application/library/WxpaySDK/lib/WxPay.Api.php';

class OrderModel extends BaseModel {

    //商品不存在或已经下架
    const GOODS_UNSELL = -1;
    //订单创建失败
    const PLACE_FAILED = 0;
    const STATUS_PAY_WAIT = 0;
    const STATUS_PAY_SUCC = 1;
    const STATUS_PAY_REFUND = 2;

    /**
     * 下订单
     * @param int $user_id
     * @param int $goods_id
     * @return int
     */
    public function placeOrder(int $user_id, int $goods_id) {
        $db = $this->DB();
        //查询商品是否在售
        $shopModel = new ShopModel();
        $goods = $shopModel->getActiveGoods($goods_id);
        if (false === $goods) {
            //商品未上架或已经下架
            return self::GOODS_UNSELL;
        }
        $goods_detail = [
            'items' => $goods['items'] ?? '',
            'version' => $goods['version'] ?? '',
            'name' => $goods['name'] ?? ''
        ];
        //生成订单ID
        $order_id = $this->createOrderID();
        $data = array(
            'order_id' => $order_id,
            'user_id' => $user_id,
            'goods_id' => $goods_id,
            'goods_detail' => json_encode($goods_detail),
            'totalf_fee' => $goods['total_fee'],
            'actually_fee' => $goods['actually_fee'],
            'coupon_fee' => $goods['coupon_fee'] ?? 0,
            'coupon_detail' => $goods['coupon_detail'] ?? '',
            'ip' => $this->input()->ip_address(),
            'trade_type' => 'APP',
            'created_date' => time(),
            'expired_date' => strtotime('+2 hour'),
            'update_date' => time()
        );
        $status = $db->insert('ms_order', $data);
        if (!$status) {
            //订单创建失败
            return self::PLACE_FAILED;
        }
        $data['goods'] = $goods;
        return $data;
    }

    /**
     * 创建订单ID
     */
    protected function createOrderID() {
        $order_id = 'APP' . date('YmdHis') . bin2hex(random_bytes(4));
        return $order_id;
    }

    /**
     * 微信下单
     * @param array $data
     * @return mixed
     */
    public function WxPlaceOrder(array $data) {
        $goods = $data['goods'];
        $inputObj = new WxPayUnifiedOrder();
        $inputObj->SetOut_trade_no($data['order_id']);
        $inputObj->SetBody($goods['name']); //商品描述
        $inputObj->SetTotal_fee($goods['actually_fee']); //价格
        //$inputObj->SetNotify_url($this->getWxNotifyUrl());
        $inputObj->SetTime_start(date('YmdHis', $data['created_date'] ?? time()));
        if (isset($data['expired_date'])) {
            $inputObj->SetTime_expire(date('YmdHis', $data['expired_date']));
        }
        $inputObj->SetTrade_type('APP');
        $inputObj->SetSpbill_create_ip($this->input()->ip_address());
        $wxapi = new WxPayApi();
        $result = $wxapi->unifiedOrder($inputObj);
        if ($result['return_code'] != 'SUCCESS') {
            logMessage("wxpay place order fail ! error_msg: {$result['return_msg']}\r\nparams: " . json_encode($data), LOG_NOTICE);
            return false;
        }
        if ($result['result_code'] != 'SUCCESS') {
            logMessage("wxpay place order fail ! error_msg: ({$result['err_code']}){$result['err_code_des']}\r\nparams: " . json_encode($data), LOG_NOTICE);
            return false;
        }
        $wxdata = new WxPayDataBase();
        $values = [
            'appid' => WxPayConfig::APPID,
            'partnerid' => WxPayConfig::MCHID,
            'prepayid' => $result['prepay_id'],
            'package' => 'Sign=WXPay',
            'noncestr' => $wxapi->getNonceStr(),
            'timestamp' => time()
        ];
        $wxdata->setValues($values);
        $values['sign'] = $wxdata->MakeSign();
        return $values;
    }

    /**
     * 第三方支付通知接口
     * @return type
     */
    protected function getWxNotifyUrl() {
        return toUrl('v1/payment/WxPayNotify');
    }

    /**
     * 获取一笔订单
     * @param string $order_id
     */
    public function getOrder(string $order_id, array $fields = ['*']) {
        $data = null;
        try {
            $fields = empty($fields) ? '*' : implode(',', $fields);
            $sql = <<<SQL
                select $fields from ms_order where order_id = ?
SQL;
            $data = $this->DB()->query_fetch($sql, [$order_id]);
        } catch (Exception $e) {
            logMessage($e->getTraceAsString(), LOG_DEBUG);
        }
        return $data;
    }

    /**
     * 完成订单, 执行订单事件
     */
    public function finishOrder($order) {
        $goods_id = $order['goods_id'] ?? false;
        $user_id = $order['user_id'] ?? false;
        if (/*!$goods_id || */!$user_id) {
            logMessage('finish order error(params missing), orderID = ' . $order['order_id'], LOG_ERR);
            return false;
        }
        try {
            /*
            $item_list = $this->DB()->query('select mig.item_id, mig.item_count from ms_goods as g join ms_map_item_goods as mig on mig.goods_id = g.id where g.id = ?', [$goods_id]);
             */
            $goods_detail = isset($order['goods_detail']) ? json_decode($order['goods_detail'], true) : [];
            $items = $goods_detail['items'] ?? [];
            if (!empty($items)) {
                foreach ($items as $one) {
                    $item = new ItemModel($one['item_id']);
                    $item->giveToUser($user_id, $one['item_count'], ['from' => 'order', 'order_id' => $order['order_id'], 'goods_id' => $goods_id]);
                }
            }
            return true;
        } catch (Exception $e) {
            logMessage('finish order error, orderID = ' . $order['order_id'], LOG_ERR);
            logMessage($e->getTraceAsString());
        }
        return false;
    }

}
