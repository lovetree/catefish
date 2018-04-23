<?php

class UserController extends BaseController {

    /**
     * 加载管理员列表
     */
    public function listAction() {
        $input = $this->input();
        $args = array();

        $query = $input->post_get('query');
        if (!is_null($query) && strlen($query) != 0) {
            $args['filters']['username'] = $query;
        }

        $page = $input->post_get('pagenum') ?? 1;
        $pagesize = $input->post_get('pagesize') ?? 10;

        $user = new Admin\UserModel();
        $data = $user->lists($page, $pagesize, $args);

        $list = $data['list']->toArray();
        unset($data['list']);
        $ret_list = [];
        foreach ($list as $item) {
            $new = array_change_keys($item, array(
                'id' => 'adminid',
                'username' => 'username',
                'created_date' => 'created_date',
                'role_name' => 'role_name',
                'status' => 'status',
                'email' => 'email',
                'phone' => 'phone',
                'role_id' => 'role_id'
            ));
            $ret_list[] = $new;
        }
        $data['data'] = $ret_list;
        return $this->succ($data, false);
    }

    /**
     * 启用/禁用管理员
     * @return boolean
     */
    public function activeAction() {
        $input = $this->input();
        //验证参数
        if (!$this->validation($input->request(), array(
                    'admin_id' => 'required',
                    'status' => 'required|inside:0:1',
                ))) {
            return false;
        }

        $user = new Admin\UserModel();
        $status = $user->setActive($input->post_get('admin_id'), $input->post_get('status') == 1 ? true : false);
        if (!$status) {
            return $this->failed('操作失败');
        }
        return $this->succ();
    }

    /**
     * 创建/修改管理员数据
     */
    public function saveAction(){
        $input = $this->input();
        //验证参数
        $request = $input->request();

        if (!$this->validation($request, [

                    'username' => 'required',
                    'password' => 'required|length:6:20',
                    'email' => 'email|length:0:50',
                    'phone' => 'phone'
                ])) {
            return false;
        }

        $admin_id = $input->post_get('admin_id');
        $user = new Admin\UserModel();
        if(empty($admin_id)){
            $login = new UserModel();
            $request['parent_id'] = $login->getLoginUserID();
            //新建
            $status = $user->create($request);
        }else{
            if(isset($request['password']) && $request['password'] === '******'){
                unset($request['password']);
            }
            //修改
            $status = $user->edit($admin_id, $request);
        }
        if (false === $status) {
            return $this->failed('操作失败');
        }
        else if (-1 === $status) {
            return $this->failed('用户名重复');
        }
        return $this->succ();
    }
    
    /**
     * 修改管理员角色信息
     */
    public function roleAction(){
        $input = $this->input();
        //验证参数
        $request = $input->request();
        if (!$this->validation($request, array(
                    'admin_id' => 'required|positive_number'
                ))) {
            return false;
        }
        
        if(empty($request['roles'])){
            $roles = [];
        }else{
            $roles = explode(',', $request['roles']);
        }
        
        
        $user = new Admin\UserModel();
        $status = $user->setRole($request['admin_id'], $roles);
        if(!$status){
            return $this->failed('操作失败');
        }
        return $this->succ();
    }
    
    /**
     * 删除管理员
     */
    public function deleteAction(){
        $input = $this->input();
        //验证参数
        $request = $input->request();
        if (!$this->validation($request, array(
                    'admin_id' => 'required|positive_number'
                ))) {
            return false;
        }
        
        
        $user = new Admin\UserModel();
        $status = $user->deleteAdmin($request['admin_id']);
        if(!$status){
            return $this->failed('操作失败');
        }
        return $this->succ();
    }
    
}
