<?php

namespace Admin;

class RoleModel extends \BaseModel {

    /**
     * 获取角色列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
    public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $this->DB()->newSelect('ms_admin_role')
                ->select('name')
                ->select('status')
                ->whereNot('status', -1)
                ->order('id', 'asc');

        if (is_array($args)) {
            //wehre
            if (isset($args['filters']['name'])) {
                $select->whereLike('name', '%' . $args['filters']['name'] . '%');
            }
            if (isset($args['filters']['status'])) {
                $select->where('status', $args['filters']['status']);
            }
        }

        $data = $db->fetchAllPage($select, $page, $pagesize);
        return $data;
    }

    /**
     * 新建/修改角色名称
     * @param int $role_id
     * @param string $name
     * @return boolean
     */
    public function save($role_id, $name) {
        $role = $this->DB()->getTable('ms_admin_role');
        if (empty($role_id)) {
            //新建
            $role->setData('name', $name);
        } else {
            //修改
            if (!$role->load($role_id)) {
                return false;
            }
            $role->setData('name', $name);
        }
        return $role->save();
    }

    /**
     * 获取当前用户可以使用配置的权限
     */
    public function getPermissions() {
        $user = new \UserModel();
        $all = $this->isAdmin();
        $navs = include APP_CFG_NAV;
        $rw = [];
        if ($all) {
            $access_list = $navs;
        } else {
            $perms = $user->getPermissions();
            $group = $perms['group'];
            $rw = $perms['rw_type'];

            $group = array_flip($group);
            $access_list = array_intersect_key($navs, $group);
        }
        $ret = [];
        foreach ($access_list as $key => $item) {
            if (!is_array($item)) {
                continue;
            }
            list($label, $url) = $item;
            $parent_key = explode('.', $key)[0];
            $ret[$parent_key]['name'] = $navs[$parent_key];
            $tmp = [
                'label' => $label,
                'value' => $key
            ];
            if ($all) {
                $tmp['r'] = 1;
                $tmp['w'] = 1;
            } else {
                $tmp['r'] = $rw[$item]['r'] ?? 0;
                $tmp['w'] = $rw[$item]['w'] ?? 0;
            }
            $ret[$parent_key]['sub'][] = $tmp;
        }
        return array_values($ret);
    }
    /**
     * 获取当前用户可以使用配置的权限
     */
    public function getPermission() {


        $navs = include APP_CFG_NAV;
        $rw = [];

        $access_list = $navs;
        return $navs;
        $ret = [];
        foreach ($access_list as $key => $item) {
            if (!is_array($item)) {
                continue;
            }
            list($label, $url) = $item;
            $parent_key = explode('.', $key)[0];
            $ret[$parent_key]['name'] = $navs[$parent_key];
            $tmp = [
                'label' => $label,
                'value' => $key
            ];


            $ret[$parent_key]['sub'][] = $tmp;
        }
        return array_values($ret);
    }
    /**
     * 获取单个角色的权限
     */
    public function getRolePerms($role_id) {
        $navs = include APP_CFG_NAV;
        $rw = [];
        $perm = new \PermissionModel();
        $perms = $perm->allByUser($user_id);
        $group = $perms['group'];
        $rw = $perms['rw_type'];

        $group = array_flip($group);
        $access_list = array_intersect_key($navs, $group);
        $ret = [];
        foreach ($access_list as $key => $item) {
            if (!is_array($item)) {
                continue;
            }
            list($label, $url) = $item;
            $parent_key = explode('.', $key)[0];
            $ret[$parent_key]['name'] = $navs[$parent_key];
            $tmp = [
                'label' => $label,
                'value' => $key
            ];
            if ($all) {
                $tmp['r'] = 1;
                $tmp['w'] = 1;
            } else {
                $tmp['r'] = $rw[$item]['r'] ?? 0;
                $tmp['w'] = $rw[$item]['w'] ?? 0;
            }
            $ret[$parent_key]['sub'][] = $tmp;
        }
        return array_values($ret);
    }

    /**
     * 保存角色的权限数据
     * @param int $role_id
     * @param array $perms 权限group列表
     */
    public function savePermissions(int $role_id, array $perms = []): bool {
        $db = $this->DB();
        $role = $db->getTable('ms_admin_role');
        if (!$role->load([
                    $role->getPrimaryKey() => $role_id,
                    'status' => 1
                ])) {
            return false;
        }
        return $db->trancation(function($db, &$rollback) use($role_id, $perms) {
                    //删除原有关联
                    $select = $db->newSelect('ms_admin_map_role_permission');
                    $select->where('role_id', $role_id);
                    $db->exec($select->deleteSql());
                    if(count($perms)){
                        //整理数据
                        array_walk($perms, function(&$item) use($db) {
                            $item = $db->getHandle()->quote($item);
                        });
                        $perms_str = implode(',', $perms);
                        //插入
                        $sql = <<<SQL
                        insert into ms_admin_map_role_permission(role_id, permission_id)
                            select {$role_id} as 'role_id',id as 'permission_id' from (
                                    SELECT id, concat(`group`,'.',rw_type) as type FROM `ms_admin_permission` as ap
                            ) as t
                            where type in ($perms_str)
SQL;
                        $db->exec($sql);
                    }
                    return true;
                });
    }

    /**
     * 设置角色有效状态
     */
    public function setActive(int $role_id, bool $status): bool {
        $table = $this->DB()->getTable('ms_admin_role');
        $filter = array(
            $table->getPrimaryKey() => $role_id,
            'status' => array('!=' => -1)
        );
        if (!$table->load($filter)) {
            return false;
        }
        $table->setData('status', $status ? 1 : 0);
        return $table->save();
    }

    /**
     * 删除角色
     * @param int $role_id
     */
    public function deleteRole(int $role_id){
        $role = $this->DB()->getTable('ms_admin_role');
        if(!$role->load($role_id)){
            return false;
        }
        $role->setData('status', -1);
        return $role->save();
    }

    /**
     * 清理数据
     */
    public function delData($tables,$checktime){
        $list = array('金币流水','游戏记录','输赢排名','礼物赠送记录','手机短信','交易日报','交易记录','反馈管理','活跃统计','存量统计','安全日志');
        $tables = array_intersect($tables,$list);
        if(!$tables){
            return false;
        }
        switch ($checktime){
            case 'month':
                $checktime = strtotime(date('Y-m-01'));
                break;
            case 'months':
                $checktime = strtotime(date('Y-m-01',strtotime('-3 month')));
                break;
            case 'year':
                $checktime = strtotime(date('Y-m-01',strtotime('-6 month')));
                break;
            case 'all':
                $checktime = 9999999999;
                break;
        }

        foreach ($tables as $item){
            switch ($item){
                case '金币流水':
                    $sql = 'delete from ms_gold_log WHERE  create_time <'.$checktime;
                    $result = $this->DB()->exec($sql);
                    if($result===false){
                        return false;
                    }
                    break;
                case '游戏记录':
                    $sql = 'delete from ms_user_log WHERE  created_date <'.$checktime;
                    $result = $this->DB_GameLog()->exec($sql);
                    if(false===$result){
                        return false;
                    }
                    break;
                case '输赢排名':
                    $sql = 'delete from ms_user_log WHERE  created_date <'.$checktime;
                    $result = $this->DB_GameLog()->exec($sql);
                    if(false===$result){
                        $this->DB()->rollBack();
                        return false;
                    }
                    break;
                case '礼物赠送记录':
                    $sql = 'delete from ms_gift_give_log WHERE  c_time <'.$checktime;
                    $result = $this->DB()->exec($sql);
                    if(false===$result){

                        return false;
                    }
                    break;
                case '手机短信':
                    $sql = 'delete from ms_sms WHERE  c_time <'.$checktime;
                    $result = $this->DB()->exec($sql);
                    if(false===$result){

                        return false;
                    }
                    break;
                case '交易日报':
                    $sql = 'delete from ms_order_stat WHERE  stat_date <'.$checktime;
                    $result = $this->DB()->exec($sql);
                    if(false===$result){

                        return false;
                    }
                    break;
                case '交易记录':
                    $sql = 'delete from ms_order WHERE  created_date <'.$checktime;
                    $result = $this->DB()->exec($sql);
                    if(false===$result){

                        return false;
                    }
                    break;
                case '反馈管理':
                    $sql = 'delete from ms_feedback WHERE  created_date <'.$checktime;
                    $result = $this->DB()->exec($sql);
                    if(false===$result){
                        return false;
                    }
                    break;
                case '活跃统计':
                    $sql = 'delete from ms_user_active WHERE  stat_time <'.$checktime;
                    $result = $this->DB()->exec($sql);
                    if(false===$result){

                        return false;
                    }
                    break;
                case '存量统计':
                    $sql = 'delete from ms_estate_stat WHERE  stat_date <'.$checktime;
                    $result = $this->DB()->exec($sql);
                    if(false===$result){

                        return false;
                    }
                    break;
                case '安全日志':
                    $sql = 'delete from ms_admin_log WHERE  created_date <'.$checktime;
                    $result = $this->DB()->exec($sql);
                    if(false===$result){
                        return false;
                    }
                    break;
            }
        }
        return true;
    }
    
}
