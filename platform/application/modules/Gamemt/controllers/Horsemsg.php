<?php
class HorsemsgController extends BaseController {
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
        $model = new \Gamemt\HorsemsgModel();
        $data = $model->lists($page, $pagesize, $args);

        //数据重构
        $list = $data['list'];
        unset($data['list']);
        $ret_list = [];
        foreach ($list as $item) {
            $new = array_change_keys($item, [
            'msg_content' => 'msg_content',

            'game_type' => 'game_type',

            'game_mode' => 'game_mode',

            'is_forbid' => 'is_forbid',

            'remark' => 'remark',
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

            'msg_content'=>'required',

            'game_type'=>'required',

            'game_mode'=>'required'

        ])) {
            return false;
        }

        $object_id = $request['id'] ?? false;

        //开启事务
        $this->DB()->beginTransaction();
        //mysql数据修改
        $model = new \Gamemt\HorsemsgModel();
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
        //redis数据修改
        $redis = PRedis::instance();
        $redis->select(R_MESSAGE_DB);
        $key = RK_BROADCAST_LIST;

        $result = $model->lists();

        $dataArr = [];
        foreach ($result['list'] as $data){
            $dataArr[] = [
                'id' => $data['id'],
                'content' => $data['msg_content'],
                'game_type' => $data['game_type'],
                'game_mode' => $data['game_mode'],
            ];
        }

        if($status = $redis->set($key, json_encode($dataArr))){
            $this->DB()->commit();
        }else{
            $this->DB()->rollback();
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
        $model = new \Gamemt\HorsemsgModel();
        if (!$model->delete($request['goods_id'])) {
        return $this->failed('操作失败');
        }
        return $this->succ();
    }

}