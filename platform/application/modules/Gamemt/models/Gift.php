<?php
namespace Gamemt;
class GiftModel extends \BaseModel {
    
    private $tb = 'ms_gift';
    private $p_l = ['id', 'name', 'unit', 'icon', 'gold', 'popularity', 'g_type', 'g_sort', 'status', 'u_time', 'updated_uid'];
    private $p_s = ['id', 'name', 'unit', 'icon', 'gold', 'popularity', 'g_type', 'g_sort'];
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
        $this->select->order('g_sort');
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
        if (!$data) return false;
        return $data->getData();
    }
    
    public function _getRule() {
        $select = $this->DB()->newSelect('ms_gift_rule');
        $data = $this->DB()->fetch($select);
        if (!$data) return false;
        return $data->getData();
    }
    
    public function _getList() {
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
        return true;
    }

    /**
     * 修改
     * @param array $params
     * @return mixed false: 保存失败;
     */
    public function _edit(int $id, array $params = []) {
        $params = array_fetch($params, $this->p_s);
        $this->initU($params, $this->p_s);
        $model = $this->DB()->getTable($this->tb);
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
        return $this->update($this->tb, $id, $params);
    }
    
    //同步redis
    public function reset(){
        $this->select->where("status", 1);
        $this->select->order('g_sort');
        $data = $this->DB()->fetchAll($this->select);
        $list = $data->toArray();
        $redis = \PRedis::instance();
        $redis->select(R_GAME_DB);
        $redis->del(PK_GIFT_LIST);
        if (!$data) return;
        if ($redis->exists(PK_GIFT_LIST)){
            return false;
        }
        
        $r = [];
        foreach ($list as $v){
            $r[$v['g_type']] = json_encode($v);
        }
        return $redis->hMset(PK_GIFT_LIST, $r);
    }
    
    public function setInfo($params):bool {
        $info = $this->_getRule();
        
        $params = array_fetch($params, ['exchange']);
        $model = $this->DB()->getTable('ms_gift_rule');
        if ($info){
            $this->initU($params, $this->p_s);
            if (!$model->load($info['id'])) {
                return false;
            }
            $model->setData($params);
            $status = $model->save();
            if (!$status) {
                return false;
            }
        }else{
            $this->initC($params, $this->p_s);
            $model->setData($params);
            $status = $model->save();
            if (!$status) {
                return false;
            }
        }
        
        return true;
    }
}