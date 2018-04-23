<?php
class PopularityController extends BaseController {
    /*** 查询列表***/
    public function listAction() {

        $request = $this->input()->request(); //接收参数
        $args  = array();

        $query      = $request['query']; //搜索值
        $query_type = $request['query_type']; //搜索字段


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

        $page     = $request['pagenum'] ?? 1; //当前页
        $pagesize = $request['pagesize'] ?? 10; //显示条目

        $user = new Player\PopularityModel(); //调用模型获取数据

        $data = $user->lists($page, $pagesize, $args);
        //反馈数据
        return $this->succ($data, false);
    }
    public function sortlistAction() {

        $request = $this->input()->request(); //接收参数
        $args  = array();

        $query      = $request['query']; //搜索值
        $query_type = $request['query_type']; //搜索字段
        $startdate = $request['startdate']; //日期

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
        $args['filters']['startdate'] = empty($startdate) ? strtotime('today') : strtotime($startdate);
        $page     = $request['pagenum'] ?? 1; //当前页
        $pagesize = $request['pagesize'] ?? 10; //显示条目

        $user = new Player\PopularityModel(); //调用模型获取数据

        $data = $user->sortlists($page, $pagesize, $args);
        //反馈数据
        return $this->succ($data, false);
    }
    public function setpopuAction(){
        $request = $this->input()->request(); //接收参数
        if(!$request['id']){
            $this->failed('丢失主键参数');
        }
        $args  = array(
            'user_id'=>$request['id'],
            'popularity' =>$request['popularity']
        );
        $obj = new Player\PopularityModel(); //调用模型获取数据
        $result = $obj->setData($args);

        return $this->succ($result);
    }

}