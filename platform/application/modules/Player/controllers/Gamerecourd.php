<?php

/**
 * 游戏记录
 */
class GamerecourdController extends BaseController {

    public function listAction() {

        $request = $this->input()->request();
        $args = array();

        //搜索条件
        if(!empty($request['query_type'])&&!empty($request['query'])){
            switch ($request['query_type']){
                case 'username':
                    $args['filters']['username'] = '%'.$request['query'].'%';
                    break;
                case 'userid':
                    $args['filters']['userid'] = '%'.$request['query'].'%';
                    break;
                case 'nickname':
                    $args['filters']['nickname'] = '%'.$request['query'].'%';
                    break;
                case 'gold1':
                    $args['filters']['gold1'] = $request['gold1'];
                    break;
                case 'gold2':
                    $args['filters']['gold2'] = $request['gold2'];
                    break;
                case 'point1':
                    $args['filters']['point1'] = $request['point1'];
                    break;
                case 'point2':
                    $args['filters']['point2'] = $request['point2'];
                    break;
                case 'wx_unionid':
                    $args['filters']['wx_unionid'] = $request['wx_unionid'];
                    break;


            }
        }
        !empty($request['user_id']) && ($args['filters']['user_id'] = $request['user_id']);
        !empty($request['gold1']) && ($args['filters']['gold1'] = $request['gold1']);
        !empty($request['gold2']) && ($args['filters']['gold2'] = $request['gold2']);
        !empty($request['point1']) && ($args['filters']['point1'] = $request['point1']);
        !empty($request['point2']) && ($args['filters']['point2'] = $request['point2']);
        !empty($request['game_id']) && ($args['filters']['game_id'] = $request['game_id']);
        !empty($request['start_time']) && ($args['filters']['start_time'] = strtotime($request['start_time']));
        !empty($request['end_time']) && ($args['filters']['end_time'] = strtotime($request['end_time']));
        if(!empty($request['sortby'])&&$request['sortby']&&$request['sortby']!='username'){

            $args['order']['by'] = $request['sortby'];
            $args['order']['type'] = $request['sorttype'];

        }
        //查询页码
        $page     = $request['pagenum'] ?? 1; //当前页
        $pagesize = $request['pagesize'] ?? 10; //显示条目

        //查询数据
        $model = new Player\GameRecourdModel();
//        return $this->succ(['total'=>0,'list'=>[1],'pageCount'=>1], false);
        $data = $model->lists($page, $pagesize, $args);

        $data['data'] = $data['list']?$data['list']:[];
        unset($data['list']);

        return $this->succ($data, false);
    }

}
