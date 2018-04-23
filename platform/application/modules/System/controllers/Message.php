<?php

class MessageController extends BaseController {

    /**
     * 查询系统消息列表
     */
    public function listAction() {
        $request = $this->input()->request();
        $args = array();

        //搜索条件
        !empty($request['query']) && ($args['filters']['content'] = $request['query']);
        
        //查询页码
        $page = $request['pagenum'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;

        //查询数据
        $model = new System\MessageModel();
        $data = $model->lists($page, $pagesize, $args);

        //数据重构
        $list = $data['list'];
        unset($data['list']);
        $ret_list = [];
        foreach ($list as $item) {
            $item['start_time'] = empty($item['start_time']) ? '' : date('Y-m-d H:i:s', $item['start_time']);
            $item['end_time'] = empty($item['start_time']) ? '' : date('Y-m-d H:i:s', $item['end_time']);
            $new = array_change_keys($item, array(
                'id' => 'id',
                'content' => 'content',
                'status' => 'status',
                'start_time' => 'start_time',
                'end_time' => 'end_time',
                'comment' => 'comment'
            ));
            $ret_list[] = $new;
        }
        $data['data'] = $ret_list;

        //反馈数据
        return $this->succ($data, false);
    }

    /**
     * 创建/修改消息数据
     */
    public function saveAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
                    'msg_id' => 'positive_number',
                    'content' => 'required|length:1:255',
                    'start_time' => 'date',
                    'end_time' => 'date'
                ])) {
            return false;
        }
        $object_id = $request['msg_id'] ?? false;
        !empty($request['start_time']) && ($request['start_time'] = strtotime($request['start_time'])); 
        !empty($request['end_time']) && ($request['end_time'] = strtotime($request['end_time'])); 
        
        $model = new \System\MessageModel();
        if (empty($object_id)) {
            //新建
            $status = $model->create($request);
        } else {
            //修改
            $status = $model->edit($object_id, $request);
        }
        if (false === $status) {
            return $this->failed('操作失败');
        }
        return $this->succ();
    }

    /**
     * 设置消息的状态
     */
    public function activeAction(){
         //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
                    'msg_id' => 'required|positive_number',
                    'status' => 'required|inside:-1:0:1',
                ])) {
            return false;
        }
        $model = new \System\MessageModel();
        if(!$model->setStatus($request['msg_id'], $request['status'])){
            return $this->failed('操作失败');
        }
        return $this->succ();
    }
    
    /**
     * 删除消息的状态
     */
    public function deleteAction(){
         //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
                    'msg_id' => 'required'
                ])) {
            return false;
        }
        $model = new \System\MessageModel();
        if(!$model->delete($request['msg_id'])){
            return $this->failed('操作失败');
        }
        return $this->succ();
    }
    
}
