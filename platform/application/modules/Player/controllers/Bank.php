<?php
class BankController extends BaseController {
    /*** 查询列表***/
    public function listAction() {
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
                case 'username':
                    $args['filters']['username'] = $query;
                    break;
                default:
                    $args['filters']['nickname'] = $query;
                    break;
            }
        }

        //查询页码
        $page = $request['pagenum'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;

        //查询数据
        $model = new \Player\BankModel();
        $data = $model->lists($page, $pagesize, $args);

        //数据重构
        $list = $data['list'];
        unset($data['list']);
        $ret_list = [];
        foreach ($list as $item) {
            $new = array_change_keys($item, [
		'id' => 'id',

		'trade_type' => 'trade_type',

		'cash_before' => 'cash_before',

		'cash_after' => 'cash_after',

		'bank_before' => 'bank_before',

		'bank_after' => 'bank_after',

		'gold_trade' => 'gold_trade',

		'service_fee' => 'service_fee',

		'operate_address' => 'operate_address',

		'game_id' => 'game_id',

		'room_id' => 'room_id',

		'remark' => 'remark',

		'created_at' => 'created_at',

		'updated_at' => 'updated_at',
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

	'trade_type'=>'required',

	'cash_before'=>'required',

	'cash_after'=>'required',

	'bank_before'=>'required',

	'bank_after'=>'required',

	'gold_trade'=>'required',

	'service_fee'=>'required',

	'operate_address'=>'required',

	'game_id'=>'required',

	'room_id'=>'required',

	'remark'=>'required',

	'created_at'=>'required',

	'updated_at'=>'required',
])) {
            return false;
        }

        $object_id = $request['id'] ?? false;
        $request['total_price'] = $request['price'];
        $model = new \Player\BankModel();
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
        $model = new \Player\BankModel();
        if (!$model->delete($request['goods_id'])) {
        return $this->failed('操作失败');
        }
        return $this->succ();
    }

}