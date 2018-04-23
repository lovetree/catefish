<?php

require_once APP_PATH . '/application/library/WxpaySDK/lib/WxPay.Api.php';

class PaymentController extends \BaseController {

    /**
     * 微信支付结果通知
     */
    public function WxPayNotifyAction() {
        $reply = new WxPayNotifyReply();
        try {
            $request = WxPayResults::Init($this->input()->input_content());
            if ($request['return_code'] != 'SUCCESS') {
                $reply->SetReturn_code('FAIL');
                $reply->SetReturn_msg($request['return_msg']);
            } else if ($request['result_code'] != 'SUCCESS') {
                $reply->SetReturn_code('FAIL');
                $reply->SetReturn_msg($request['err_code_des']);
            } else {
                //成功校验数据
                $order_id = $request['out_trade_no'];
                $db = $this->DB();
                $first = false;
                $order = false;
                //更新数据
                $result = $db->trancation(function($db, &$rollback) use($order_id, $request, &$first, &$order) {
                    //检测订单是否存在
                    $order = $db->query_fetch('select * from ms_order where order_id = ?', [$order_id]);
                    if(empty($order)){
                        return false;
                    }
                    if ($order['status'] != OrderModel::STATUS_PAY_WAIT) {
                        //已成功支付订单
                        return true;
                    }
                    /*======================================
                     * 超时由微信控制
                     * =====================================*/
                    /*
                     //检测超时
                    if($order['expired_date'] < time()){
                        return false;
                    }
                    */
                    
                    $transaction_id = $request['transaction_id'] ?? null;
                    //检测交易单号
                    if (!$transaction_id) {
                        return false;
                    }
                    //检测该笔交易是否已经记录
                    $data = $db->query_fetch('select 1 from ms_order_thirdparty where transaction_id = ?', [$transaction_id]);
                    if (!empty($data)) {
                        //直接返回成功信息
                        return true;
                    }
                    //整理数据
                    $time_end = $request['time_end'];
                    $finish_time = substr($time_end, 0, 4) . '-' . substr($time_end, 4, 2) . '-' . substr($time_end, 6, 2) . ' ' . implode(':', str_split(substr($time_end, 8), 2));
                    //统计使用的优惠券
                    $coupon_count = $request['coupon_count'] ?? 0;
                    for ($i = 0; $i != $coupon_count; $i++) {
                        if (isset($request['coupon_id_' . $i])) {
                            $coupon_detail[$request['coupon_id_' . $i]] = $request['coupon_fee_' . $i] ?? 0;
                        }
                    }

                    //插入数据
                    $status = $db->insert('ms_order_thirdparty', [
                        'transaction_id' => $transaction_id,
                        'order_id' => $order_id,
                        'channel' => 'wxpay',
                        'status' => 1,
                        'trade_type' => $request['trade_type'],
                        'bank_type' => $request['bank_type'],
                        'fee_type' => $request['fee_type'] ?? 'CNY',
                        'total_fee' => $request['total_fee'],
                        'cash_fee' => $request['cash_fee'] ?? ($request['total_fee'] - ($request['coupon_fee'] ?? 0)),
                        'cash_fee_type' => $request['cash_fee_type'] ?? 'CNY',
                        'coupon_fee' => $request['coupon_fee'] ?? 0,
                        'coupon_detail' => isset($coupon_detail) ? json_encode($coupon_detail) : '',
                        'finish_date' => $finish_time
                    ]);

                    if (!$status) {
                        //保存失败
                        $rollback = true;
                        return false;
                    }
                    //更新本地订单
                    $status = $db->update('ms_order', ['status' => OrderModel::STATUS_PAY_SUCC, 'update_date' => time()], 'order_id = ' . $db->quote($order_id));
                    if(!$status){
                        //保存失败
                        $rollback = true;
                        return false;
                    }
                    $first = true;
                    return true;
                });
                //响应成功信息
                if ($result) {
                    $reply->SetReturn_code('SUCCESS');
                    $reply->SetReturn_msg('');
                } else {
                    $reply->SetReturn_code('FAIL');
                    $reply->SetReturn_msg('系统错误');
                }
                //执行动作
                if(true === $first){
                    WebApp::inform(WebApp::AFTER_PAYMENT, [$order]);
                }
            }
        } catch (Exception $e) {
            $reply->SetReturn_code('FAIL');
            $reply->SetReturn_msg($e->getMessage());
        }
        flushXml:
        //返回结果
        echo $reply->ToXml();
    }

    /**
     * 订单结果查询
     */
    public function queryAction() {
        $request = $this->input()->json_stream();
        //验证参数
        if (!$this->validation($request, array(
                    'order_id' => 'required',
                ))) {
            return false;
        }
        $order_id = $request['order_id'];
        $orderModel = new OrderModel();
        $order = $orderModel->getOrder($order_id, ['status']);
        if (!$order) {
            return $this->failed('查询失败');
        }
        $ret['state'] = $order['status'] == OrderModel::STATUS_PAY_WAIT ? 0 : 1;
        return $this->succ($ret);
    }

    /**
     * 转账充值
     *
     * @return bool
     */
    public function remitAction(){
        $request = $this->input()->json_stream();
        //验证参数
        if (!$this->validation($request, array(
            'amount' => 'required',
            'remitter' => 'required',
            'depo_bank' => 'required'
        ))) {
            return false;
        }

        try{
            PaymentModel::remit($_SESSION['user_id'], $request['amount'], $request['payee'],
                $request['cardno'], $request['depo_bank'], $request['remitter'],
                $request['remit_time'], $request['remit_addr']);
        }catch (\Exception $e){
            $this->failed("服务器忙", RCODE_BUSY);
        }

        return $this->succ();
    }

    /**
     * 充值、汇款记录
     * @return bool
     */
    public function rechargeLogAction(){
        try{
            $result = PaymentModel::getRechargeLog($_SESSION['user_id']);
            //重构数据
            $dataArr = [];
            foreach ($result as $item) {
                $dataArr[] = [
                    'r_flowno' => $item['flowno'],
                    'r_date' => date('Y-m-d', $item['r_date']),
                    'r_amount' => $item['amount'],
                    'r_type' => $item['r_type'],
                    'r_status' => $item['r_status'],
                ];
            }
        }catch (\Exception $e){
            $this->failed("服务器忙", RCODE_BUSY);
        }

        return $this->succ(['list'=>$dataArr]);
    }
    
    /**
     * 提现
     *
     * @return bool
     */
    public function cashAction(){
        $request = $this->input()->json_stream();
        //验证参数
        if (!$this->validation($request, array(
            'amount' => 'required',
            'depo_bank' => 'required',
            'account_name' => 'required',
            'branch_bank' => 'required',
            'cardno' => 'required',
            'pass' => 'required',
        ))) {
            return false;
        }

        try{
            PaymentModel::cash($_SESSION['user_id'], $request);
        }catch (\Exception $e){
            return $this->failed($e->getMessage(), RCODE_BUSY);
        }

        return $this->succ();
    }

    /**
     *苹果In-App支付结果通知
     */
    public function InAppPayNoticeAction(){
        $request = $this->input()->json_stream();
        echo $request;
        //验证参数
        /*if (!$this->validation($request, array(
            'product_id' => 'required',
        ))) {
            return false;
        }

        $appleOrder = new AppleOrder();*/

        return $this->succ();
    }

    /**
     * 快汇宝接口
     */
    public function khbAction()
    {
        $input = $this->input()->post();

        $status = $this->DB()->insert('ms_khb_trade', [
            'numid' => $input["NumID"],
            'type' => $input["Type"],
            'remark' => $input["Remark"],
            'money' => $input["Money"] * 100,
            'fee' => $input["Fee"] * 100,
            'state' => $input["State"],
            'o_time' => strtotime($input["Time"]),
            'payuser' => $input["PayUser"],
            'source' => $input["Source"],
            'created_at' => time()

        ]);

        if ($status) {
            echo 'Success';
        } else{
            echo 'fail';
        }
    }

}
