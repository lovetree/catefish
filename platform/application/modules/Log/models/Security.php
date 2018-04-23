<?php

namespace Log;

class SecurityModel extends \BaseModel {

    /**
     * 获取管理员列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
    public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_admin_log')
                ->select('main_table.operator_name')
                ->select('main_table.action')
                ->select('main_table.action_desc')
                ->select('main_table.target_id')
                ->select('main_table.result_code')
                ->select('main_table.result_text')
                ->select('main_table.detail')
                ->select('main_table.created_date')
                ->select('main_table.ip')
                ->order('created_date', 'desc');

        if (is_array($args)) {
            //wehre
            if (isset($args['filters']['admin_id'])) {
                $select->where('main_table.operator_id', $args['filters']['admin_id']);
            }
            if (isset($args['filters']['admin_name'])) {
                $select->whereLike('main_table.operator_name', '%' . $args['filters']['admin_name'] . '%');
            }
            if (isset($args['filters']['path'])) {
                $select->whereLike('main_table.action', '%' . $args['filters']['path'] . '%');
            }
            if (isset($args['filters']['start_time'])) {
                $select->where('main_table.created_date', $args['filters']['start_time'], '>=');
            }
            if (isset($args['filters']['end_time'])) {
                $select->where('main_table.created_date', $args['filters']['end_time'], '<=');
            }
            if (isset($args['filters']['target_id'])) {
                $select->whereLike('main_table.target_id', '%' . $args['filters']['target_id'] . '%');
            }
        }

        $data = $db->fetchAllPage($select, $page, $pagesize);
        $data['list'] = $data['list']->toArray();
        return $data;
    }

    public function getRoomName($id){
        $db = $this->DB();
        $select = $db->newSelect('ms_gm_room')
            ->select('room_name')
            ->where('id',$id);
        return $db->fetch($select)->getData('room_name');
    }
    public function getRole($id){
        $db = $this->DB();
        $select = 'select name from ms_admin_role WHERE id in ('.$id.')';
        return $db->search($select);
    }

}
