<?php

class PermissionModel extends BaseModel {

    /**
     * 加载用户权限数据
     */
    public function allByUser($user_id) {
        $db = $this->DB();
        $select = $db->newSelect('ms_admin_permission')
            ->select('path as path')
            ->select('group as `group`')
            ->select('rw_type')
            ->join('ms_admin_map_role_permission as amrp', 'amrp.permission_id = main_table.id')
            ->join('ms_admin_map_user_role as amur', 'amur.role_id = amrp.role_id')
            ->join('ms_admin_role as ar', 'ar.id = amur.role_id')
            ->where('amur.admin_id', $user_id)
            ->whereNot('ar.status', 0)
            ->where('main_table.status', 1);

        $data = $db->fetchAll($select)->toArray();
        $path = array_column($data, 'path');
        $group = array_unique(array_column($data, 'group'));
        //计算读写权限
        $rw = [];
        foreach($data as $k => $v){
            $rw[$v['group']][$v['rw_type'] == 0 ? 'r' : 'w'] = 1;
        }
        $list = [
            'path' => $path,
            'group' => $group,
            'rw_type' => $rw
        ];
        return $list;
    }


    /**
     * 加载角色权限数据
     */
    public function allByRole($role_id){
        $db = $this->DB();
        $select = $db->newSelect('ms_admin_map_role_permission')
                ->select('concat(ap.`group`,".",ap.rw_type) as `perm`', true)
                ->join('ms_admin_role as ar', 'main_table.role_id = ar.id')
                ->join('ms_admin_permission as ap', 'ap.id = main_table.permission_id')
                ->where('ar.id', $role_id)
                ->where('ar.status', 1)
                ->where('ap.status', 1)
                ->group('perm');
        $data = $db->fetchAll($select)->toArray();
        return array_column($data, 'perm');
    }
    
    
}
