<?php

namespace Player;

class WinorloseModel extends \BaseModel {

    /**
     * 获取输赢排行榜数据
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
    public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB_GameLog();
        $sql = '
            select ### from(
                select 
                    user_id, 
                    mu.username,
                    mu.nickname,
                    sum(win_gold) as win_gold,
                    sum(gold_tax) as service_fee
                from ms_user_log mul, ms_db_main.ms_user mu
                where mu.id = mul.user_id
                ';

        if (is_array($args) && isset($args['filters'])) {
            if (isset($args['filters']['userid'])) {
                $sql .= ' and mul.user_id like "%' .$args['filters']['userid']. '%"';
            }
            if (isset($args['filters']['username'])) {
                $sql .= ' and mu.username like "%' .$args['filters']['username']. '%"';
            }
            if (isset($args['filters']['nickname'])) {
                $sql .= ' and mu.nickname like "%' .$args['filters']['nickname']. '%"';
            }
            if (isset($args['filters']['start_time'])) {
                $sql .= ' and mul.created_date >=' .$args['filters']['start_time'];
            }
            if (isset($args['filters']['end_time'])) {
                $sql .= ' and mul.created_date <' .$args['filters']['end_time'];
            }
            if (isset($args['filters']['game_id'])) {
                $sql .= ' and mul.game_id = ' .$args['filters']['game_id'];
            }
        }

        $sql .= ' group by mul.user_id) t';

        $data['total'] = $db->search(str_replace('###', 'count(1) as cnt', $sql))[0]['cnt'];

        $sql .= ' order by win_gold  '.$args['order']['type'];
        $sql .= ' limit ' . ($page-1) * $pagesize . ', '. $pagesize;
        $data['data']  = $db->search(str_replace('###', '*', $sql));
        $data['pageCount'] = ceil($data['total']/$pagesize);

        return $data;
    }

}
