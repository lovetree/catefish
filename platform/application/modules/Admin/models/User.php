<?php

namespace Admin;

class UserModel extends \BaseModel {

    /**
     * 获取管理员列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
    public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $this->DB()->newSelect('ms_admin')
                ->joinLeft('ms_admin_map_user_role as amur', 'amur.admin_id = main_table.id')
                ->joinLeft('ms_admin_role as ar', 'ar.id = amur.role_id and ar.`status` = 1')
                ->select('main_table.username')
                ->select('main_table.created_date')
                ->select('main_table.status')
                ->select('main_table.phone')
                ->select('main_table.email')
                ->select('group_concat(ar.name) as role_name', true)
                ->select('group_concat(ar.id) as role_id', true)
                ->where('main_table.status', array('>=' => 0))
                ->group('main_table.id')
                ->order('created_date', 'desc');

        //非超级管理员只能看到名下的用户
        if (!$this->isAdmin()) {
            $select->where('main_table.parent_id', $this->getLoginUserID());
        }

        if (is_array($args)) {
            //wehre
            if (isset($args['filters']['username'])) {
                $select->whereLike('main_table.username', '%' . $args['filters']['username'] . '%');
            }
        }

        $data = $db->fetchAllPage($select, $page, $pagesize);
        return $data;
    }

    /**
     * 设置管理员有效状态
     */
    public function setActive(int $admin_id, bool $status): bool {
        $admin = $this->DB()->getTable('ms_admin');
        $filter = array(
            $admin->getPrimaryKey() => $admin_id,
            'status' => array('!=' => -1)
        );
        //非超级管理员只能操作名下的用户
        if (!$this->isAdmin()) {
            $filter['parent_id'] = $this->getLoginUserID();
        }
        if (!$admin->load($filter)) {
            return false;
        }
        $admin->setData('status', $status ? 1 : 0);
        return $admin->save();
    }

    /**
     * 创建管理员
     * @param array $params
     * @return mixed false: 保存失败; -1: 用户名重复; 其他=成功
     */
    public function create(array $params = array()) {
        $params = array_fetch($params, 'username', 'password', 'phone', 'email', 'parent_id');
        $admin = $this->DB()->getTable('ms_admin');
        if ($admin->load($params['username'], 'username')) {
            return -1;
        }
        $admin->setData($params);
        $admin->setData('password', md5($params['password'] ?? '123456'));
        return $admin->save();
    }

    /**
     * 修改管理员信息
     * @param int $admin_id
     * @param array $params
     * @return boolean
     */
    public function edit(int $admin_id, array $params = array()) {
        $params = array_fetch($params, 'username', 'password', 'phone', 'email');
        $admin = $this->DB()->getTable('ms_admin');
        $filter = array(
            $admin->getPrimaryKey() => $admin_id,
            'status' => array('!=' => -1)
        );
        //非超级管理员只能操作名下的用户
        if (!$this->isAdmin()) {
            $filter['parent_id'] = $this->getLoginUserID();
        }
        if (!$admin->load($filter)) {
            return false;
        }
        $admin->setData($params);
        $admin->setData('password', md5($params['password'] ?? '123456'));
        return $admin->save();
    }

    /**
     * 修改管理员角色信息
     * @param int $admin_id
     * @param array $role_list 角色ID列表
     * @return boolean
     */
    public function setRole(int $admin_id, array $role_list = array()) {
        $db = $this->DB();
        $admin = $db->getTable('ms_admin');
        $filter = array(
            $admin->getPrimaryKey() => $admin_id,
            'status' => array('!=' => -1)
        );
        //非超级管理员只能操作名下的用户
        if (!$this->isAdmin()) {
            $filter['parent_id'] = $this->getLoginUserID();
        }
        if (!$admin->load($filter)) {
            return false;
        }

        return $db->trancation(function($db, &$rollback) use($admin_id, $role_list) {
                    $select = $db->newSelect('ms_admin_map_user_role');
                    $select->where('admin_id', $admin_id);
                    $db->exec($select->deleteSql());
                    //检测角色ID
                    $role_select = $db->newSelect('ms_admin_role')
                            ->whereIn('id', $role_list)
                            ->where('status', 1)
                            ->select('id');
                    $role_ids = $db->search($role_select->toString());
                    if (empty($role_ids)) {
                        return true;
                    }
                    foreach ($role_ids as &$item) {
                        array_unshift($item, $admin_id);
                    }
                    if (!$db->exec($select->insert(array('admin_id', 'role_id'), $role_ids))) {
                        $rollback = true;
                        return false;
                    }
                    return true;
                });
    }

    /**
     * 获取用户数据
     * @param int|array $user_id 用户ID, 可以是一个数组
     * @param array $fields 获取想要的字段信息
     * @return \Dao\Collection
     */
    public function getAdminData($user_id, array $fields = []){
        $fields = array_flip($fields);
        $fields = array_fetch($fields, ['username']);
        if(empty($fields)){
            return false;
        }
        $fields = array_keys(array_change_keys($fields, array(
            'username' => 'main_table.username',
        )));
        $select = $this->DB()->newSelect('ms_admin');
        $select->select($fields);
        
        //条件
        if(is_array($user_id)){
            $select->whereIn('main_table.id', $user_id);
        }else{
            $select->where('main_table.id', $user_id);
        }
        
        $data = $this->DB()->fetchAll($select);
        return $data;
    }
    
    /**
     * 删除管理员
     */
    public function deleteAdmin($admin_id){
        $admin = $this->DB()->getTable('ms_admin');
        if(!$admin->load($admin_id)){
            return false;
        }
        $admin->setData('status', -2);
        return $admin->save();
    }
    
}
