<?php

class IntegralController extends BaseController {

    /**
     * 加载用户数据列表
     */
    public function listAction() {
        $request = $this->input()->request();
        $args = array();

        //搜索条件
        $query = $request['query'] ?? false;
        $query_type = $request['query_type'] ?? false;
        if(!empty($query) && !empty($query_type)){
            switch ($query_type){
                case 'user_id':
                    $args['filters']['user_id'] = $query;
                    break;
                case 'user_name':
                    $args['filters']['user_name'] = $query;
                    break;
                case 'integral_above':
                    $args['filters']['integral_above'] = $query;
                    break;
                case 'integral_below':
                    $args['filters']['integral_below'] = $query;
                    break;
            }
        }

        //查询页码
        $page = $request['pagenum'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;

        //查询数据
        $model = new Player\IntegralModel();
        $data = $model->lists($page, $pagesize, $args);

        $data['data'] = $data['list'];
        unset($data['list']);

        return $this->succ($data, false);
    }

}
