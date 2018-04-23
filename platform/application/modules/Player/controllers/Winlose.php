<?php

/**
 * 游戏记录
 */
class WinloseController extends BaseController {

    public function listAction() {
        $input = $this->input();
        $request = $input->request();
        $args = array();

        //搜索条件
        !empty($request['query']) && ($args['filters']['username'] = $request['query']);
        !empty($request['game_id']) && ($args['filters']['game_id'] = $request['game_id']);
        !empty($request['start_time']) && ($args['filters']['start_time'] = strtotime($request['start_time']));
        !empty($request['end_time']) && ($args['filters']['end_time'] = strtotime($request['end_time'])); 

        //查询页码
        $page = $input->post_get('pagenum') ?? 1;
        $pagesize = $input->post_get('pagesize') ?? 10;

        //查询数据
        switch ($args['filters']['game_id']){
            case 'all':
                $model = new Player\WinloseModel();
            case '1':
            $model = new Player\WinloseMJModel();
            case '2':
                $model = new Player\WinloseNNModel();
        }

        $data = $model->lists($page, $pagesize, $args);

        $ret_list = [];
        foreach ($data['list'] as $item) {
            isset($item['duration']) && $item['duration'] = int2duration($item['duration']);
            isset($item['created_date']) && $item['created_date'] = date('Y-m-d H:i:s', $item['created_date']);
            $new = array_change_keys($item, array(
                'user_id' => 'user_id',
                'username' => 'username',
                'nickname' => 'nickname',
                'win_num' => 'win_num',
                'lose_num' => 'lose_num',
                'duration' => 'duration',
            ));
            $ret_list[] = $new;
        }

        unset($data['list']);
        $data['data'] = $ret_list;

        return $this->succ($data, false);
    }

}
