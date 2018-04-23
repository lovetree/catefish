<?php

namespace Player;

class BusylineModel extends \BaseModel {

    /**
     * 获取用户在线数据
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
    public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB_GameLog();
        $select = $db->newSelect('ms_user_online')
                ->joinLeft('ms_db_main.ms_gm_gamemode as gm', 'gm.id = main_table.game_mode')
                ->joinLeft('ms_db_main.ms_gm_gametype as gt', 'gt.id = main_table.game_id')
                ->joinLeft('ms_db_main.ms_user ms', 'ms.id = main_table.user_id')
                ->select('gm.mode_name')
                ->select('gt.game_name')
                ->select('ms.username')
                ->select('ms.nickname')
                ->select('main_table.game_mode')
                ->select('main_table.game_id')
                ->select('main_table.user_id')
                ->select('main_table.ip')
                ->select('main_table.gold')
                ->select('main_table.credit')
                ->select('main_table.id')
                ->select('main_table.update_time')
                ->order('update_time', 'desc');

        if (is_array($args)) {

            if (isset($args['filters']['user_id'])) {
                $select->where('main_table.user_id', $args['filters']['user_id']);
            }

            if (isset($args['filters']['game_id'])) {
                $select->where('main_table.game_id', $args['filters']['game_id']);
            }
            if (isset($args['filters']['game_mode'])) {
                $select->where('main_table.game_mode', $args['filters']['game_mode']);
            }
        }

        $data = $db->fetchAllPage($select, $page, $pagesize);

//        if ($data['list']->count()) {
//            //获取用户帐号
//            $userModel = new \Player\UserModel();
//            $collection = $userModel->getUserData($data['list']->getColumnValue('user_id'), ['username', 'nickname']);
//            if ($collection) {
//                $data['list']->each(function($item) use($collection) {
//                    $userTable = $collection->getItem($item->getData('user_id'));
//                    if ($userTable) {
//                        $item->setData('username', $userTable->getData('username'));
//                        $item->setData('nickname', $userTable->getData('nickname'));
//                    }
//                });
//            }
//        }


        $data['list'] = $data['list']->toArray();
        return $data;
    }

}
