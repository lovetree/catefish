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
        if (is_array($res)){
            $redis = PRedis::instance();
            $redis->select(R_GAME_DB);
            $user_info = $redis->hMget(RK_USER_INFO . $_SESSION['user_id'], array('gold','credit','emerald','vip'));
            $res['gold'] = $user_info['gold'];
            $res['credit'] = $user_info['credit'];
            $res['emerald'] = $user_info['emerald'];
            $res['vip'] = $user_info['vip'];
            return $this->succ($res);
        }
        return $this->failed($res);
    }
    
}
