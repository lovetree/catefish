<?php
class GiftlogController extends BaseController {
/*** 查询列表***/
    public function listAction() {

        $request = $this->input()->request(); //接收参数
        $args  = array();

        $query      = $request['query']; //搜索值
        $query_type = $request['query_type']; //搜索字段

        $page = $request['pagenum'] ?? 1; //当前页
        $pagesize = $request['pagesize'] ?? 10; //显示条目

        if (!is_null($query) && strlen($query) != 0) {
            if ($query_type == 'userid') {
                $args['filters']['userid'] = $query;
            }
            if ($query_type == 'nickname') {
                $args['filters']['nickname'] = $query;
            }
        }
        !empty($request['start_time']) && ($args['filters']['start_time'] = strtotime($request['start_time']));
        !empty($request['end_time']) && ($args['filters']['end_time'] = strtotime($request['end_time']));
        !empty($request['user_id']) && ($args['filters']['user_id'] = $request['user_id']);
        !empty($request['to_user_id']) && ($args['filters']['to_user_id'] = $request['to_user_id']);
        $user = new Player\GiftlogModel(); //调用模型获取数据
        $data = $user->lists($page, $pagesize, $args);
        //数据重构
        $list = $data['list'];
        unset($data['list']);
        $ret_list = [];
        foreach ($list as $item) {
            $new = array_change_keys($item, [
                'id' => 'id',
                'user_id'    => 'user_id',
                'nickname'   => 'nickname',
                'to_user_id' => 'to_user_id',
                'toname'   => 'toname',
                'g_type' => 'g_type',
                'gold'   => 'gold',
                'count'  => 'count',
                'c_time' => 'c_time',
            ], false);
            $new['c_time'] = date('Y-m-d H:i', $new['c_time']);
            $ret_list[] = $new;
        }
        $data['data'] = $ret_list;
        //反馈数据
        return $this->succ($data, false);
    }

}