<?php
namespace Gamemt;
class ModelModel extends \BaseModel {
    
    private $tb = 'ms_model';
    private $p_l = ['id', 'm_type', 'name', 'status' , 'u_time', 'updated_uid'];
    private $p_s = ['id', 'm_type', 'name', 'status'];
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
    
    /**
     * 创建
     * @param array $params
     * @return mixed false: 保存失败;
     */
    public function _create(array $params = []) {
        $params = array_fetch($params, $this->p_s);
        $this->initC($params, $this->p_s);
        
        $db = $this->DB();
        $db->beginTransaction();
        $model = $db->getTable($this->tb);
        $model->setData($params);
        $status = $model->save();
        if (!$status) {
            $db->rollBack();
            return false;
        }
        
        if (!$this->baseDelRedis(R_GAME_DB, PK_MODEL)){
            $db->rollBack();
            return false;
        }
        
        $db->commit();
        $this->initReids();
        
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
        $db->beginTransaction();
        $model = $db->getTable($this->tb);
        if (!$model->load($id)) {
            $db->rollBack();
            return false;
        }
        $model->setData($params);
        $status = $model->save();
        if (!$status) {
            $db->rollBack();
            return false;
        }
        
        if (!$this->baseDelRedis(R_GAME_DB, PK_MODEL)){
            $db->rollBack();
            return false;
        }
        
        $db->commit();
        $this->initReids();
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
        
        $db = $this->DB();
        $db->beginTransaction();
        if(!$this->update($this->tb, $id, $params)){
            $db->rollBack();
            return false;
        }
        
        if (!$this->baseDelRedis(R_GAME_DB, PK_MODEL)){
            $db->rollBack();
            return false;
        }
        
        $db->commit();
        $this->initReids();
        return true;
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
        
        $db = $this->DB();
        $db->beginTransaction();
        if(!$this->update($this->tb, $id, $params)){
            $db->rollBack();
            return false;
        }
        if (!$this->baseDelRedis(R_GAME_DB, PK_MODEL)){
            $db->rollBack();
            return false;
        }
        
        $db->commit();
        $this->initReids();
        return true;
    }
    
    //同步redis
    private function initReids(){
        $this->select->whereNot('status', -1);
        $data = $this->DB()->fetchAll($this->select);
        if (!$data) return;
        $list = $data->toArray();
        foreach ($list as $k=>$v){
            unset($list[$k]['u_time']);
            unset($list[$k]['updated_uid']);
        }
        
        $redis = \PRedis::instance();
        $redis->select(R_GAME_DB);
        $redis->set(PK_MODEL, json_encode($list));
    }
}