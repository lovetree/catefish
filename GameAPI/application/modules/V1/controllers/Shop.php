<?php

class ShopController extends \BaseController {

    /**
     * 获取商品列表
     */
    public function goodsAction() {
        $shop = new ShopModel();
        $data = $shop->getGoodsList();

        $data = array_change_keys($data, [
            'g_id' => 'gid',
            'name' => 'g_name',
            'img_url' =>' g_img_url',
            'desc' => 'g_desc',
            'version' => 'g_version',
            'price' => 'g_price'
        ], true);

        return $this->succ($data);
    }

    /**
     * 用户购买商品
     */
    public function buyAction() {
        $request = $this->input()->json_stream();
        //验证参数
        if (!$this->validation($request, array(
                    'cmd' => 'required|inside:wxpay',
                    'g_id' => 'required|positive_number',
                ))) {
            return false;
        }
        //当前玩家ID
        $userMode = new UserModel();
        $user_id = $userMode->getLoginUserID();
        //系统下单
        $orderModel = new OrderModel();
        $data = $orderModel->placeOrder($user_id, $request['g_id']);
        if (!is_array($data)) {
            if (OrderModel::GOODS_UNSELL === $data) {
                return $this->failed('无法购买', RCODE_ORDER_UNSELL);
            } else if (OrderModel::PLACE_FAILED === $data) {
                return $this->failed('服务器繁忙', RCODE_BUSY);
            }
            return $this->failed('服务器繁忙', RCODE_BUSY);
        }
        //系统下单成功, 创建第三方订单
        $cmd = $request['cmd'];
        if ($cmd == 'wxpay') {
            //微信下单
            $result = $orderModel->WxPlaceOrder($data);
        }
        if (!is_array($result)) {
            return $this->failed('系统繁忙', RCODE_BUSY);
        }
        $result['order_id'] = $data['order_id'];
        $result['cmd'] = $cmd;
        return $this->succ($result);
    }

    /**
     * 商城
     * source 0大厅 1捕鱼
     * type=0表示金币 1表示绿宝石
     */
    public function shopAction() {
        $request = $this->input()->json_stream();
        //验证参数
        if (!$this->validation($request, array(
                    'source' => 'required|positive_number',
                    's_type' => 'required|positive_number'
                ))) {
            return false;
        }
        $shop = new ShopModel();
        return $this->succ($shop->getShop($request['source'], $request['s_type']));
    }
    
    /**
     * 商城下单
     */
    public function shopbuyAction() {
        $request = $this->input()->json_stream();
        //验证参数
        if (!$this->validation($request, array(
                    'gid' => 'required|positive_number',
                ))) {
            return false;
        }
        $shop = new ShopModel();

        $res = $shop->shopbuy($request['gid']);
        if (is_array($res) && $res['status'] == 0){
            $redis = PRedis::instance();
            $redis->select(R_GAME_DB);
            $user_info = $redis->hMget(RK_USER_INFO . $_SESSION['user_id'], array('gold','credit','emerald','vip'));
            $res['gold'] = $user_info['gold'];
            $res['credit'] = $user_info['credit'];
            $res['emerald'] = $user_info['emerald'];
            $res['vip'] = $user_info['vip'];
            return $this->succ($res);
        }
        if(array_key_exists('error',$res))
        {
            return $this->failed($res['error']);
        }
        else
        {
            return $this->failed($res);
        }
    }


    public function pointShopAction(){

        $request = $this->input()->json_stream();
        //验证参数
        if (!$this->validation($request, array(
            'gid' => 'required|positive_number',
            'terminal' => 'required',
        ))) {
            return false;
        }
        $count = $request['count'];
        if(is_null($count))
        {
            $count=1;
        }

        $shop = new ShopModel();
        $res = $shop->pointShop($request['gid'],$request['terminal'],$count);
        if($res['status']==0)
        {
            return $this->succ($res);
        }
        else
        {

            return $this->failed($res['msg']);
        }
    }

    public function payResultAction(){

        $request = $this->input()->json_stream();
        if (!$this->validation($request, array(
            'user_id' => 'required',
            'trade_no' => 'required',
            'result_code' => 'required',
            'time_end' => 'required',
        ))) {
            $result['result_code']='1';
            $result['result_desc']='参数错误';
            echo(json_encode($result,JSON_UNESCAPED_UNICODE));
            return;
        }

        logMessage('积分接口回调参数;' . var_export(json_encode($request), true));
        $user_id=$request['user_id'];
        $order_id=$request['trade_no'];
        $status=$request['result_code'];
        $pay_time=$request['time_end'];
        if(is_null($user_id)|| is_null($order_id) || is_null($status) || is_null($pay_time))
        {
            $this->failed("参数错误", RCODE_ARG_ILLEGAL);
        }

        $shop = new ShopModel();
        $pay_time=strtotime($pay_time);
        $res=$shop->payResult($user_id, $order_id, $status, $pay_time);
        $result['result_code']=$res['status'].'';
        $result['result_desc']=$res['msg'];
        echo(json_encode($result,JSON_UNESCAPED_UNICODE));
    }

    public function exchangeListAction(){
        $request = $this->input()->json_stream();
        if (!$this->validation($request, array(
            'page_size' => 'required',
            'page_num' => 'required',
            'type' => 'required',
        ))) {
            return false;
        }
        $shop = new ShopModel();
        $data=$shop->getExchangeList($request['page_size'],$request['page_num'],$_SESSION['user_id'],$request['type']);
        if(!$data)
        {
            return $this->failed();
        }

        return $this->succ($data);
    }
}
