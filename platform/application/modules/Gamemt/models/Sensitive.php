<?php
namespace Gamemt;
class SensitiveModel extends \BaseModel {
    
    private $tb = 'ms_sensitive_word';
    private $p_l = ['id', 'name'];
    private $p_s = ['id', 'name'];
    private $model = [];
    private $select = [];
    public $keys = [];
    public $valid = [];
    public function __construct() {
        if (!$this->model){
            $this->model = $this->DB()->getTable($this->tb);
        }
        
        if (!$this->select){
            $this->select = $select = $this->DB()->newSelect($this->tb);
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
    public function _list(int $page = 1, int $pagesize = 15, array $args = []): array {
        if (is_array($args) && isset($args['filters'])) {
            //wehre
            foreach($args['filters'] as $filter=>$val){
                $this->select->whereLike($filter, '%' . $val . '%');
            }
        }
        $this->select->whereNot('status', -1);
        $this->select->order('id', 'DESC');
        $data = $this->DB()->fetchAllPage($this->select, $page, $pagesize);
        $data['list'] = $data['list']->toArray();
        return $data;
    }
    
    public function _get(array $params) {
        $select = $this->DB()->newSelect($this->tb);
        foreach ($params as $k=>$v){
            $select->where($k, $v);
        }
        $this->select->whereNot('status', -1);
        $res = $this->DB()->fetch($select);
        if (!$res) return false;
        return $res->getData();
    }
    
    public function _getAll(){
        $select = $this->DB()->newSelect($this->tb);
        $this->select->whereNot('status', -1);
        $data = $this->DB()->fetchAll($select);
        if ($data){
            return $data->toArray();
        }
        return false;
    }
    
    /**
     * 创建
     * @param array $params
     * @return mixed false: 保存失败;
     */
    public function _create(array $params = []) {
        $params = array_fetch($params, $this->p_s);
        $this->initC($params, $this->p_s);
        
        $this->model->setData($params);
        $status = $this->model->save();
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
        
        if (!$this->model->load($id)) {
            return false;
        }
        $this->model->setData($params);
        $status = $this->model->save();
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
        if(!$this->update($this->tb, $id, $params)){
            return false;
        }
        
        return true;
    }
    
    //同步redis
    public function reset(){
        $this->select->whereNot('status', -1);
        $data = $this->DB()->fetchAll($this->select);
        if (!$data) return;
        $list = $data->toArray();
        $r = [];
        foreach ($list as $k=>$v){
            $r[] = $v['name'];
        }
        
        $redis = \PRedis::instance();
        $redis->select(R_GAME_DB);
        return $redis->set(PK_SENSITIVE_WORD, json_encode($r));
    }
    
    //批量上传
    public function upload(array $data) {
        
        //过滤已经存在的
        $list = $this->_getAll();
        $names = [];
        if ($list){
            foreach ($list as $v){
                $names[] = $v['name'];
            }
        }
        
        $beans = [];
        //去重
        array_filter(array(array_filter($data)));
        foreach ($data as $v){
            if ($names && in_array($v, $names)){
                continue;
            }
            $bean = [];
            $bean['name'] = $v;
            $this->initC($bean);
            $beans[] = $bean;
        }
        
        if (!$beans){
            return true;
        }
        
        $select = $this->DB()->newSelect($this->tb);
        $sql = $select->insert(['name','updated_uid','created_uid','u_time','c_time'], $beans);
        return $this->DB()->exec($sql);
    }
    
}