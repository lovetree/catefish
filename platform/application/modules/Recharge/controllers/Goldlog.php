<?php
class GoldlogController extends BaseController {
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
        $model = new \Recharge\GoldlogModel();
        $data = $model->lists($page, $pagesize, $args);

        //数据重构
        $list = $data['list'];
        unset($data['list']);
        $ret_list = [];
        foreach ($list as $item) {
            $new = array_change_keys($item, [
		'id' => 'id',

		'trade_flow' => 'trade_flow',

		'trade_at' => 'trade_at',

		'channel_id' => 'channel_id',

		'presentor_id' => 'presentor_id',

		'gold_present' => 'gold_present',

		'receiver_type' => 'receiver_type',

		'reveiver_id' => 'reveiver_id',

		'trade_type' => 'trade_type',

		'status' => 'status',

		'create_at' => 'create_at',
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

	'trade_flow'=>'required',

	'trade_at'=>'required',

	'channel_id'=>'required',

	'presentor_id'=>'required',

	'gold_present'=>'required',

	'receiver_type'=>'required',

	'reveiver_id'=>'required',

	'trade_type'=>'required',

	'status'=>'required',

	'create_at'=>'required',
])) {
            return false;
        }

        $object_id = $request['id'] ?? false;
        $request['total_price'] = $request['price'];
        $model = new \Recharge\GoldlogModel();
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
        $model = new \Recharge\GoldlogModel();
        if (!$model->delete($request['goods_id'])) {
        return $this->failed('操作失败');
        }
        return $this->succ();
    }

}