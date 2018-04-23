<?php
class CarouselController extends BaseController {
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
        $model = new \Gamemt\CarouselModel();
        $data = $model->lists($page, $pagesize, $args);

        //数据重构
        $list = $data['list'];
        unset($data['list']);
        $ret_list = [];
        foreach ($list as $item) {
            $new = array_change_keys($item, [
            'id' => 'id',

            'img_url' => 'img_url',

            'order' => 'order',

            'game_type' => 'game_type',

            'game_mode' => 'game_mode',

            'is_hide' => 'is_hide',

            'jump_url' => 'jump_url',

            'created_at' => 'created_at',

            'updated_at' => 'updated_at'
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

					'order'=>'required',

					'game_type'=>'required',

					'game_mode'=>'required',

					'is_hide'=>'required',

					'jump_url'=>'required',
        ])) {
            return false;
        }

        $object_id = $request['id'] ?? false;

        if(isset($_FILES['img_url'])){
            //验证上传图片
            $image = $_FILES['img_url'];
            //图片格式必须为jpg
            if($image['type'] != 'image/jpg' && $image['type'] != 'image/jpeg'){
                return $this->failed('图片格式必须为jpg或者jpeg');
            }
            //保存图片
            $saveName = md5('carousel'.time().$image['name']).'.jpg';
            move_uploaded_file($image['tmp_name'], APP_PATH . '/upload_files/' . $saveName);
            $request['img_url'] = '/files/'. $saveName;
        }

        //开启事务
        $this->DB()->beginTransaction();
        //mysql数据修改
        $model = new \Gamemt\CarouselModel();
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

        //redis数据修改
        $redis = PRedis::instance();
        $redis->select(R_MESSAGE_DB);
        $key = RK_CAROUSEL_LIST;

        $result = $model->lists();

        $dataArr = [];
        foreach ($result['list'] as $data){
            $dataArr[] = [
                'order' => $data['order'],
                'url' => 'http://'.$_SERVER['HTTP_HOST'] . $data['img_url'],
                'game_type' => $data['game_type'],
                'game_mode' => $data['game_mode'],
            ];
        }
        if($redis->exists($key)){
            $originData = json_decode($redis->get($key), true);
            $version = $originData['version'];
            unset($originData);
        }

        $result = [
            'version' => $version + 1,
            'list' => $dataArr
        ];

        if($status = $redis->set($key, json_encode($result))){
            $this->DB()->commit();
        }else{
            $this->DB()->rollback();
            return $this->failed('保存失败');
        }

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
        $model = new \Gamemt\CarouselModel();
        if (!$model->delete($request['id'])) {
        return $this->failed('操作失败');
        }
        return $this->succ();
    }

}