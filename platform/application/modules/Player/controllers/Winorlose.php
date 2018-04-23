<?php
class WinorloseController extends BaseController {
    /*** 查询列表***/
    public function listAction() {

        $request = $this->input()->request(); //接收参数
        $args  = array();

        $query      = $request['query']; //搜索值
        $query_type = $request['query_type']; //搜索字段


        $args['order']['type'] = $request['sorttype'];
        if (!is_null($query) && strlen($query) != 0) {
            if ($query_type == 'userid') {
                $args['filters']['userid'] = $query;
            }
            if ($query_type == 'username') {
                $args['filters']['username'] = $query;
            }
            if ($query_type == 'nickname') {
                $args['filters']['nickname'] = $query;
            }
        }
        !empty($request['game_id']) && ($args['filters']['game_id'] = $request['game_id']);
        !empty($request['start_time']) && ($args['filters']['start_time'] = strtotime($request['start_time']));
        !empty($request['end_time']) && ($args['filters']['end_time'] = strtotime($request['end_time']));
        $page     = $request['pagenum'] ?? 1; //当前页
        $pagesize = $request['pagesize'] ?? 10; //显示条目

        $user = new Player\WinorloseModel(); //调用模型获取数据
        $data = $user->lists($page, $pagesize, $args);

        //反馈数据
        return $this->succ($data, false);
    }

}