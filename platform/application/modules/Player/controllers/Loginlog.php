<?php
class LoginlogController extends BaseController {
/*** 查询列表***/
public function listAction() {
        $request = $this->input()->request();
        $args = array();

        //搜索条件
        $query = $request['query'] ?? false;
        $query_type = $request['query_type'] ?? false;
        if(!empty($query) && !empty($query_type)){
            switch ($query_type){
                case 'username':
                    $args['filters']['username'] = $query;
                    break;
                case 'nickname':
                    $args['filters']['nickname'] = $query;
                    break;
                default:
                    $args['filters']['username'] = $query;
                    break;
            }
        }

        if(isset($request['start_time']) && !empty($request['start_time'])){
            $args['filters']['start_time'] = strtotime($request['start_time']);
        }
        if(isset($request['end_time']) && !empty($request['end_time'])){
            $args['filters']['end_time'] = strtotime($request['end_time'] . '+1 day');
        }
        //点击按钮查询优先级高
        if(isset($request['query_today']) && $request['query_today'] == '1'){
            $args['filters']['start_time'] = strtotime(date("Y-m-d"));
            $args['filters']['end_time'] = strtotime(date("Y-m-d", strtotime('+1 day')));
        }elseif(isset($request['query_yes']) && $request['query_yes'] == '1'){
            $args['filters']['start_time'] = strtotime(date("Y-m-d", strtotime('-1 day')));
            $args['filters']['end_time'] = strtotime(date("Y-m-d"));
        }elseif(isset($request['query_week']) && $request['query_week'] == '1'){
            $args['filters']['start_time'] = strtotime(date("Y-m-d", strtotime('-1 week')));
            $args['filters']['end_time'] = strtotime(date("Y-m-d"));
        }

        //查询页码
        $page = $request['pagenum'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;

        //查询数据
        $model = new \Player\LoginlogModel();
        $data = $model->lists($page, $pagesize, $args);

        //数据重构
        $list = $data['list'];
        unset($data['list']);
        $ret_list = [];
        foreach ($list as $item) {
            $new = array_change_keys($item, [
                'id' => 'id',

                'game_id' => 'game_id',

                'usrename' => 'usrename',

                'nickname' => 'nickname',

                'login_time' => 'login_time',

                'login_ip' => 'login_ip',

                'address' => 'address',

                'login_type' => 'login_type',
            ], false);

            $new['login_time'] = date('Y-m-d', $new['login_time']);

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

	'game_id'=>'required',

	'usrename'=>'required',

	'nickname'=>'required',

	'login_time'=>'required',

	'login_ip'=>'required',

	'address'=>'required',

	'login_type'=>'required',

	'create_time'=>'required',
])) {
            return false;
        }

        $object_id = $request['id'] ?? false;
        $request['total_price'] = $request['price'];
        $model = new \Player\LoginlogModel();
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
        $model = new \Player\LoginlogModel();
        if (!$model->delete($request['goods_id'])) {
        return $this->failed('操作失败');
        }
        return $this->succ();
    }

}