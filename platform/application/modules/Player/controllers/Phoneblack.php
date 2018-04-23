<?php
class PhoneblackController extends BaseController {
    /**
     * 查询列表
     *
     **/
    public function listAction() {
        //查询页码
        $page = $request['pagenum'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;

        //查询数据
        $model = new \Player\PhoneblackModel();
        $data = $model->lists($page, $pagesize, ['type'=>$this->input()->get('type')]);

        //反馈数据
        return $this->succ($data, false);
    }

/**
 * 创建/修改
 **/
public function saveAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
            'user_id'=>'required',
        ])) {
            return false;
        }

        $object_id = $request['id'] ?? false;
        $model = new \Player\PhoneblackModel();
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
     * 删除商品的状态
     *
     **/
    public function deleteAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
            'id' => 'required'
        ])) {
        return false;
        }
        $model = new \Player\PhoneblackModel();
        if (!$model->delete($request['id'], $request['type'])) {
        return $this->failed('操作失败');
        }
        return $this->succ();
    }

}