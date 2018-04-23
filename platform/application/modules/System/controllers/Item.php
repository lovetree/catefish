<?php

class ItemController extends BaseController {

    /**
     * 查询道具列表
     */
    public function listAction() {
        $request = $this->input()->request();
        $args = array();

        //搜索条件
        !empty($request['query']) && ($args['filters']['name'] = $request['query']);
        
        //查询页码
        $page = $request['pagenum'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;

        //查询数据
        $model = new System\ItemModel();
        $data = $model->lists($page, $pagesize, $args);

        //数据重构
        $list = $data['list'];
        unset($data['list']);
        $ret_list = [];
        foreach ($list as $item) {
            $new = array_change_keys($item, array(
                'id' => 'item_id',
                'name' => 'name'
            ));
            $ret_list[] = $new;
        }
        $data['data'] = $ret_list;

        //反馈数据
        return $this->succ($data, false);
    }

    /**
     * 创建/修改
     */
    public function saveAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
                    'item_id' => 'positive_number',
                    'name' => 'required|length:1:50'
                ])) {
            return false;
        }
        $object_id = $request['item_id'] ?? false;
        
        $model = new System\ItemModel();
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

    /**
     * 设置消息的状态
     */
    public function activeAction_unused(){
         //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
                    'item_id' => 'required|positive_number',
                    'status' => 'required|inside:0:1',
                ])) {
            return false;
        }
        $model = new System\ItemModel();
        if(!$model->setStatus($request['item_id'], $request['status'])){
            return $this->failed('操作失败');
        }
        return $this->succ();
    }
    
    /**
     * 删除消息的状态
     */
    public function deleteAction_Unused(){
         //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
                    'item_id' => 'required'
                ])) {
            return false;
        }
        $model = new System\ItemModel();
        if(!$model->delete($request['item_id'])){
            return $this->failed('操作失败');
        }
        return $this->succ();
    }
    
}
