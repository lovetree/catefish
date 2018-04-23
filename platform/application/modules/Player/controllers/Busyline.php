<?php

/**
 * 卡线管理
 */
class BusyLineController extends BaseController {

    /**
     * 无搜索条件时，不提供列表展示所有数据
     * 支持的搜索：用户ID || 游戏帐号 + 游戏ID + 游戏类型
     * @return type
     */
    public function listAction() {
        $request = $this->input()->request();
        $args = array();

        //搜索条件
        if(isset($request['game_id']) && !empty($request['game_id'])){
            $args['filters']['game_id'] = $request['game_id'];
        }

        if(isset($request['game_mode']) && !empty($request['game_mode'])){
            $args['filters']['game_mode'] = $request['game_mode'];
        }

        if(isset($request['query']) && !empty($request['query'])){
            $args['filters']['user_id'] = $request['query'];
        }

        $page = $request['pagenum'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;

        $model = new Player\BusylineModel();
        $data = $model->lists($page, $pagesize, $args);
        $list = $data['list'];
        unset($data['list']);
        $ret_list = [];
        foreach ($list as $item) {
            $new = array_change_keys($item, array(
                'id' => 'id',
                'user_id'    => 'user_id',
                'username'   => 'username',
                'nickname' => 'nickname',
                'ip'   => 'ip',
                'update_time' =>'update_time',
                'mode_name' => 'mode_name',
                'game_name'   => 'game_name',
                'credit'  => 'credit',
                'gold' => 'gold',
            ), true);
            $ret_list[] = $new;
        }
        $data['data'] = $ret_list;
        return $this->succ($data, false);
    }

}
