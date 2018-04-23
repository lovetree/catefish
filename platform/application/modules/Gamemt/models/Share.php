<?php
namespace Gamemt;
class ShareModel extends \BaseModel {
    
    private $tb = 'ms_benefits_share_log';
    private $p_l = ['id', 'invite_id', 'user_id', 'gold', 'status', 'ip', 'c_time'];
    private $p_s = ['id', 'new_gold', 'new_time_limit', 'new_match', 'old_share_gold', 
        'old_share_interval', 'old_share_succ', 'invite_valid_time', 'invite_url'];
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
        $data = $this->DB()->fetchAllPage($this->select, $page, $pagesize);
        $data['list'] = $data['list']->toArray();
        return $data;
    }
    
    public function _get() {
        $select = $this->DB()->newSelect('ms_benefits_share_rule');
        if ($res = $this->DB()->fetch($select)){
            return $res->getData();
        }
        return null;
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
        $db = $this->DB();
        $db->beginTransaction();
        $model = $db->getTable('ms_benefits_share_rule');
        $model->setData($params);
        $status = $model->save();
        if (!$status) {
            $db->rollBack();
            return false;
        }
        
        //删除缓存
        if (!$this->baseDelRedis(R_BENEFITS_DB, PK_SHARE_RULE)){
            $db->rollBack();
            return false;
        }
        
        $db->commit();
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
        $db = $this->DB();
        $db->beginTransaction();
        $model = $db->getTable('ms_benefits_share_rule');
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
        
        //删除缓存
        if (!$this->baseDelRedis(R_BENEFITS_DB, PK_SHARE_RULE)){
            $db->rollBack();
            return false;
        }
        
        $db->commit();
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
    
}