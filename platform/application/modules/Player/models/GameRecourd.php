<?php

namespace Player;

class GameRecourdModel extends \BaseModel {

    /**
     * 获取游戏记录数据
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
    public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {

        $db = $this->DB_GameLog();
        $select = $db->newSelect('ms_user_log')
            ->joinLeft('ms_db_main.ms_user mu', 'mu.id=main_table.user_id')
            ->joinLeft('ms_db_main.ms_gm_gametype ggt', 'ggt.id = main_table.game_id')
            ->joinLeft('ms_db_main.ms_gm_gamemode ggm', 'ggm.id = main_table.game_mode')
            ->select('main_table.id')
            ->select('main_table.user_id')
            ->select('mu.username')
            ->select('mu.nickname')
            ->select('mu.wx_unionid')
            ->select('ggt.game_name')
            ->select('ggm.mode_name')
            ->select('table_id')
            ->select('chair_id')
            ->select('win_gold')
            ->select('duration')
            ->select('gold')
            ->select('point')
            ->select('FROM_UNIXTIME(created_date, \'%Y-%m-%d %H:%i:%s\')as created_date' , true);

        $select->isnotnull('mu.username');
        if(isset($args['filters']['game_id'])){
            $select->where('main_table.game_id', $args['filters']['game_id']);
        }
        if(isset($args['filters']['userid'])){
            $select->whereLike('main_table.user_id', $args['filters']['userid']);
        }
        if(isset($args['filters']['user_id'])){
            $select->whereLike('main_table.user_id', $args['filters']['user_id']);
        }
        if(isset($args['filters']['wx_unionid'])){
            $select->whereLike('mu.wx_unionid', $args['filters']['wx_unionid']);
        }
        if(isset($args['filters']['username'])){
            $select->whereLike('mu.username', $args['filters']['username']);
        }
        if(isset($args['filters']['nickname'])){
            $select->whereLike('mu.nickname', $args['filters']['nickname']);
        }
        if(isset($args['filters']['start_time'])){
            $select->where('main_table.created_date', $args['filters']['start_time'],'>=');
        }
        if(isset($args['filters']['end_time'])){
            $select->where('main_table.created_date', $args['filters']['end_time'],'<');
        }
        if(isset($args['filters']['gold1'])){
            $select->where('main_table.win_gold', $args['filters']['gold1'],'>=');
        }
        if(isset($args['filters']['gold2'])){
            $select->where('main_table.win_gold', $args['filters']['gold2'],'<=');
        }
        if(isset($args['filters']['point1'])){
            $select->where('main_table.point', $args['filters']['point1'],'>=');
        }
        if(isset($args['filters']['point2'])){
            $select->where('main_table.point', $args['filters']['point2'],'<=');
        }
        if(isset($args['order']['by'])){
            $select->order('main_table.'.$args['order']['by'],$args['order']['type']);
        }

        $data = $db->fetchAllPage($select, $page, $pagesize);

        $arr = $data['list']->toArray();
        foreach ($arr as &$item){
            $item['point'] = $item['point']?$item['point'].'%':'0%';
            if($item['duration']<60){
                $item['duration'] = $item['duration'].'秒';
            }elseif ($item['duration']<3600){
                $m = floor($item['duration']/60);
                $i = $item['duration']%60;
                $item['duration'] = $m.'分'.$i.'秒';
            }else{
                $h = floor($item['duration']/3600);
                $m = floor(($item['duration']%3600)/60);
                $i = ($item['duration']%3600)%60;
                $item['duration'] = $h.'小时'.$m.'分'.$i.'秒';
            }

        }
        $data['list']= $arr;
//
//        $data['total'] = $total;
//        $data['pageCount'] =$pages;
        return $data;
    }

}
