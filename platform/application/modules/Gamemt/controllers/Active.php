<?php

class ActiveController extends BaseController {
    
    private $model = null;
    public function init() {
        if (!$this->model){
            $this->model = new \Gamemt\ActiveModel();
        }
    }
    
    /*
     * 查询列表
     */
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
                default:
                    $args['filters']['id'] = $query;
                    break;
            }
        }

        //查询页码
        $page = $request['pagenum'] ?? 1;
        $pagesize = $request['pagesize'] ?? 10;

        //查询数据
        $data = $this->model->_list($page, $pagesize, $args);
        //数据重构
        $list = $data['list'];
        unset($data['list']);
        $ret_list = [];
        
        foreach ($list as $item) {
            $new = array_change_keys($item, $this->model->keys, true);
            $ret_list[] = $new;
        }

        $pageInfo = [];
        $pageInfo['page'] = $page;
        $pageInfo['pageSize'] = $pagesize;
        $pageInfo['pageCount'] = $data['pageCount'];
        $pageInfo['total'] = $data['total'];
        $pageInfo['pageUrl'] = '/gamemt/notice/list';
        $this->getView()->assign('pageInfo', $pageInfo);
        $this->walkData($ret_list);
        $this->getView()->assign('list', $ret_list);
        $this->display('list');
    }
    
    public function editAction() {
        $h_title = "";
        $request = $this->input()->request();
        $info = [];
        $h_title = "添加活动";
        if (isset($request['id']) && $request['id']){
            $params = [];
            $params['id'] = $request['id'];
            $info = $this->model->_get($params);
            $h_title = "编辑活动";
        }
        
        //获取模板信息
        $subject = new \Gamemt\SubjectModel();
        $s_list = $subject->_getList();
        $this->getView()->assign('s_list', $s_list);
        $this->getView()->assign('h_title', $h_title);
        $this->getView()->assign('info', $info);
        $this->display('edit');
    }
    
    /*
     * 创建/修改
     */

    public function saveAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
            'id'=>'require',
            'title'=>'require',
            'image'=>'require',
            'link_type'=>'require',
            'link'=>'require',
            'a_sort'=>'require',
            'times'=>'require'
        ])) {
            return false;
        }
        
        $time = explode('-', $request['times']);
        $start_time = $time[0];
        $end_time = $time[1];
        $params = [];
        $params['title'] = $request['title'];
        $params['image'] = $request['image'];
        $params['link_type'] = $request['link_type'];
        $params['game_type'] = $request['game_type'];
        $params['game_mode'] = $request['game_mode'];
        $params['link'] = $request['link'];
        $params['a_sort'] = $request['a_sort'];
        $params['start_time'] = strtotime($start_time);
        $params['end_time'] = strtotime($end_time);
        $object_id = $request['id'] ?? false;
        if (empty($object_id)) {
            //新建
            $status = $this->model->_create($params);
        } else {
            //修改
            $status = $this->model->_edit($object_id, $params);
        }
        if (false === $status) {
            return $this->failed('保存失败');
        }

        //更新redis数据
        RedisFreshModel::refreshActivity();

        return $this->succ();
    }

    /*
     * 删除商品的状态
     * 批量删除
     */

    public function deleteAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
                    'id' => 'required'
                ])) {
            return false;
        }
        if (!$this->model->_delete($request['id'])) {
            return $this->failed('操作失败');
        }

        //更新redis数据
        RedisFreshModel::refreshActivity();

        return $this->succ();
    }

    /*
     * 删除商品的状态
     * 批量上下架
     * @id值，批量操作的id号
     * @status 0下架 1上架
     */

    public function updateAction() {
        //验证参数
        $request = $this->input()->request();
        if (!$this->validation($request, [
                'id' => 'required',
                'status' => 'required'
                ])) {
            return false;
        }
        
        $params = [];
        $params['status'] = $request['status'];
        if (!$this->model->_update($request['id'], $params)) {
            return $this->failed('操作失败');
        }

        //更新redis数据
        RedisFreshModel::refreshActivity();

        return $this->succ();
    }

}
