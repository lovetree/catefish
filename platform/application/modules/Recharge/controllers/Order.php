<?php

class OrderController extends BaseController {

    /**
     * 加载订单列表
     */
    public function listAction() {
        $request = $this->input()->request();
        $args = array();

        //搜索条件
        $query = $request['query'] ?? '';
        $query_type = $request['query_type'] ?? '';
        if(!empty($query)){
            switch($query_type){
                case 'order_id':
                    $args['filters']['order_id'] = $query;
                    break;
                case 'user_id':
                    $args['filters']['user_id'] = $query;
                    break;
                case 'goods_id':
                    $args['filters']['goods_id'] = $query;
                    break;
                case 'wx_unionid':
                    $args['filters']['wx_unionid'] = $query;
                    break;

            }
        }
        !empty($request['start_time']) && ($args['filters']['start_time'] = strtotime($request['start_time']));
        !empty($request['end_time']) && ($args['filters']['end_time'] = strtotime($request['end_time']));
        //实付金额范围
        if($request['amount'] !== ''){
            $amountArr = explode('-', $request['amount']);
            $args['filters']['amount_up'] = $amountArr[1];
            $args['filters']['amount_down'] = $amountArr[0];
        }
        //支付状态
        if($request['status'] !== ''){
            $args['filters']['status'] = $request['status'];
        }
        //日期范围
        if($request['date_range'] !== ''){
            switch ($request['date_range']){
                case 'today':
                    $args['filters']['start_time'] = strtotime('today');
                    $args['filters']['end_time'] = strtotime('today +1 day');
                    break;
                case 'yes':
                    $args['filters']['start_time'] = strtotime('today -1 day');
                    $args['filters']['end_time'] = strtotime('today');
                    break;

                case 'week':
                    $args['filters']['start_time'] = strtotime('today -6 day');
                    $args['filters']['end_time'] = strtotime('today +1 day');
                    break;
            }
        }


        //查询页码
        $page = $request['pagenum'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;

        //查询数据
        $model = new Recharge\OrderModel();
        $data = $model->lists($page, $pagesize, $args);

        $list = $data['list'];
        unset($data['list']);
        $ret_list = [];
        foreach ($list as $item) {
            isset($item['created_date']) && $item['created_date'] = date('Y-m-d H:i:s', $item['created_date']);
            isset($item['expired_date']) && $item['expired_date'] = date('Y-m-d H:i:s', $item['expired_date']);
            isset($item['update_date']) && $item['update_date'] = date('Y-m-d H:i:s', $item['update_date']);
            if(!empty($item['goods_detail'])){
                $item['goods_detail'] = json_decode($item['goods_detail'], true);
            }
            $new = array_change_keys($item, [], true);
            $ret_list[] = $new;
        }
        $data['data'] = $ret_list;
        return $this->succ($data, false);
    }
    
}
