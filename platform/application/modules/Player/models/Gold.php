<?php

namespace Player;

class GoldModel extends \BaseModel {

    /**
     * 获取游戏记录数据
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
    public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        //搜索日志数据
        $db =  $this->DB();

        $select = $db->newSelect('ms_user_estate')
                ->joinLeft('ms_safe as sf', 'sf.user_id = main_table.user_id')
                ->joinLeft('ms_db_main.ms_user ui', 'main_table.user_id = ui.id')
                ->select('main_table.user_id')
                ->select('main_table.gold')
                ->select('main_table.credit')
                ->select('ui.wx_unionid')
                ->select('ui.nickname')
                ->select('ui.username')
                ->select('win_num')
               ->select('lose_num')
                ->select('sf.safe_gold as sgold')
                ->select('main_table.update_date');

            $select->isnotnull('ui.username','is not null');

        if (is_array($args)) {
            if (isset($args['filters']['user_id'])) {
                $select->where('main_table.user_id', $args['filters']['user_id']);
            }
            if (isset($args['filters']['gold_above'])) {
                $select->where('main_table.gold', $args['filters']['gold_above'], '>=');
            }
            if (isset($args['filters']['gold_below'])) {
                $select->where('main_table.gold', $args['filters']['gold_below'], '<=');
            }
            if (isset($args['filters']['wx_unionid'])) {
                $select->where('ui.wx_unionid', $args['filters']['wx_unionid']);
            }



            if(isset($args['order'])){
                foreach ($args['order'] as $key=>$val){
                    if($key == 'user_id') continue;
                    if($key=='sgold') $select->order('sf.safe_gold', $val);
                    else $select->order('main_table.'.$key, $val);
                }
            }else{
                $select->order('main_table.gold', 'desc');
            }
        }
        $result = $db->fetchAllPage($select, $page, $pagesize);
        $result['sql'] = $select->toString();
        return $result;
        
        //获取用户帐号和用户昵称
//        $userModel = new \Player\UserModel();
//        $collection = $userModel->getUserData($data['list']->getColumnValue('user_id'), ['username', 'nickname']);
//        if($collection){
//            $data['list']->flip('user_id');
//            $collection->each(function($userTable, $user_id) use($data){
//                $item = $data['list']->getItem($user_id);
//                if($item){
//                    $item->setData('username' , $userTable->getData('username'));
//                    $item->setData('nickname' , $userTable->getData('nickname'));
//                }
//            });
//        }
//        $data['list'] = $data['list']->toArray();
    }

}
