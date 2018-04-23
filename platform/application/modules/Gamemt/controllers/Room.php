<?php
class RoomController extends BaseController {
    /**
     * 查询列表
     **/
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
        if(!empty($request['type']) && !empty($request['type'])) $type = $request['type'];
        //查询页码
        $page = $request['pagenum'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;

        //查询数据
        $model = new \Gamemt\RoomModel();
        $data = $model->lists($page, $pagesize, $args,$type);

        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        //库存从redis取

        foreach ($data['data'] as $k=>$v){
            $key = 'config_'. $v['game_type'] .'_' . $v['game_mode'];
            $stock = $redis->hGet($key, 'currentstock');
            $stocks = $redis->hGet($key, 'stock');
            $instock = $redis->hGet($key, 'instock');
            $outstock = $redis->hGet($key, 'outstock');
            $data['data'][$k]['currentstock'] = $stock?$stock:0;
            $data['data'][$k]['stock'] = $stocks?$stocks:0;
            $data['data'][$k]['instock'] = $instock?$instock:0;
            $data['data'][$k]['outstock'] = $outstock?$outstock:0;
            if ($data['data'][$k]['stock']>0){
                $data['data'][$k]['state']='放水';
            }elseif($data['data'][$k]['stock']<0){
                $data['data'][$k]['state']='抽水';
            }else{
                if($stock<$data['data'][$k]['stock_limit_down'])
                    $data['data'][$k]['state']='抽水';
                else
                    $data['data'][$k]['state']='正常';
            }
        }

        //反馈数据
        return $this->succ($data, false);
    }

    /*** 创建/修改***/
    public function saveAction() {
        //验证参数
        $request = $this->input()->request();


        //mysql修改
        $object_id = $request['id'] ?? false;
        $model = new \Gamemt\RoomModel();
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

    public function setStockAction(){
        $input = $this->input()->request();

        $model = $this->DB()->getTable('ms_gm_room');
        if (!$model->load($input['id'])) {
            return false;
        }
        $type = $model->getOriginData('game_type','');
        $mod = $model->getOriginData('game_mode','');
        $key = 'config_'. $type.'_' . $mod;
        $redis = PRedis::instance();
        $old = $redis->hGet($key, 'currentstock');;
        $model->setData(['currentstock'=>$old-$input['currentstock']]);
        $status = $model->save();

        if (!$status) {
            return $this->failed('修改失败');
        }

        //更新redis

        $redis->select(R_GAME_DB);
        $redis->hSet('config_'.$input['game_type'] . '_'. $input['game_mode'], 'currentstock', $old-$input['currentstock']);

        return $this->succ();
    }
    public function setStocksAction(){
        $input = $this->input()->request();

        $model = $this->DB()->getTable('ms_gm_room');
        if (!$model->load($input['id'])) {
            return false;
        }
        $model->setData(['stock'=>$input['stock']]);
        $status = $model->save();

        if (!$status) {
            return $this->failed('修改失败');
        }

        //更新redis
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $redis->hSet('config_'.$input['game_type'] . '_'. $input['game_mode'], 'stock', $input['stock']);

        return $this->succ();
    }

    /*** 删除商品的状态***/
    public function deleteAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
        'id' => 'required'
        ])) {
        return false;
        }
        $model = new \Gamemt\RoomModel();
        if (!$model->delete($request['id'])) {
        return $this->failed('操作失败');
        }
        return $this->succ();
    }

    public function refreshAction(){
        if(RedisFreshModel::refreshRoomList()){
            return $this->succ();
        }else{
            return $this->failed("刷新失败");
        }
    }

}