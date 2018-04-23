<?php
class GamemodeController extends BaseController {
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
        $model = new \Gamemt\GamemodeModel();
        $data = $model->lists($page, $pagesize, $args);

        //数据重构
        $list = $data['list'];
        unset($data['list']);
        $ret_list = [];
        foreach ($list as $item) {
            $new = array_change_keys($item, [
		'id' => 'id',

		'mode_name' => 'mode_name',

		'status' => 'status',
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
			'mode_name'=>'required',

			'status'=>'required',
])) {
            return false;
        }

        $object_id = $request['id'] ?? false;
        $model = new \Gamemt\GamemodeModel();
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
        $model = new \Gamemt\GamemodeModel();
        if (!$model->delete($request['id'])) {
        return $this->failed('操作失败');
        }
        return $this->succ();
    }

}