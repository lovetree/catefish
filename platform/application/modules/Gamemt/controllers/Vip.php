<?php

class VipController extends BaseController {
    
    private $model = null;
    public function init() {
        if (!$this->model){
            $this->model = new \Gamemt\VipModel();
        }
    }
    
    /*
     * 查询列表
     */
    public function listAction() {
        $request = $this->input()->request();
        $args = array();
        
        //搜索条件
        $query = $request['query'] ?? false;
        $query_type = $request['query_type'] ?? false;
        if (!empty($query) && !empty($query_type)) {
            switch ($query_type) {
                case 'id':
                    $args['filters']['id'] = $query;
                    break;
                default:
                    $args['filters']['id'] = $query;
                    break;
            }
        }

        //查询页码
        $page = $request['page'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;

        //查询数据
        $data = $this->model->_list($page, $pagesize, $args);
        //数据重构
        $list = $data['list'];
        unset($data['list']);
        $ret_list = [];
        
        foreach ($list as $item) {
            $new = array_change_keys($item, $this->model->keys, true);
            $ret_list[] = $new;
        }
        
        $pageInfo = [];
        $pageInfo['page'] = $page;
        $pageInfo['pageSize'] = $pagesize;
        $pageInfo['pageCount'] = $data['pageCount'];
        $pageInfo['total'] = $data['total'];
        $pageInfo['pageUrl'] = '/gamemt/notice/list';
        $this->getView()->assign('pageInfo', $pageInfo);
        $this->walkData($ret_list);
        $this->getView()->assign('list', $ret_list);
        $this->display('list');
    }
    
    public function editAction() {
        $h_title = "VIP和签到配置";
        $request = $this->input()->request();
        $info = $this->model->_get(['id'=>$request['id']]);
        $this->getView()->assign('h_title', $h_title);
        $this->getView()->assign('info', $info);
        $this->display('edit');
    }
    
    /*
     * 创建/修改
     */

    public function saveAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, $this->model->valid)) {
            return false;
        }
        
        $object_id = $request['id'] ?? false;
        if (!$object_id) return $this->failed("id号不合法");
        $status = $this->model->_edit($object_id, $request);
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
