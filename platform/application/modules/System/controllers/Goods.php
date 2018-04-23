<?php

class GoodsController extends BaseController {

    /**
     * 查询商品列表
     */
    public function listAction() {
        $request = $this->input()->request();
        $args = array();

        //搜索条件
        !empty($request['query']) && ($args['filters']['name'] = $request['query']);

        //查询页码
        $page = $request['pagenum'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;

        //查询数据
        $model = new System\GoodsModel();
        $data = $model->lists($page, $pagesize, $args);

        //数据重构
        $data['data'] = $data['list']->toArray();
        unset($data['list']);

        //反馈数据
        return $this->succ($data, false);
    }

    /**
     * 创建/修改
     */
    public function saveAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
                    'id' => 'positive_number',
                    'name' => 'required|length:1:50',
                    'show_name' => 'required|length:1:50',
                    'price' => 'required|positive_number',
//                    'img_url' => 'length:1:255'
                ])) {
            return false;
        }

        //处理上传的图片
        $file = UpFile::Init()['file'] ?? false;
        if (false !== $file) {
            if (!$file->isError()) {
                try {
                    $imgUrl = $file->moveTo(APP_UPLOAD_DIR, true);
                    $request['img_url'] = $imgUrl;
                } catch (Exception $e) {
                    logMessage($e->getMessage());
                }
            } else {
                return $this->failed('上传的文件错误');
            }
        }

        $object_id = $request['id'] ?? false;
        $request['total_price'] = $request['price'];
        isset($request['start_time']) && ($request['start_time'] = strtotime($request['start_time']));
        isset($request['end_time']) && ($request['end_time'] = strtotime($request['end_time']));
        $model = new System\GoodsModel();
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

    /**
     * 设置商品的状态
     */
    public function activeAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
                    'goods_id' => 'required|positive_number',
                    'status' => 'required|inside:0:1',
                ])) {
            return false;
        }
        $model = new System\GoodsModel();
        if (!$model->setStatus($request['goods_id'], $request['status'])) {
            return $this->failed('操作失败');
        }
        return $this->succ();
    }

    /**
     * 删除商品的状态
     */
    public function deleteAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
                    'goods_id' => 'required'
                ])) {
            return false;
        }
        $model = new System\GoodsModel();
        if (!$model->delete($request['goods_id'])) {
            return $this->failed('操作失败');
        }
        return $this->succ();
    }

}
