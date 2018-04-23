<?php
namespace Gamemt;
class PackageModel extends \BaseModel {
    
    private $tb = 'ms_first_recharge_package';
    private $tblog = 'ms_first_recharge_package_log'; //日志文件
    private $p_l = ['id', 'type', 'package', 'status'];
    private $p_s = ['id', 'type', 'package'];
    private $model = [];
    private $select = [];
    public $keys = [];
    public $valid = [];
    public function __construct() {
        if (!$this->model){
            $this->model = $this->DB()->getTable($this->tb);
        }
        
        if (!$this->select){
            $this->select = $this->DB()->newSelect($this->tb);
        }
        
        if ($this->p_l){
            foreach ($this->p_l as $v){
                $this->select->select($v);
                $this->keys[$v] = $v;
            }
        }
        
        if ($this->p_s){
            foreach ($this->p_s as $v){
                $this->valid[$v] = 'required';
            }
        }
    }
    
    /**
     * 获取列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
    public function _info(int $page = 1, int $pagesize = 15, array $args = []): array {
        $select = $this->DB()->newSelect($this->tblog);
        if (is_array($args) && isset($args['filters'])) {
            //wehre
            foreach($args['filters'] as $filter=>$val){
                $select->whereLike($filter, '%' . $val . '%');
            }
        }
        $select->order('id', 'DESC');
        $select->select('id');
        $select->select('user_id');
        $select->select('type');
        $select->select('package');
        $select->select('ip');
        $select->select('c_time');
        $data = $this->DB()->fetchAllPage($select, $page, $pagesize);
        $data['list'] = $data['list']->toArray();
        return $data;
    }
    
    public function _list(int $page = 1, int $pagesize = 15, array $args = []): array {
        if (is_array($args) && isset($args['filters'])) {
            //wehre
            foreach($args['filters'] as $filter=>$val){
                $this->select->whereLike($filter, '%' . $val . '%');
            }
        }
        $this->select->whereNot('status', -1);
        $data = $this->DB()->fetchAllPage($this->select, $page, $pagesize);
        $data['list'] = $data['list']->toArray();
        return $data;
    }
    
    public function _get(array $params) {
        $select = $this->DB()->newSelect($this->tb);
        foreach ($params as $k=>$v){
            $select->where($k, $v);
        }
        $data = $this->DB()->fetch($select);
        if (!$data) return [];
        return $data->getData();
    }
    
    public function _getList() {
        $this->select->order('vip_level');
        $data = $this->DB()->fetchAll($this->select);
        return $data->toArray();
    }
    
    /**
     * 创建
     * @param array $params
     * @return mixed false: 保存失败;
     */
    public function _create(array $params = []) {
        $params = array_fetch($params, $this->p_s);
        $this->initC($params, $this->p_s);
        $model = $this->DB()->getTable($this->tb);
        $model->setData($params);
        $status = $model->save();
        if (!$status) {
            return false;
        }
        return $status;
    }

    /**
     * 修改
     * @param array $params
     * @return mixed false: 保存失败;
     */
    public function _edit(int $id, array $params = []) {
        $params = array_fetch($params, $this->p_s);
        $this->initU($params, $this->p_s);
        $db = $this->DB();
        $model = $db->getTable($this->tb);
        if (!$model->load($id)) {
            return false;
        }
        $model->setData($params);
        $status = $model->save();
        if (!$status) {
            return false;
        }
        return true;
    }
    
    /**
     * 删除记录
     * @param int|array $id
     * @return boolean
     */
    public function _delete($id) {
        if (!is_array($id)) {
            $id = [$id];
        }
        
        $params = [];
        $params['status'] = -1;
        $this->initU($params);
        return $this->update($this->tb, $id, $params);
    }
    
    /**
     * 更新上下架
     * @param int|array $id
     * @return boolean
     */
    public function _update($id, $params){
        if (!is_array($id)) {
            $id = [$id];
        }
        $this->initU($params);
        if(!$this->update($this->tb, $id, $params)){
            return false;
        }
        return true;
    }
}