<?php

namespace Player;

class IntegralModel extends \BaseModel {

    /**
     * 获取游戏记录数据
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
    public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        //搜索日志数据
        $gamelog_db = $this->DB_GameLog();
        $select = $gamelog_db->newSelect('ms_user_estate')
                ->joinLeft('ms_user_log as ul', 'ul.user_id = main_table.user_id')
                ->select('main_table.user_id')
                ->select('main_table.gold')
                ->select('count(ul.id) as total', true)
                ->select('count(IF(ul.win_gold > 0, 1, null)) as win_times', true)
                ->select('count(IF(ul.win_gold = 0, 1, null)) as deuce_times', true)
                ->select('count(IF(ul.win_gold < 0, 1, null)) as lose_times', true)
                ->select('IFNULL(sum(ul.duration), 0) as play_duration', true)
                ->select('main_table.update_date')
                ->group('main_table.user_id')
                ->order('update_date', 'desc');

        if (is_array($args)) {
            //wehre
            if (isset($args['filters']['user_id'])) {
                $select->where('main_table.user_id', $args['filters']['user_id']);
            }
            if (isset($args['filters']['gold_above'])) {
                $select->where('main_table.gold', $args['filters']['gold_above'], '>=');
            }
            if (isset($args['filters']['gold_below'])) {
                $select->where('main_table.gold', $args['filters']['gold_below'], '<=');
            }
        }

        $data = $gamelog_db->fetchAllPage($select, $page, $pagesize);
        
        //获取用户帐号和用户昵称
        $userModel = new \Player\UserModel();
        $collection = $userModel->getUserData($data['list']->getColumnValue('user_id'), ['username', 'nickname']);
        if($collection){
            $data['list']->flip('user_id');
            $collection->each(function($userTable, $user_id) use($data){
                $item = $data['list']->getItem($user_id);
                if($item){
                    $item->setData('username' , $userTable->getData('username'));
                    $item->setData('nickname' , $userTable->getData('nickname'));
                }
            });
        }
        $data['list'] = $data['list']->toArray();
        return $data;
    }

}
