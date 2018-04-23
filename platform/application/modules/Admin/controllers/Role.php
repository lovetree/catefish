<?php

class RoleController extends BaseController {

    /**
     * 加载角色列表
     */
    public function listAction() {
        $request = $this->input()->request();
        $args = array();

        $query = $request['query'] ?? null;
        if (!is_null($query) && strlen($query) != 0) {
            $args['filters']['name'] = $query;
        }
        !empty($request['available']) && ($args['filters']['status'] = 1);

        $page = $request['pagenum'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;

        $role = new Admin\RoleModel();
        $data = $role->lists($page, $pagesize, $args);

        $list = $data['list']->toArray();
        unset($data['list']);
        $ret_list = [];
        foreach ($list as $item) {
            $new = array_change_keys($item, array(
                'id' => 'role_id',
                'name' => 'name',
                'status' => 'status'
            ));
            $ret_list[] = $new;
        }
        $data['data'] = $ret_list;
        return $this->succ($data, false);
    }

    /**
     * 新建/修改角色名称
     */
    public function saveAction() {
        $request = $this->input()->request();
        //验证参数
        if (!$this->validation($request, array(
                    'role_id' => 'positive_number',
                    'name' => 'required|string:2'
                ))) {
            return false;
        }

        $role = new Admin\RoleModel();
        $status = $role->save($request['role_id'] ?? null, $request['name']);
        if (!$status) {
            return $this->failed('保存失败');
        }
        return $this->succ();
    }

    /**
     * 加载当前用户的权限内容
     */
    public function permsAction() {
        $role = new \Admin\RoleModel();
        $perms = $role->getPermissions();
        return $this->succ($perms);
    }
    
    /**
     * 加载角色的权限
     */
    public function permAction() {
        $request = $this->input()->request();
        //验证参数
        if (!$this->validation($request, array(
                    'role_id' => 'required|positive_number',
                ))) {
            return false;
        }
        
        $permModel = new PermissionModel();
        $perms = $permModel->allByRole($request['role_id']);
        return $this->succ($perms);
    }
    
    /**
     * 保存角色的权限
     */
    public function setpermsAction(){
        $request = $this->input()->request();
        //验证参数
        if (!$this->validation($request, array(
                    'role_id' => 'required|positive_number'
                ))) {
            return false;
        }
        if(empty($request['perms'])){
            $perms = [];
        }else{
            $perms = explode(',', $request['perms']);
        }
        
        $role = new \Admin\RoleModel();
        if(!empty($request['role_name'])){
            $role->save($request['role_id'], $request['role_name']);
        }
        if(!$role->savePermissions($request['role_id'], $perms)){
            return $this->failed('保存失败');
        }
        return $this->succ();
    }

    /**
     * 启用/禁用角色
     * @return boolean
     */
    public function activeAction() {
        $input = $this->input();
        //验证参数
        if (!$this->validation($input->request(), array(
                    'role_id' => 'required',
                    'status' => 'required|inside:0:1',
                ))) {
            return false;
        }

        $model = new Admin\RoleModel();
        $status = $model->setActive($input->post_get('role_id'), $input->post_get('status') == 1 ? true : false);
        if (!$status) {
            return $this->failed('操作失败');
        }
        return $this->succ();
    }
    
    /**
     * 删除角色
     */
    public function deleteAction(){
        $input = $this->input();
        //验证参数
        $request = $input->request();
        if (!$this->validation($request, array(
                    'role_id' => 'required|positive_number'
                ))) {
            return false;
        }
        
        $model = new Admin\RoleModel();
        $status = $model->deleteRole($request['role_id']);
        if(!$status){
            return $this->failed('操作失败');
        }
        return $this->succ();
    }

    /**
     * 数据清理类型
     */
    public function cleanlistAction(){
        $list = array('金币流水','游戏记录','输赢排名','礼物赠送记录','手机短信','交易日报','交易记录','反馈管理','活跃统计','存量统计','安全日志');
        foreach ($list as $item){
            $data[]['type'] = $item;
        }
        $result['data'] = $data;
        $result['total'] = count($list);
        $result['pageCount'] = 1;
        return $this->succ($result, false);
    }
    public function docleanAction(){
        $input = $this->input()->request();
        $checktime = $input['checktime'];
        if(!strlen(trim($input['tables']))){
            $this->failed('无效请求,请选择至少一种数据类型!');
            exit;
        }
        $tables = explode(',',$input['tables']);
        $model = new \Admin\RoleModel();
        $result = $model->delData($tables,$checktime);
        if ($result){
            $this->succ();
        }else{
            $this->failed($result);
        }
    }
    
}
