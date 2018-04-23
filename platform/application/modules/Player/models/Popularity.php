<?php

namespace Player;

class PopularityModel extends \BaseModel {

    /**
     * 获取输赢排行榜数据
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
    public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $sql = 'select ### from(select g.user_id,g.popularity,u.nickname,u.username from ms_gift_popularity as g INNER JOIN ms_user as u on u.id= g.user_id  WHERE g.id > 0 ';

        if (is_array($args) && isset($args['filters'])) {
            if (isset($args['filters']['userid'])) {
                $sql .= ' and g.user_id like "%' .$args['filters']['userid']. '%"';
            }
            if (isset($args['filters']['username'])) {
                $sql .= ' and u.username like "%' .$args['filters']['username']. '%"';
            }
            if (isset($args['filters']['nickname'])) {
                $sql .= ' and u.nickname like "%' .$args['filters']['nickname']. '%"';
            }
        }

        $sql .= ' group by g.user_id ) t';

        $data['total'] = $db->search(str_replace('###', 'count(1) as cnt', $sql))[0]['cnt'];

        $sql .= ' order by popularity desc ';

        $sql .= ' limit ' . ($page-1) * $pagesize . ', '. $pagesize;
        $data['data']  = $db->search(str_replace('###', '*', $sql));
        $data['pageCount'] = floor($data['total']/$pagesize);
        $data['sql']= $sql;
        return $data;
    }
    public function sortlists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $enddate = $args['filters']['startdate']+60*60*24-1;
        $sql = 'select u.id user_id,u.username,u.nickname,sum(g.gold*g.count) popularity from ms_gift_give_log g LEFT JOIN ms_user u ON g.user_id = u.id where g.c_time between '.$args['filters']['startdate'].' and '.$enddate;
        if (is_array($args) && isset($args['filters'])) {
            if (isset($args['filters']['userid'])) {
                $sql .= ' and g.user_id like "%' .$args['filters']['userid']. '%"';
            }
            if (isset($args['filters']['username'])) {
                $sql .= ' and u.username like "%' .$args['filters']['username']. '%"';
            }
            if (isset($args['filters']['nickname'])) {
                $sql .= ' and u.nickname like "%' .$args['filters']['nickname']. '%"';
            }
        }

        $sql .= ' group by g.user_id';
        

        $sql .= ' order by c_time desc ';

        $sql .= ' limit ' . ($page-1) * $pagesize . ', '. $pagesize;
        $result = $db->search($sql);
        $data['total'] = count($result);
        $getsql = 'select IFNULL(sum(g.gold*g.count),0) getgold from ms_gift_give_log g where g.to_user_id = ### and g.c_time between '.$args['filters']['startdate'].' and '.$enddate;
        foreach ($result as &$v){
            $v['getgold'] = $db->search(str_replace('###', $v['user_id'], $getsql))[0]['getgold'];
        }
        $data['data']  = $result;

        $data['pageCount'] = floor($data['total']/$pagesize);
        return $data;
    }

    public function setData($arr){
        $db = $this->DB();

        $old = $db->search('select popularity from ms_gift_popularity WHERE   user_id = '.$arr['user_id'])[0]['popularity'];

        $sql = 'update ms_gift_popularity set popularity = '.($arr['popularity']+$old).' where user_id = '.$arr['user_id'];
        $result = $db->exec($sql);
        return [1];
    }

}
