<?php

class FishController extends BaseController {
    
    private $model = null;
    public function init() {
        if (!$this->model){
            $this->model = new \Gamemt\FishModel();
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
        $pageInfo['pageUrl'] = '/gamemt/fish/list';
        $this->getView()->assign('pageInfo', $pageInfo);
        $this->walkData($ret_list);
        $this->getView()->assign('list', $ret_list);
        $this->display('list');
    }

    public function editAction() {
        $h_title = "";
        $request = $this->input()->request();
        $info = [];
        $h_title = "添加鱼种";
        if (isset($request['id']) && $request['id']){
            $params = [];
            $params['id'] = $request['id'];
            $info = $this->model->_get($params);
            $h_title = "编辑鱼种";
        }
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
        
        $multiple = explode(",", $request['multiple']);
        foreach ($multiple as $v){
            if (!is_numeric($v) || $v <0){
                return $this->failed("金币倍速不合法");
            }
        }
        
        $object_id = $request['id'] ?? false;
        if (empty($object_id)) {
            $info = $this->model->_get(['kind'=>$request['kind']]);
            if ($info){
                return $this->failed("kind不可重复添加");
            }
            //新建
            $status = $this->model->_create($request);
        } else {
            //修改
            $status = $this->model->_edit($object_id, $request);
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

    /***
     * 捕鱼调控系数
     */
    public function coefficientAction() {
        $h_title = "调控系数";
        $this->getView()->assign('h_title', $h_title);
        $info = $this->model->getCoefficient();
        $this->getView()->assign('info', $info);
        $this->getView()->assign('pHidden', true);
        $this->display('coefficient');
    }
    
    public function ajaxCoeAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
                'controlfactor' => 'number|min:-200|max:200',
                'room1' => 'number|min:-200|max:200',
                'room2' => 'number|min:-200|max:200',
                'room3' => 'number|min:-200|max:200',
                'room4' => 'number|min:-200|max:200'
                ])) {
            return false;
        }
        
        $params = [];
        $params['controlfactor'] = $request['controlfactor'];
        $params['room1'] = $request['room1'];
        $params['room2'] = $request['room2'];
        $params['room3'] = $request['room3'];
        $params['room4'] = $request['room4'];
        if (!$this->model->_ajaxCoe($params)) {
            return $this->failed('操作失败');
        }
        return $this->succ();
    }
    
}
