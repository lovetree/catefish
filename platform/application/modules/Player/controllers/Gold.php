<?php

class GoldController extends BaseController {

    /**
     * 加载用户数据列表
     */
    public function listAction() {
        $request = $this->input()->request();
        $args = array();

        //搜索条件
        $query = $request['query'] ?? false;
        $query_type = $request['query_type'] ?? false;
        $sortBy = $request['sortby'];
        $sortType = $request['sorttype'];
        if(!empty($query) && !empty($query_type)){
            switch ($query_type){
                case 'user_id':
                    $args['filters']['user_id'] = $query;
                    break;
                case 'gold_above':
                    $args['filters']['gold_above'] = $query;
                    break;
                case 'gold_below':
                    $args['filters']['gold_below'] = $query;
                    break;
                case 'wx_unionid':
                    $args['filters']['wx_unionid'] = $query;
                    break;

            }
        }

        if($sortBy){
            $args['order'][$sortBy] = $sortType;
        }

        //查询页码
        $page = $request['pagenum'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;

        //查询数据
        $model = new Player\GoldModel();
        $data = $model->lists($page, $pagesize, $args);

//        return $this->succ(array('total'=>1,'data'=>[$data],'pageCount'=>1),false);
//        exit;
        $data['data'] = $data['list']->toArray();

        unset($data['list']);

        return $this->succ($data, false);
    }

}
