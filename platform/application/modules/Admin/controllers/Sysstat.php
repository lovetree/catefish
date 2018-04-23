<?php
class SysstatController extends BaseController {
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
                default:
                    $args['filters']['id'] = $query;
                    break;
            }
        }

        //查询页码
        $page = $request['pagenum'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;

        //查询数据
        $model = new \Admin\SysstatModel();
        $data = $model->lists($page, $pagesize, $args);

        //数据重构
        $list = $data['list'];
        unset($data['list']);
        $ret_list = [];
        foreach ($list as $item) {
            $new = array_change_keys($item, [
		'id' => 'id',

		'gold_self' => 'gold_self',

		'gold_safe' => 'gold_safe',

		'gold_total' => 'gold_total',

		'gold_present' => 'gold_present',

		'sign' => 'sign',

		'promotion' => 'promotion',

		'register' => 'register',

		'admin_present' => 'admin_present',

		'received_phone_bind' => 'received_phone_bind',

		'prize_for_recharge' => 'prize_for_recharge',

		'game_tax' => 'game_tax',

		'sys_losewin' => 'sys_losewin',

		'tool_buy' => 'tool_buy',

		'gift_buy' => 'gift_buy',

		'market' => 'market',

		'day_sum' => 'day_sum',
], true);
            $ret_list[] = $new;
        }
        $data['data'] = $ret_list;

        //反馈数据
        return $this->succ($data, false);
    }

	/*** 创建/修改***/
	public function saveAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
			'id'=>'required',

			'gold_self'=>'required',

			'gold_safe'=>'required',

			'gold_total'=>'required',

			'gold_present'=>'required',

			'sign'=>'required',

			'promotion'=>'required',

			'register'=>'required',

			'admin_present'=>'required',

			'received_phone_bind'=>'required',

			'prize_for_recharge'=>'required',

			'game_tax'=>'required',

			'sys_losewin'=>'required',

			'tool_buy'=>'required',

			'gift_buy'=>'required',

			'market'=>'required',

			'day_sum'=>'required',
])) {
            return false;
        }

        $object_id = $request['id'] ?? false;
        $model = new \Admin\SysstatModel();
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
        'id' => 'required'
        ])) {
        return false;
        }
        $model = new \Admin\SysstatModel();
        if (!$model->delete($request['id'])) {
        return $this->failed('操作失败');
        }
        return $this->succ();
    }

}