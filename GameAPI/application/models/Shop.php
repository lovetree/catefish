<?php

class ShopModel extends BaseModel
{

    /**
     * 获取商品列表
     */
    public function getGoodsList()
    {
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $cache_data = $redis->get(RK_GOODS_LIST);
        if (false === $cache_data) {
            $sql = <<<SQL
                select 
                    id as gid, 
                    show_name as `name`, 
                    total_price as price,
                    `desc`,
                    version,
                    img_url
                from ms_goods 
                where (start_time is NULL or start_time <= ?)
                    and (end_time is NULL or end_time >= ?)
                    and status = 1
SQL;
            $list = $this->DB()->query($sql, [time(), time()]);
            if (empty($list)) {
                $list = [];
            } else {
                $redis->set(RK_GOODS_LIST, json_encode($list));
                $redis->expire(RK_GOODS_LIST, R_GOODS_TIMEOUT);
            }
        } else {
            $list = json_decode($cache_data, true);
            if (json_error()) {
                logMessage('json 解析错误: ' . $cache_data);
                return array();
            }
        }
        return $list;
    }

    /**
     * 获取一个在售的商品
     * @param type $goods_id
     */
    public function getActiveGoods($goods_id)
    {
        $db = $this->DB();
        $sql = <<<SQL
            select 
                g.id, g.show_name, g.type, g.worth, g.present, g.source, g.total_price, g.version,
                group_concat(item.`id`, "##", item.`name`, "##", mig.item_count) as items,
                g.aibei_waresid
            from ms_goods as g
            left join ms_map_item_goods as mig on mig.goods_id = g.id
            left join ms_item as item on item.id = mig.item_id
            where g.id = ? 
                and (g.start_time is NULL or g.start_time <= ? )
                and (g.end_time is NULL or g.end_time >= ? )
                and g.status = 1
            group by g.id
SQL;
        $data = $db->query_fetch($sql, [$goods_id, time(), time()]);
        if (empty($data)) {
            return false;
        }
        if (isset($data['items'])) {
            $item_list = explode(',', $data['items']);
            array_walk($item_list, function (&$v) {
                $tmp = explode('##', $v);
                $v = ['item_id' => $tmp[0], 'item_name' => $tmp[1], 'item_count' => $tmp[2]];
            });
            $data['items'] = $item_list;
        }
        return [
            'goods_id' => $data['id'],
            'name' => $data['show_name'],
            'type' => $data['type'],
            'source' => $data['source'],
            'total_fee' => $data['total_price'],
            'worth' => $data['worth'],
            'present' => $data['present'],
            'actually_fee' => $data['total_price'],
            'version' => $data['version'],
            'items' => $data['items'],
            'aibei_waresid' => $data['aibei_waresid']
        ];
    }

    public function _getGoods(int $source, int $type)
    {
        $sql = <<<SQL
            select 
                    id as gid, 
                    show_name as `name`, 
                    total_price as price,
                    worth,present,
                    `desc`,
                    version,
                    img_url
                from ms_goods 
                where (start_time is NULL or start_time <= ?)
                    and (end_time is NULL or end_time >= ?)
                    and status = 1 and source = ? and type = ?
SQL;
        return $this->DB()->query($sql, [time(), time(), $source, $type]);
    }

    public function _getOneGood(int $source, int $gid)
    {
        $sql = <<<SQL
            select 
                    id as gid, 
                    show_name as `name`, 
                    total_price as price,
                    worth,present,
                    `desc`,
                    version,
                    img_url
                from ms_goods 
                where (start_time is NULL or start_time <= ?)
                    and (end_time is NULL or end_time >= ?)
                    and status = 1 and source = ? and id = ? limit 1
SQL;
        return $this->DB()->query_fetch($sql, [time(), time(), $source, $gid]);
    }

    public function _getShop(int $source, int $type)
    {
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $key = RK_GOODS_LIST . $source . '_' . $type;
        $cache_data = $redis->get($key);
        if (false === $cache_data) {
            $list = $this->_getGoods($source, $type);
            if (empty($list)) {
                $list = [];
            } else {
                $redis->set($key, json_encode($list));
                $redis->expire($key, R_GOODS_TIMEOUT);
            }
        } else {
            $list = json_decode($cache_data, true);
        }
        return $list;
    }

    /**
     * 捕鱼商城
     * 延后优化成redis获取，暂由mysql来读取
     */
    public function getShop(int $source, int $type)
    {
        $return = [];
        //获取商品信息
        $list = $this->_getShop($source, $type);
        $return['goodsList'] = $list ?? [];
        //获取砖石
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $user_info = $redis->hMget(RK_USER_INFO . $_SESSION['user_id'], array('credit', 'emerald', 'gold','point'));
        $return['credit'] = $user_info['credit'] ?? 0;
        $return['emerald'] = $user_info['emerald'] ?? 0;
        $return['gold'] = $user_info['gold'] ?? 0;
        $return['point'] = $user_info['point'] ?? 0;
        //获取vip信息
        $vip = new VipModel();
        $return['vipInfo'] = $vip->vipInfo();
        $return['source'] = $source;
        $return['type'] = $type;
        return $return;
    }

    /**
     *  捕鱼下单
     */
    public function shopbuy(int $gid)
    {
        $goods = $this->getActiveGoods($gid);
        if (!$goods) return _1000801;

        //开始下单
        $goods_detail = [
            'items' => $goods['items'] ?? '',
            'version' => $goods['version'] ?? '',
            'name' => $goods['show_name'] ?? ''
        ];
        //生成订单ID
        $order_id = $this->createOrderID();
        $data = array(
            'order_id' => $order_id,
            'user_id' => $_SESSION['user_id'],
            'goods_id' => $gid,
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
            'status' => 1
        );

        //如果是钻石购买，判断钻石是否足够
        switch ($goods['source']) {
            case 0:
                if ($goods['type'] == 0) {
                    break;
                }
            case 1:
            case 10:
            default :
                //获取砖石
                $redis = PRedis::instance();
                $redis->select(R_GAME_DB);
                $user_info = $redis->hMget(RK_USER_INFO . $_SESSION['user_id'], array('credit', 'nickname', 'emerald'));
                $emerald = $user_info['emerald'];
                $credit = $user_info['credit'] ?? 0;
                if ($credit < $goods['total_fee'] / 100) {
                    return ['status' => 1, 'error' => _1000802];
                }
        }
        $db = $this->DB();
        $db->beginTransaction();
        $status = $db->insert('ms_order', $data);
        if (!$status) {
            //订单创建失败
            $db->rollBack();
            logMessage('ms_order 插入失败;' . var_export($data, true));
            return _1000803;
        }

        $r_p = [];
        $r_v = [];
        //如果是钻石购买，判断钻石是否足够
        switch ($goods['source']) {
            case 10:
                break;
            case 0:
                if ($goods['type'] == 0) {
                    break;
                }
            case 1:
            default :
                //减少钻石
                $r_p[] = 'credit';
                $r_v[] = -$goods['total_fee'] / 100;
//                if (!$this->onChangeUserCredit($_SESSION['user_id'], -$goods['total_fee']/100)){
//                    $db->rollBack();
//                    return _1000803;
//                }
        }

        $user = new UserModel();
        $user_info = $user->getOneUserInfo($_SESSION['user_id'], ['id', 'user_pay', 'nickname']);
        switch ($goods['source']) {
            case 0:
                //大厅商城，0是购买钻石，1是钻石购买金币
                switch ($goods['type']) {
                    case 0:
                        //修改充值数据
                        try {
                            $db->update('ms_user_info', ['user_pay' => ($goods['total_fee'] / 100 + $user_info['user_pay'])],
                                'id = ' . $user_info['id']);
                        } catch (Exception $ex) {
                            $db->rollBack();
                            logMessage($ex->getTraceAsString());
                            return _1000603;
                        }
                        //充值购买钻石
                        $r_p[] = 'credit';
//                        if (!$this->onChangeUserCredit($_SESSION['user_id'], $goods['worth'])){
//                            $db->rollBack();
//                            return _1000803;
//                        }
                        $credit_type = 1;
                        $content = "恭贺" . $user_info['nickname'] . "充值" . $goods['worth'] . "钻石";
                        break;
                    case 1:
                        //钻石购买金币
                        //赠送金币
                        $r_p[] = 'gold';
//                        if (!$this->onChangeUserGold($_SESSION['user_id'], $goods['worth'])){
//                            $db->rollBack();
//                            return _1000803;
//                        }
                        $content = "恭贺" . $user_info['nickname'] . "购买了" . $goods['worth'] . "金币";
                        $gold_type = 2;
                        $credit_type = 2;
                        break;
                }
                break;
            case 1:
                //赠送相关金币还是绿宝石
                switch ($goods['type']) {
                    case 0:
                        //赠送金币
                        $r_p[] = 'gold';
//                        if (!$this->onChangeUserGold($_SESSION['user_id'], $goods['worth'])){
//                            $db->rollBack();
//                            return _1000803;
//                        }
                        $content = "恭贺" . $user_info['nickname'] . "购买了" . $goods['worth'] . "金币";
                        $gold_type = 2;
                        $credit_type = 2;
                        break;
                    case 1:
                        //赠送绿宝石
                        $r_p[] = 'emerald';
//                        if (!$this->onChangeUserEmerald($_SESSION['user_id'], $goods['worth'])){
//                            $db->rollBack();
//                            return _1000803;
//                        }
                        $content = "恭贺" . $user_info['nickname'] . "购买了" . $goods['worth'] . "绿宝石";
                        $credit_type = 3;
                        $emerald_type = 1;
                        break;
                }
                break;
            case 10:
                $r_p[] = 'gold';
                $content = "恭贺" . $user_info['nickname'] . "购买了" . $goods['worth'] . "房卡";
                break;
        }
        $r_v[] = $goods['worth'] + $goods['present'];
        if (!$this->onChangeUserData($_SESSION['user_id'], $r_p, $r_v)) {
            $db->rollBack();
            logMessage('修改用户redis信息失败：' . $_SESSION['user_id']);
            return _1000803;
        }
        if (isset($emerald_type)) {
            $emerald_data = [
                'user_id' => $_SESSION['user_id'],
                'emerald_change' => $goods['worth'],
                'emerald_after' => $emerald + $goods['worth'],
                'type' => $emerald_type,
                'create_time' => time()
            ];
            $db->insert("ms_emerald_log", $emerald_data);
        }
        if (isset($credit_type)) {
            switch ($credit_type) {
                case '1':
                    $credit_change = $goods['worth'];
                    break;
                default:
                    $credit_change = -$goods['total_fee'] / 100;
                    break;
            }
            $credit_data = [
                'user_id' => $_SESSION['user_id'],
                'credit_change' => $credit_change,
                'credit_after' => $credit + $credit_change,
                'type' => $credit_type,
                'create_time' => time()
            ];
            $db->insert("ms_credit_log", $credit_data);
        }
        if (isset($gold_type)) {
            $newData = [
                'user_id' => $_SESSION['user_id'],
                'gold_change' => $goods['worth'],
                'gold_after' => (new data\UserModel($_SESSION['user_id']))->get('gold'),
                'type' => $gold_type,
                'create_time' => time()
            ];
            $db->insert('ms_gold_log', $newData);
        }
        $db->commit();

        //同步商城购买任务
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $user_info = $redis->hMget(RK_USER_INFO . $_SESSION['user_id'], array('shopTask'));
        $shopTask = [];
        if ($user_info['shopTask']) {
            $shopTask = json_decode($user_info['shopTask'], TRUE);
            if (date('Y-m-d', time()) == $shopTask['date']) {
                $shopTask['num'] += 1;
            } else {
                $shopTask['date'] = date('Y-m-d', time());
                $shopTask['num'] = 1;
            }
        } else {
            $shopTask['date'] = date('Y-m-d', time());
            $shopTask['num'] = 1;
        }
        $redis->hMset(RK_USER_INFO . $_SESSION['user_id'], array('shopTask' => json_encode($shopTask)));

        $detail = [];
        $detail['gid'] = $gid;
        $detail['count'] = 1;
        $detail['why'] = "商城购买";
        $this->_addUserLog($_SESSION['user_id'], LOG_ACT_SHOP, json_encode($detail));


        //开始广播
        $common = new CommonModel();
        $broadcast = [];
        $broadcast['content'] = $content; //广播内容
        $broadcast['source'] = 0; //来源0大厅广播
        $broadcast['times'] = 5; //发送刺死 0表示无限制
        $broadcast['intervals'] = 0; //时间间隔 0表示无间隔
        $broadcast['start_time'] = time(); //开始时间
        $broadcast['end_time'] = time() + 3600; //结束时间
        //$common->sendBroadcast($broadcast);

        WebApp::inform(WebApp::UPGRADE_VIP, [$_SESSION['user_id']]);
        return ['status' => 0, 'type' => $goods['type']];
    }


    public function pointShop(int $gid, $terminal, $count)
    {
        $returnvalue = [];
        $db = $this->DB();
        //查询商品是否在售
        $shopModel = new ShopModel();
        $goods = $shopModel->getActiveGoods($gid);
        if (false === $goods) {
            //商品未上架或已经下架
            return ['status' => 1, 'msg' => _1000801];
        }

        $userModel = new UserModel();
        $info = $userModel->getOneUser($_SESSION['user_id']);

        //获取金币
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $user_info = $redis->hMget(RK_USER_INFO . $_SESSION['user_id'], array('gold', 'credit'));

        $userModel = new UserModel();
        $point=$userModel->refreshPoint($info['id'], $terminal);

        //当为金币兑换积分时，先看金币是足够
        if ($goods['type'] == 2) {
            $gold = $user_info['gold'] ?? 0;
            if ($gold < $goods['total_fee'] * $count / 100) {
                logMessage('金币不足;玩家id:' .$_SESSION['user_id'].',玩家金币:'.$gold.',购买数量：'.$count.',商品id:'.$gid .',商品单价：'.$goods['total_fee']);
                return ['status' => 1, 'msg' => _1000805];
            }
        }
        else
        {
            if($point<$goods['total_fee']  / 100)
            {
                return ['status' => 1, 'msg' => _1000806];
            }
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
            'user_id' => $_SESSION['user_id'],
            'goods_id' => $gid,
            'goods_detail' => json_encode($goods_detail),
            'totalf_fee' => $goods['total_fee'] * $count,
            'actually_fee' => $goods['actually_fee'],
            'coupon_fee' => $goods['coupon_fee'] ?? 0,
            'coupon_detail' => $goods['coupon_detail'] ?? '',
            'ip' => $this->input()->ip_address(),
            'trade_type' => 'APP',
            'created_date' => time(),
            'expired_date' => strtotime('+2 hour'),
            'update_date' => time()
        );
        $db = $this->DB();
        $db->beginTransaction();
        $status = $db->insert('ms_order', $data);
        if (!$status) {
            //订单创建失败
            $db->rollBack();
            logMessage('ms_order 插入失败;' . var_export($data, true));
            $returnvalue['msg'] = '订单插入失败';
            $returnvalue['status'] = 1;
            return $returnvalue;
        }

        //若存在积分的交易，则先请求A平台完成积分的加减

        $paygood = 0;
        $payamount = 0;
        $buygood = 0;
        $buyamount = 0;
        $exchangetype = 0;

        //
        //刷新积分信息

        if ($goods['type'] == '3') {
            //积分兑换钻石
            $point_change = -$goods['total_fee'] / 100;
            $payamount = $goods['total_fee'] / 100;
            $buyamount = $goods['worth'] + $goods['present'];
            $paygood=$point- $payamount;
            $buygood=$user_info['credit'] + $buyamount;
        } else {
            //金币兑换积分
            $point_change = $goods['worth'] * $count;
            $payamount = $goods['total_fee'] * $count / 100;
            $buyamount = $goods['worth'] * $count;
            $paygood=$user_info['gold'] -$payamount;
            $buygood=$point + $buyamount;
        }

        $point_request['user_id'] = $_SESSION['user_id'];
        $point_request['amount'] = $point_change;


        $newData = [
            'orderID' => $order_id,
            'userid' => $_SESSION['user_id'],
            'paygoods' => $paygood,
            'payamount' => $payamount,
            'buygoods' => $buygood,
            'buyamount' => $buyamount,
            'exchtype' => $goods['type'],
            'ip' => $this->input()->ip_address(),
            'exchangetime' => time()
        ];

        $status = $db->insert('ms_point_exchange_log', $newData);
        if (!$status) {
            //订单创建失败
            $db->rollBack();
            logMessage('ms_point_exchange_log 插入失败;' . var_export($newData, true));
            $returnvalue['msg'] = '订单插入失败';
            $returnvalue['status'] = 1;
            return $returnvalue;
        }

        $db->commit();

        //请求A平台完成交易
        if (!is_null($info['wx_unionid'])) {
            if ($goods['type'] == '3') {
                //积分兑换钻石
                $inputdata['user_id'] = $info['wx_unionid'];
                $inputdata['diamonds'] = $buyamount . '';
                $inputdata['integral'] = $payamount . '';
                $inputdata['trade_no'] = $order_id;
                $inputdata['notify_url'] = config_item('aPlatform.notifyUrl');
                $inputdata['return_url']= config_item('aPlatform.returnUrl');
                //$postdata = getPostData('exchangeDiamonds', $terminal, $inputdata);
                $point_result = postData('exchangeDiamonds', $terminal, $inputdata);
            } else {
                //金币兑换积分

                $inputdata['user_id'] = $info['wx_unionid'];
                $inputdata['gold'] = $payamount . '';
                $inputdata['integral'] = $buyamount . '';
                $inputdata['trade_no'] = $order_id;
                $inputdata['notify_url'] = config_item('aPlatform.notifyUrl');
                $inputdata['return_url']= config_item('aPlatform.returnUrl');
                //$postdata = getPostData('exchangeIntegral', $terminal, $inputdata);
                $point_result = postData('exchangeIntegral', $terminal, $inputdata);
            }
        }

        if ($point_result['code'] != 0) {
            //如果A平台积分加减失败，报错
            logMessage('请求A平台加减积分失败;' . var_export($point_request, true));
            $returnvalue['msg'] = $point_result['msg'];
            $returnvalue['status'] = 1;
            return $returnvalue;
        }

        logMessage('跳转支付地址：' . $point_result['data']['pay_url']);
        $returnvalue['url'] = $point_result['data']['pay_url'];
        $returnvalue['status'] = 0;
        return $returnvalue;
    }

    public function payResult($user_id, $order_id, $payres, $pay_time, $terminal)
    {
        $db = $this->DB();
        $res = [];
        $orderModel = new OrderModel();
        $order = $orderModel->getOrder($order_id);

        $log = $this->getExchangelog($order_id);
        $info = $db->query_fetch('select * from ms_user where wx_unionid = ?', array($user_id));
        if (!$info) {
            $res['status'] = 1;
            $res['msg'] = '用户不存在';
            return $res;
        }

        $user = new Data\UserModel($info['id']);
        if ($order) {
            if ($info['id'] == $order['user_id']) {
                if ($order['status'] != 0 || $log['status'] != 0) {
                    $res['status'] = 1;
                    $res['msg'] = '已经是已完成订单';
                } else {

                    if ($payres == 0) {
                        $db->beginTransaction();
                        if ($log['exchtype'] == 3) {
                            //积分兑换钻石
                            var_dump($log['buyamount']);
                            $user->addCredit($log['buyamount']);
                        } else {
                            //金币兑换积分
                            $user->addGold(0 - $log['payamount']);
                        }

                        $userModel = new UserModel();
                        $userModel->refreshPoint($info['id'], $terminal);

                        $status = $db->update('ms_point_exchange_log', ['status' => OrderModel::STATUS_PAY_SUCC, 'updatetime' => $pay_time], 'id = ' . $log['id']);
                        if (!$status) {
                            //保存失败
                            $db->rollBack();
                            $res['status'] = 1;
                            $res['msg'] = '订单更新失败';
                            return $res;
                        }
                        $status = $db->update('ms_order', ['status' => OrderModel::STATUS_PAY_SUCC, 'update_date' => $pay_time], 'id = ' . $order['id']);
                        if (!$status) {
                            //保存失败
                            $db->rollBack();
                            $res['status'] = 1;
                            $res['msg'] = '订单更新失败';
                            return $res;
                        }
                        $db->commit();
                        $res['status'] = 0;
                        $res['msg'] = '订单更新成功';
                    } else {
                        $res['status'] = 1;
                        $res['msg'] = '支付失败';
                    }
                }
            } else {
                $res['status'] = 1;
                $res['msg'] = '用户ID不匹配';
            }
        } else {
            $res['status'] = 1;
            $res['msg'] = '订单不存在';
        }
        return $res;
    }

    public function getExchangeList($page_size,$page_num,$user_id,$type, array $fields = ['*'])
    {
        $data = null;
        try {
        $start_position=($page_num-1)*$page_size;
        $fields = empty($fields) ? '*' : implode(',', $fields);
        $sql = <<<SQL
                select $fields from ms_point_exchange_log where userid = $user_id and exchtype=$type order by exchangetime desc limit $start_position,$page_size
SQL;
            $data = $this->DB()->query($sql);
        } catch (Exception $e) {
            logMessage($e->getTraceAsString(), LOG_DEBUG);
            return false;
        }
        return $data;
    }
    /**
     * 创建订单ID
     */
    protected function createOrderID()
    {
        $order_id = 'APP' . date('YmdHis') . bin2hex(random_bytes(4));
        return $order_id;
    }

    public function getExchangelog($order_id, array $fields = ['*'])
    {
        $data = null;
        try {
            $fields = empty($fields) ? '*' : implode(',', $fields);
            $sql = <<<SQL
                select $fields from ms_point_exchange_log where orderID = ?
SQL;
            $data = $this->DB()->query_fetch($sql, [$order_id]);
        } catch (Exception $e) {
            logMessage($e->getTraceAsString(), LOG_DEBUG);
        }
        return $data;
    }
}
