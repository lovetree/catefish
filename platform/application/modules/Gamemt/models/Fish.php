<?php
namespace Gamemt;
class FishModel extends \BaseModel {
    
    private $tb = 'ms_fish';
    private $p_l = ['id', 'kind', 'name', 'saphire', 'speed', 'multiple', 
        'hp', 'localmultiple', 'hpvar', 'captureProbability', 'status'];
    private $p_s = ['id', 'kind', 'name', 'saphire', 'speed', 'multiple', 
        'hp', 'localmultiple', 'hpvar', 'captureProbability'];
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
        $this->select->order('kind', 'DESC');
        $data = $this->DB()->fetchAllPage($this->select, $page, $pagesize);
        $data['list'] = $data['list']->toArray();
        return $data;
    }
    
    public function _get(array $params) {
        $select = $this->DB()->newSelect($this->tb);
        foreach ($params as $k=>$v){
            $select->where($k, $v);
        }
        $select->whereNot('status', -1);
        $data = $this->DB()->fetch($select);
        if (!$data) return [];
        return $data->getData();
    }
    
    /**
     * 获取系数
     */
    public function getCoefficient() {
        $select = $this->DB()->newSelect("ms_fish_coefficient");
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
        
        if (!$this->baseDelRedis(R_GAME_DB, PK_FISH)){
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
        
        if (!$this->baseDelRedis(R_GAME_DB, PK_FISH)){
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
        if (!$this->baseDelRedis(R_GAME_DB, PK_FISH)){
            $db->rollBack();
            return false;
        }
        
        $db->commit();
        $this->initReids();
        return true;
    }
    
    //同步redis
    private function initReids(){
        $this->select->where('status', 1);
        $this->select->order('kind');
        $data = $this->DB()->fetchAll($this->select);
        if (!$data) return;
        $list = $data->toArray();
        $r_list = [];
        foreach ($list as $k=>$v){
            unset($v['u_time']);
            unset($v['updated_uid']);
            unset($v['status']);
            unset($v['id']);
            $r_list[$v['kind']] = json_encode($v);
        }
        
        $redis = \PRedis::instance();
        $redis->select(R_GAME_DB);
        $redis->hMset(PK_FISH, $r_list);
    }
    
    //调控系数
    public function _ajaxCoe($params) {
        $beans = [];
        $beans['controlfactor'] = $params['controlfactor'];
        $beans['room'] = json_encode(['room1'=>$params['room1'], 
            'room2'=>$params['room2'], 'room3'=>$params['room3'], 'room4'=>$params['room4']]);
        
        $info = $this->getCoefficient();
        
        $db = $this->DB();
        $model = $db->getTable('ms_fish_coefficient');
        $db->beginTransaction();
        if ($info){
            $this->initU($beans);
            if (!$model->load($info['id'])) {
                $db->rollBack();
                return false;
            }
        }else{
            $this->initC($beans);
            
        }
        
        $model->setData($beans);
        $status = $model->save();
        if (!$status) {
            $db->rollBack();
            return false;
        }
        
        $redis = \PRedis::instance();
        $redis->select(R_GAME_DB);
        $r_list = [];
        foreach ($params as $k=>$v){
            $r_list[ucfirst($k)] = $v;
        }
        
        if (!$redis->hMset(PK_FISH_COEFFICIENT, $r_list)){
            $db->rollBack();
            return false;
        }
        
        $db->commit();
        return true;
    }
    
}