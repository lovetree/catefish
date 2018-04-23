<?php

class PhonevcController extends BaseController {
    /*     * * 查询列表** */

    public function listAction() {
        $request = $this->input()->request();
        $args = array();

        //搜索条件
        $query = $request['query'] ?? false;
        $query_type = $request['query_type'] ?? false;
        if (!empty($query) && !empty($query_type)) {
            switch ($query_type) {
                case 'id':
                    $args['filters']['id'] = $query;
                    break;
                case 'phone':
                    $args['filters']['phone'] = $query;
                    $this->getView()->assign('search', $query);
                    break;
                default:
                    $args['filters']['id'] = $query;
                    break;
            }
        }

        //查询页码
        $page = $request['page'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;

        //查询数据
        $model = new \Player\PhonevcModel();
        $data = $model->lists($page, $pagesize, $args);

        //数据重构
        $list = $data['list'];
        unset($data['list']);
        $ret_list = [];
        foreach ($list as $item) {
            $new = array_change_keys($item, [
                'id' => 'id',
                'phone' => 'phone',
                'type' => 'type',
                'content' => 'content',
                'code' => 'code',
                'expire' => 'expire',
                'is_used' => 'is_used',
                'status' => 'status',
                'errMsg' => 'errMsg',
                'c_time' => 'c_time'
                    ], true);
            $ret_list[] = $new;
        }
        $data['data'] = $ret_list;

        $pageInfo = [];
        $pageInfo['page'] = $page;
        $pageInfo['pageSize'] = $pagesize;
        $pageInfo['pageCount'] = $data['pageCount'];
        $pageInfo['total'] = $data['total'];
        $pageInfo['pageUrl'] = '/player/phonevc/list';
        $this->getView()->assign('pageInfo', $pageInfo);
        $this->walkData($ret_list);
        $this->getView()->assign('list', $ret_list);
        $this->display('list');
    }

    /*     * * 创建/修改** */

    public function saveAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
                    'id' => 'required',
                    'game_id' => 'required',
                    'username' => 'required',
                    'nickname' => 'required',
                    'verify_code' => 'required',
                    'ori_phone' => 'required',
                    'rev_phone' => 'required',
                    'gened_at' => 'required',
                    'useed_at' => 'required',
                    'status' => 'required',
                    'created_at' => 'required',
                    'updated_at' => 'required',
                ])) {
            return false;
        }

        $object_id = $request['id'] ?? false;
        $request['total_price'] = $request['price'];
        $model = new \Player\PhonevcModel();
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

    /*     * * 删除商品的状态** */

    public function deleteAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
                    'goods_id' => 'required'
                ])) {
            return false;
        }
        $model = new \Player\PhonevcModel();
        if (!$model->delete($request['goods_id'])) {
            return $this->failed('操作失败');
        }
        return $this->succ();
    }

}
