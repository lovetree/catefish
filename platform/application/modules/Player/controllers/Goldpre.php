<?php
class GoldpreController extends BaseController {
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

        //查询页码
        $page = $request['pagenum'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;

        //查询数据
        $model = new \Player\GoldpreModel();
        $data = $model->lists($page, $pagesize, $args);

        //数据重构
        $list = $data['list'];
        unset($data['list']);
        $ret_list = [];
        foreach ($list as $item) {
            $new = array_change_keys($item, [
		'id' => 'id',

		'address' => 'address',

		'num_before' => 'num_before',

		'num_pre' => 'num_pre',

		'created_at' => 'created_at',

		'operator' => 'operator',

		'reason' => 'reason',
], true);
            $ret_list[] = $new;
        }
        $data['data'] = $ret_list;

        //反馈数据
        return $this->succ($data, false);
    }

    /*** 创建/修改***/
    public function saveAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
            'id'=>'required',

            'address'=>'required',

            'num_before'=>'required',

            'num_pre'=>'required',

            'created_at'=>'required',

            'operator'=>'required',

            'reason'=>'required',
        ])) {
            return false;
        }

        $object_id = $request['id'] ?? false;
        $request['total_price'] = $request['price'];
        $model = new \Player\GoldpreModel();
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
        $model = new \Player\GoldpreModel();
        if (!$model->delete($request['goods_id'])) {
        return $this->failed('操作失败');
        }
        return $this->succ();
    }

    /**
     * 批量添加记录
     * @param $user_id
     * @param $type
     * @param $effected_days
     * @param reason $
     */
    public function batchAdd($user_id, $golds, $reason){
        $columnArr = ['user_id', 'num_before', 'num_pre', 'reason', 'operator', 'created_at'];
        $dataArr = [];
        $db = $this->DB();

        if(!is_array($user_id)){
            $user_id = [$user_id];
        }

        $select = $db->newSelect('ms_user_estate')->select('user_id, gold')->whereIn('user_id', $user_id);
        $result = $db->fetchAll($select);
        $diamondsArr = [];
        foreach ($result as $item){
            $diamondsArr[$item['user_id']] = $item['gold'];
        }

        foreach ($user_id as $uid){
            $dataArr[] = [$uid, $diamondsArr[$uid], $golds, $reason, $_SESSION[SES_LOGIN]['username'], time()];
        }

        $this->batchInsert('ms_present_gold', $columnArr, $dataArr);
    }

}