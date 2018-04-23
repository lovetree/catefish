<?php

namespace System;

class FeedbackModel extends \BaseModel {

    /**
     * 获取商品列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
    public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_feedback')
                ->joinLeft('ms_user as u', 'u.id = main_table.user_id')
                ->joinLeft('ms_user_info as info', 'info.user_id = main_table.user_id')
                ->select(['main_table.*'])
                ->select(['u.username', 'info.nickname'])
                ->order('created_date', 'desc');

        if (is_array($args)) {
            //wehre
            if (isset($args['filters']['wx_unionid'])) {
                $select->where('u.wx_unionid', $args['filters']['wx_unionid']);
            }
        }

        $data = $db->fetchAllPage($select, $page, $pagesize);

        $data['list'] = $data['list']->toArray();
        return $data;
    }

}
