<?php

class OnlineController extends BaseController {
    
    private $model = null;
    public function init() {
        if (!$this->model){
            $this->model = new \Gamemt\OnlineModel();
        }
    }

    public function editAction() {
        $h_title = "在线奖励";
        $info = $this->model->_get();
        $this->getView()->assign('h_title', $h_title);
        $this->getView()->assign('info', $info);
        $this->getView()->assign('pHidden', true);
        $this->display('edit');
    }
    
    /*
     * 创建/修改
     */

    public function saveAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
            'id'=>'require',
            'times'=>'require',
            'time' => 'require',
            'coin' => 'require'
        ])) {
            return false;
        }
        
        $params = [];
        $rule = [];
        
        if (isset($request['times'])){
            if (count($request['times']) != count($request['time']) || count($request['times']) != count($request['coin'])){
                return $this->failed("参数不完善");
            }
            for($i=0; $i<count($request['time']); $i++){
                $rule[] = [
                    'times'=>$request['times'][$i],
                    'time'=>$request['time'][$i],
                    'coin'=>$request['coin'][$i],
                ];
            }
        }
        $params['online_rule'] = json_encode($rule);
        $object_id = $request['id'] ?? false;
        if (empty($object_id)) {
            //新建
            $status = $this->model->_create($params);
        } else {
            //修改
            $status = $this->model->_edit($object_id, $params);
        }
        if (false === $status) {
            return $this->failed('保存失败');
        }
        
        return $this->succ();
    }

    /*
     * 删除商品的状态
     * 批量删除
     */

    public function deleteAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
                    'id' => 'required'
                ])) {
            return false;
        }
        if (!$this->model->_delete($request['id'])) {
            return $this->failed('操作失败');
        }
        return $this->succ();
    }

    /*
     * 删除商品的状态
     * 批量上下架
     * @id值，批量操作的id号
     * @status 0下架 1上架
     */

    public function updateAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
                'id' => 'required',
                'status' => 'required'
                ])) {
            return false;
        }
        
        $params = [];
        $params['status'] = $request['status'];
        if (!$this->model->_update($request['id'], $params)) {
            return $this->failed('操作失败');
        }
        return $this->succ();
    }

}
