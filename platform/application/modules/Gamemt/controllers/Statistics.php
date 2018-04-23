<?php

class StatisticsController extends BaseController {
    
    private $model = null;
    public function init() {
        if (!$this->model){
            $this->model = new \Gamemt\StatisticsModel();
        }
    }
    
    /**
     * 用户统计
     */
    public function userAction() {
        $t = isset($_GET['type']) ? trim($_GET['type']) : '';
        $data = $this->model->user($t);
        $this->getView()->assign('pHidden', TRUE);
        $this->getView()->assign('rHidden', TRUE);
        $this->getView()->assign('type', $t);
        $this->getView()->assign('line_data', $data['line_data']);
        $this->getView()->assign('step', $data['step']);
        $this->getView()->assign('totalArr', $data['totalArr']);
        $this->getView()->assign('totalList', $data['totalList']);
        $this->getView()->assign('h_title', "用户统计");
        $this->display("user");
    }
    /**
     * 活跃统计
     */
    public function activeAction(){
        $t = isset($_GET['type']) ? trim($_GET['type']) : '';
        $data = $this->model->active($t);

        $this->getView()->assign('pHidden', TRUE);
        $this->getView()->assign('rHidden', TRUE);
        $this->getView()->assign('type', $t);
        $this->getView()->assign('line_data', $data['line_data']);
        $this->getView()->assign('step', $data['step']);
        $this->getView()->assign('totalArr', $data['totalArr']);
        $this->getView()->assign('totalList', $data['totalList']);
        $this->getView()->assign('h_title', "活跃统计");
        $this->display("active");

    }

    /**
     * 存量统计
     */
    public function estateAction(){
        $t = isset($_GET['type']) ? trim($_GET['type']) : '';

        $data = $this->model->estateStat($t);
//        echo  '<pre>';
//        var_dump($data['totalList']);
//        exit;

        $this->getView()->assign('pHidden', TRUE);
        $this->getView()->assign('rHidden', TRUE);
        $this->getView()->assign('type', $t);
        $this->getView()->assign('line_data', $data['line_data']);
        $this->getView()->assign('step', $data['step']);
        $this->getView()->assign('totalArr', $data['totalArr']);
        $this->getView()->assign('totalList', $data['totalList']);
        $this->getView()->assign('h_title', "统计");
        $this->display("estate");

    }

    public function countAction(){
        $request = $this->input()->request(); 
        $args  = array();

        $page = $request['pagenum'] ?? 1; 
        $pagesize = $request['pagesize'] ?? 10; 

        !empty($request['start_time']) && ($args['filters']['start_time'] = strtotime($request['start_time']));
        !empty($request['end_time']) && ($args['filters']['end_time'] = strtotime($request['end_time']));
        $data = $this->model->countGold($page, $pagesize, $args);
        unset($data['list']);
        $new = [];
        foreach ($data['data'] as $k => $v) {

            $new[] = $v;
            $new[$k]['time']=date('Y-m-d',$v['create_time']);
            $new[$k]['count'] = $v['credit_gold']+$v['admin_gold']+$v['sys_gold']+$v['sys_produce']+$v['gift_gold']+$v['renqi_gold']+$v['other']+$v['sys_recover'];
            $new[$k]['all'] = $new[$k]['count']+$v['pre_gold'];
        }
        $data['data'] = $new;
        //反馈数据
        return $this->succ($data, false);
    }
    public function getGoldType($id){
        $array = ['捕鱼','钻石兑换','系统赠送','后台改动','签到','任务','在线奖励','人气兑换','其他','赠送礼物','存入保险箱','保险箱取出','21点','德州','牛牛'];
        return !empty($array[$id-1]) ? $array[$id-1] : $id;
    }
    public function streamAction(){
        $request = $this->input()->request();
        $args  = array();

        $page = $request['pagenum'] ?? 1; 
        $pagesize = $request['pagesize'] ?? 10; 
        !empty($request['query']) && ($args['filters']['query'] = $request['query']);
        !empty($request['type']) && ($args['filters']['type'] = $request['type']);
        !empty($request['user_id']) && ($args['filters']['user_id'] = $request['user_id']);
        !empty($request['start_time']) && ($args['filters']['start_time'] = strtotime($request['start_time']));
        !empty($request['end_time']) && ($args['filters']['end_time'] = strtotime($request['end_time']));
        $data = $this->model->goldStrem($page, $pagesize, $args);
        unset($data['list']);
        $new = [];
        foreach ($data['data'] as $k => $v) {

            $new[] = $v;
            $new[$k]['time']=date('Y-m-d H:i:s',$v['create_time']);
            $new[$k]['type_name'] = $this->getGoldType($v['type']);
        }
        $data['data'] = $new;
        //反馈数据
        return $this->succ($data, false);
    }
    public function safelogAction(){
        $request = $this->input()->request();
        $args  = array();

        $page = $request['pagenum'] ?? 1; 
        $pagesize = $request['pagesize'] ?? 10; 
        !empty($request['query']) && ($args['filters']['query'] = $request['query']);
        !empty($request['type']) && ($args['filters']['type'] = $request['type']);
        !empty($request['user_id']) && ($args['filters']['user_id'] = $request['user_id']);
        !empty($request['start_time']) && ($args['filters']['start_time'] = strtotime($request['start_time']));
        !empty($request['end_time']) && ($args['filters']['end_time'] = strtotime($request['end_time']));
        $data = $this->model->safelog($page, $pagesize, $args);
        unset($data['list']);
        $new = [];
        foreach ($data['data'] as $k => $v) {

            $new[] = $v;
            $new[$k]['time']=date('Y-m-d H:i:s',$v['c_time']);
            switch ($v['type']) {
                case '1':
                    $new[$k]['type_name']="存入";
                    break;
                case '2':
                    $new[$k]['type_name']="取出";
                    break;
                case '4':
                    $new[$k]['type_name']="后台改动";
                    break;
                default:
                    $new[$k]['type_name']=$v['type'];
                    break;
            }
        }
        $data['data'] = $new;
        //反馈数据
        return $this->succ($data, false);

    }

    public function creditlogAction(){
        $request = $this->input()->request();
        $args  = array();

        $page = $request['pagenum'] ?? 1; 
        $pagesize = $request['pagesize'] ?? 10; 
        !empty($request['query']) && ($args['filters']['query'] = $request['query']);
        !empty($request['type']) && ($args['filters']['type'] = $request['type']);
        !empty($request['user_id']) && ($args['filters']['user_id'] = $request['user_id']);
        !empty($request['start_time']) && ($args['filters']['start_time'] = strtotime($request['start_time']));
        !empty($request['end_time']) && ($args['filters']['end_time'] = strtotime($request['end_time']));
        $data = $this->model->creditlog($page, $pagesize, $args);
        unset($data['list']);
        $new = [];
        foreach ($data['data'] as $k => $v) {

            $new[] = $v;
            $new[$k]['time']=date('Y-m-d H:i:s',$v['create_time']);
            switch ($v['type']) {
                case '1':
                    $new[$k]['type_name']="充值";
                    break;
                case '2':
                    $new[$k]['type_name']="兑换金币";
                    break;
                case '3':
                    $new[$k]['type_name']="兑换绿宝石";
                    break;
                case '4':
                    $new[$k]['type_name']="后台改动";
                    break;
                case '5':
                    $new[$k]['type_name']="钻石抢装";
                    break;
                default:
                    $new[$k]['type_name']=$v['type'];
                    break;
            }
        }
        $data['data'] = $new;
        //反馈数据
        return $this->succ($data, false);    
    }
    public function renqiAction(){
        $request = $this->input()->request();
        $args = array();
        $page = $request['pagenum'] ?? 1; 
        $pagesize = $request['pagesize'] ?? 10; 
        !empty($request['query']) && ($args['filters']['query'] = $request['query']);
        !empty($request['user_id']) && ($args['filters']['user_id'] = $request['user_id']);
        !empty($request['start_time']) && ($args['filters']['start_time'] = strtotime($request['start_time']));
        !empty($request['end_time']) && ($args['filters']['end_time'] = strtotime($request['end_time']));
        $data = $this->model->renqi($page, $pagesize, $args);
        unset($data['list']);
        $new = [];
        foreach ($data['data'] as $k => $v) {

            $new[] = $v;
            $new[$k]['time']=date('Y-m-d H:i:s',$v['create_time']);
        }
        $data['data'] = $new;
        return $this->succ($data, false);

    }
    public function emeraldAction(){
        $request = $this->input()->request();
        $args  = array();

        $page = $request['pagenum'] ?? 1; 
        $pagesize = $request['pagesize'] ?? 10; 
        !empty($request['query']) && ($args['filters']['query'] = $request['query']);
        !empty($request['type']) && ($args['filters']['type'] = $request['type']);
        !empty($request['user_id']) && ($args['filters']['user_id'] = $request['user_id']);
        !empty($request['start_time']) && ($args['filters']['start_time'] = strtotime($request['start_time']));
        !empty($request['end_time']) && ($args['filters']['end_time'] = strtotime($request['end_time']));
        $data = $this->model->emeraldlog($page, $pagesize, $args);
        unset($data['list']);
        $new = [];
        foreach ($data['data'] as $k => $v) {

            $new[] = $v;
            $new[$k]['time']=date('Y-m-d H:i:s',$v['create_time']);
            switch ($v['type']) {
                case '1':
                    $new[$k]['type_name']="钻石兑换";
                    break;
                case '2':
                    $new[$k]['type_name']="炮台升级";
                    break;
                case '3':
                    $new[$k]['type_name']="新人赠送";
                    break;
                case '4':
                    $new[$k]['type_name']="后台改动";
                    break;
                case '5':
                    $new[$k]['type_name']="捕鱼";
                    break;
                case '6':
                    $new[$k]['type_name']="倍数解锁";
                    break;
                case '7':
                    $new[$k]['type_name']="双管炮解锁";
                    break;
                case '8':
                    $new[$k]['type_name']="自动挂机";
                    break;
                default:
                    $new[$k]['type_name']=$v['type'];
                    break;
            }
        }
        $data['data'] = $new;
        //反馈数据
        return $this->succ($data, false);    
    }
    public function loginlogAction(){
        $request = $this->input()->request();
        $args = array();
        $page = $request['pagenum'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;
        !empty($request['query']) && ($args['filters']['query'] = $request['query']);
        !empty($request['user_id']) && ($args['filters']['user_id'] = $request['user_id']);
        !empty($request['start_time']) && ($args['filters']['start_time'] = strtotime($request['start_time']));
        !empty($request['end_time']) && ($args['filters']['end_time'] = strtotime($request['end_time']));
        $data = $this->model->loginlog($page, $pagesize, $args);
        unset($data['list']);
        $new = [];
        foreach ($data['data'] as $k => $v) {

            $new[] = $v;
            $new[$k]['time']=date('Y-m-d H:i:s',$v['create_time']);
        }
        $data['data'] = $new;
        return $this->succ($data, false);
    }

    public  function pointExchangeLogAction(){
        $request = $this->input()->request();
        $args  = array();

        $page = $request['pagenum'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;
        !empty($request['query']) && ($args['filters']['query'] = $request['query']);
        !empty($request['exchtype']) && ($args['filters']['exchtype'] = $request['exchtype']);
        !empty($request['user_id']) && ($args['filters']['userid'] = $request['userid']);
        !empty($request['start_time']) && ($args['filters']['start_time'] = strtotime($request['start_time']));
        !empty($request['end_time']) && ($args['filters']['end_time'] = strtotime($request['end_time']));
        $data = $this->model->pointlog($page, $pagesize, $args);

        //数据重构
        $list = $data['list'];
        unset($data['list']);
        $ret_list = [];
        //根据时间区间确定竞技场当前的状态
        foreach ($list as $item) {
            isset($item['exchangetime']) && ($item['exchangetime'] = date('Y-m-d H:i:s', $item['exchangetime']));
            if(isset($item['updatetime'])  && $item['updatetime'] !=0)
            {
                $item['updatetime'] = date('Y-m-d H:i:s', $item['updatetime']);
            }
            else
            {
                $item['updatetime'] ='';
            }
            $new = array_change_keys($item, array(
                'id' => 'id'
            ), true);
            $ret_list[] = $new;
        }
        $data['data'] = $ret_list;
        //反馈数据
        return $this->succ($data, false);

    }


}
