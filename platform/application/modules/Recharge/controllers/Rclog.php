<?php
class RclogController extends BaseController {
/*** 查询列表***/public function listAction() {
        $request = $this->input()->request();
        $args = array();

        //搜索条件
        $query = $request['query'] ?? false;
        $query_type = $request['query_type'] ?? false;
        if(!empty($query) && !empty($query_type)){
            switch ($query_type){
                case 'id':
                    $args['filters']['id'] = $query;
                    break;
                default:
                    $args['filters']['id'] = $query;
                    break;
            }
        }

        //查询页码
        $page = $request['pagenum'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;

        //查询数据
        $model = new \Recharge\RclogModel();
        $data = $model->lists($page, $pagesize, $args);

        //数据重构
        $list = $data['list'];
        unset($data['list']);
        $ret_list = [];
        foreach ($list as $item) {
            $new = array_change_keys($item, [
		'id' => 'id',

		'recharge_time' => 'recharge_time',

		'service_type' => 'service_type',

		'username' => 'username',

		'game_id' => 'game_id',

		'order_flow' => 'order_flow',

		'order_amount' => 'order_amount',

		'payed_amount' => 'payed_amount',

		'recharge_type' => 'recharge_type',

		'gold_recharge' => 'gold_recharge',

		'gold_present' => 'gold_present',

		'gold_before' => 'gold_before',

		'gold_after' => 'gold_after',

		'ip' => 'ip',

		'operator' => 'operator',

		'created_at' => 'created_at',
], true);
            $ret_list[] = $new;
        }
        $data['data'] = $ret_list;

        //反馈数据
        return $this->succ($data, false);
    }

/*** 创建/修改***/public function saveAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
	'id'=>'required',

	'recharge_time'=>'required',

	'service_type'=>'required',

	'username'=>'required',

	'game_id'=>'required',

	'order_flow'=>'required',

	'order_amount'=>'required',

	'payed_amount'=>'required',

	'recharge_type'=>'required',

	'gold_recharge'=>'required',

	'gold_present'=>'required',

	'gold_before'=>'required',

	'gold_after'=>'required',

	'ip'=>'required',

	'operator'=>'required',

	'created_at'=>'required',
])) {
            return false;
        }

        $object_id = $request['id'] ?? false;
        $request['total_price'] = $request['price'];
        $model = new \Recharge\RclogModel();
        if (empty($object_id)) {
            //新建
            $status = $model->create($request);
        } else {
            //修改
            $status = $model->edit($object_id, $request);
        }
        if (false === $status) {
            return $this->failed('保存失败');
        }
        return $this->succ();
    }

/*** 删除商品的状态***/
    public function deleteAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
        'goods_id' => 'required'
        ])) {
        return false;
        }
        $model = new \Recharge\RclogModel();
        if (!$model->delete($request['goods_id'])) {
        return $this->failed('操作失败');
        }
        return $this->succ();
    }

}