<?php
namespace Player;
class GiftlogModel extends \BaseModel {
/**
     * 获取列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_gift_give_log')
            ->joinLeft('ms_user as ui', 'ui.id = main_table.user_id')
            ->joinLeft('ms_user as us', 'us.id = main_table.to_user_id')
            ->joinLeft('ms_gift as gf', 'gf.g_type = main_table.g_type')
            ->select('main_table.id')
            ->select('main_table.user_id')
            ->select('ui.nickname')
            ->select('main_table.to_user_id')
            ->select('us.nickname as toname')
            ->select('gf.name as g_type')
            ->select('main_table.gold')
            ->select('main_table.count')
            ->select('main_table.c_time');

        if (is_array($args) && isset($args['filters'])) {
            if (isset($args['filters']['userid'])) {
                $select->Where('main_table.user_id', ['like' => '"%' . $args['filters']['userid'] . '%"']);
                $select->orWhere('main_table.to_user_id', ['like' => '"%' . $args['filters']['userid'] . '%"']);
//                $select->orWhereLike('main_table.user_id', "%".$args['filters']['userid']."%");
//                $select->orWhereLike('main_table.to_user_id', "%".$args['filters']['userid']."%");
            }
            if (isset($args['filters']['nickname'])) {
                $select->where('ui.nickname', ['like' => '"%' . $args['filters']['nickname'] . '%"']);
            }
            if (isset($args['filters']['to_user_id'])) {
                $select->where('main_table.to_user_id', $args['filters']['to_user_id'] );
            }
            if (isset($args['filters']['user_id'])) {
                $select->where('main_table.user_id', $args['filters']['user_id'] );
            }
            if (isset($args['filters']['start_time'])) {
                $select->where('main_table.c_time', $args['filters']['start_time'],'>=');
            }
            if (isset($args['filters']['end_time'])) {
                $select->where('main_table.c_time', $args['filters']['end_time'],'<');
            }
        }

        $select->order('main_table.c_time', 'desc');

        $data = $db->fetchAllPage($select, $page, $pagesize);
        $arr = $data['list']->toArray();
        foreach ($arr as &$item){
            $item['gold'] = $item['gold']*$item['count'];
        }
        $data['list'] = $arr;

        return $data;
    }
}