<?php
class RcstatController extends BaseController {
/*** 查询列表***/
public function listAction() {
        $request = $this->input()->request();
        $args = array();

        //搜索条件
        $query = $request['query'] ?? false;
        $query_type = $request['query_type'] ?? false;
        if(!empty($query) && !empty($query_type)){
            switch ($query_type){
                case 'id':
                    $args['filters']['id'] = $query;
                    break;
                default:
                    $args['filters']['id'] = $query;
                    break;
            }
        }
    !empty($request['start_time']) && ($args['filters']['start_time'] = strtotime($request['start_time']));
    !empty($request['end_time']) && ($args['filters']['end_time'] = strtotime($request['end_time']));
    //日期范围
    if($request['date_range'] !== ''){
        switch ($request['date_range']){
            case 'today':
                $args['filters']['start_time'] = strtotime('today');
                $args['filters']['end_time'] = strtotime('today +1 day');
                break;
            case 'yes':
                $args['filters']['start_time'] = strtotime('today -1 day');
                $args['filters']['end_time'] = strtotime('today');
                break;

            case 'week':
                $date=date('Y-m-d');  //当前日期

                $first=1; //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期

                $w=date('w',strtotime($date));  //获取当前周的第几天 周日是 0 周一到周六是 1 - 6

                $now_start=date('Y-m-d',strtotime("$date -".($w ? $w - $first : 6).' days')); //获取本周开始日期，如果$w是0，则表示周日，减去 6 天

                $args['filters']['start_time'] = $now_start;
                $args['filters']['end_time'] = strtotime("$now_start +6 days");
                break;
            case 'last':
                $date=date('Y-m-d');  //当前日期

                $first=1; //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期

                $w=date('w',strtotime($date));  //获取当前周的第几天 周日是 0 周一到周六是 1 - 6

                $now_start=date('Y-m-d',strtotime("$date -".($w ? $w - $first : 6).' days')); //获取本周开始日期，如果$w是0，则表示周日，减去 6 天

                $last_start=date('Y-m-d',strtotime("$now_start - 7 days"));  //上周开始日期

                $last_end=date('Y-m-d',strtotime("$now_start - 1 days"));  //上周结束日期

                $args['filters']['start_time'] = $last_start;
                $args['filters']['end_time'] = $last_end;
                break;
        }
    }
        //查询页码
        $page = $request['pagenum'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;

        //查询数据
        $model = new \Recharge\RcstatModel();
        $data = $model->lists($page, $pagesize, $args);

        //数据重构
        $list = $data['list'];
        unset($data['list']);
        $ret_list = [];
        foreach ($list as &$item) {
            $item['total'] = $item['total']/100;
            $item['actually'] = $item['actually']/100;
            $item['coupon'] = $item['coupon']/100;
            $item['bank'] = $item['bank']/100;
            $item['chongzhi'] = $item['chongzhi']/100;
            $item['dianka'] = $item['dianka']/100;
            $item['wx'] = $item['wx']/100;
            $item['qq'] = $item['qq']/100;
            $item['ali'] = $item['ali']/100;
            $item['stat_date'] = date('Y-m-d',$item['stat_date']);
            $ret_list[] = $item;
        }
        $data['data'] = $ret_list;

        //反馈数据
        return $this->succ($data, false);
    }

/*** 创建/修改***/public function saveAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
	'id'=>'required',

	'wx_amount'=>'required',

	'zfb_amount'=>'required',

	'wy_amount'=>'required',

	'card_amount'=>'required',

	'stat_date'=>'required',

	'create_time'=>'required',
])) {
            return false;
        }

        $object_id = $request['id'] ?? false;
        $request['total_price'] = $request['price'];
        $model = new \Recharge\RcstatModel();
        if (empty($object_id)) {
            //新建
            $status = $model->create($request);
        } else {
            //修改
            $status = $model->edit($object_id, $request);
        }
        if (false === $status) {
            return $this->failed('保存失败');
        }
        return $this->succ();
    }

/*** 删除商品的状态***/
    public function deleteAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
        'goods_id' => 'required'
        ])) {
        return false;
        }
        $model = new \Recharge\RcstatModel();
        if (!$model->delete($request['goods_id'])) {
        return $this->failed('操作失败');
        }
        return $this->succ();
    }

}