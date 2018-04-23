<?php
class DiamondController extends BaseController {
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
                    $args['filters']['user_id'] = $query;
                    break;
                case 'gold_above':
                    $args['filters']['gold_above'] = $query;
                    break;
                case 'gold_below':
                    $args['filters']['gold_below'] = $query;
                    break;
            }
        }

        //查询页码
        $page = $request['pagenum'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;

        //查询数据
        $model = new \Player\DiamondModel();
        $data = $model->lists($page, $pagesize, $args);

        //数据重构
        $list = $data['list'];
        unset($data['list']);
        $ret_list = [];
        foreach ($list as $item) {
            $new = array_change_keys($item, array(
                'id'            => 'id',
                'user_id'      => 'user_id',
                'username'     => 'username',
                'nickname'     => 'nickname',
                'credit'       => 'credit',
                'update_date' => 'update_date',
            ),true);
            $ret_list[] = $new;
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

            'game_id'=>'required',

            'username'=>'required',

            'diamonds_before'=>'required',

            'trade_event'=>'required',

            'nickname'=>'required',

            'diamonds_after'=>'required',

            'dismonds_trade'=>'required',

            'operate_address'=>'required',

            'created_at'=>'required',

            'updated_at'=>'required',

            'remark'=>'required',
        ])) {
            return false;
        }

        $object_id = $request['id'] ?? false;
        $request['total_price'] = $request['price'];
        $model = new \Player\DiamondModel();
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
        $model = new \Player\DiamondModel();
        if (!$model->delete($request['goods_id'])) {
        return $this->failed('操作失败');
        }
        return $this->succ();
    }

}