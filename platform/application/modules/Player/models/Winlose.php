<?php

namespace Player;

class WinloseModel extends \BaseModel {

    /**
     * 获取排行榜数据
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
    public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        //搜索日志数据
        $gamelog_db = $this->DB_GameLog();
        $select = $gamelog_db->newSelect('ms_winlose_stat')
                ->select('user_id')
                ->select('username')
                ->select('nickname')
                ->select('win_num')
                ->select('lose_num')
                ->select('created_date')
                ->select('duration')
                ->order('win_num', 'desc');

        if (is_array($args)) {
            if (!isset($args['filters']['start_time'])) {
                $args['filters']['start_time'] = strtotime(date('Y-m-d'));
            }
            $select->where('created_date', $args['filters']['start_time'], '>=');

            if (!isset($args['filters']['end_time'])) {
                $args['filters']['end_time'] = $args['filters']['start_time']  + 86400;
            }
            $select->where('created_date', $args['filters']['end_time'], '<=');

            if (isset($args['filters']['username'])) {
                    $select->where('username', $args['filters']['username']);
            }
        }

        $data = $gamelog_db->fetchAllPage($select, $page, $pagesize);

        $data['list'] = $data['list']->toArray();
        return $data;
    }



}
