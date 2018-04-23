<?php
namespace Gamemt;
class NoticeModel extends \BaseModel {
    
    private $tb = 'ms_game_message';
    private $p_l = ['id', 'title', 'content', 'status', 'start_time', 
        'attribute', 'images', 'u_time', 'updated_uid'];
    private $p_s = ['id', 'title', 'content', 'start_time', 'attribute', 'images'];
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
        $this->select->where('type', 0);
        $this->select->whereNot('status', -1);
        $this->select->order('id', 'desc');
        $data = $this->DB()->fetchAllPage($this->select, $page, $pagesize);
        $data['list'] = $data['list']->toArray();
        return $data;
    }
    
    public function _get(array $params) {
        $select = $this->DB()->newSelect($this->tb);
        foreach ($params as $k=>$v){
            $select->where($k, $v);
        }
        return $this->DB()->fetch($select)->getData();
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
        
        $info = $this->_get(['id'=>$id]);
        if ($info && $info['status']){
            if (!$this->baseZdelRedis(R_MESSAGE_DB, RK_NOTICE_LIST, $id)){
                $db->rollBack();
                return false;
            }
            $data = [];
            $data['id'] = $status;
            $data['type'] = 0;
            $data['title'] = $info['title'];
            $data['images'] = $info['images'];
            $data['content'] = $info['content'];
            $data['c_time'] = date('Y-m-d H:i:s');
            if (!$this->baseZaddRedis(R_MESSAGE_DB, RK_NOTICE_LIST, intval($id), $data)){
                $db->rollBack();
                return false;
            }
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
        
        $db = $this->DB();
        $db->beginTransaction();
        if(!$this->update($this->tb, $id, $params)){
            $db->rollBack();
            return false;
        }
        
        if (!$this->baseZdelRedis(R_MESSAGE_DB, RK_NOTICE_LIST, $id)){
            $db->rollBack();
            return false;
        }
        
        $db->commit();
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
        
        if ($params['status']){
            //上线
            $this->select->whereIn('id', $id);
            $list = $this->DB()->fetchAll($this->select);
            if (!$list){
                $db->rollBack();
                return false;
            }
            $list = $list->toArray();
            if (!$list){
                $db->rollBack();
                return false;
            }
            $score = [];
            foreach ($list as $k=>$v){
                $list[$k]['score'] = $v['id'];
                $score[] = $v['id'];
            }
            if (!$this->baseZaddRedis(R_MESSAGE_DB, RK_NOTICE_LIST, $score, $list)){
                $db->rollBack();
                return false;
            }
        }else{
            //下线
            if (!$this->baseZdelRedis(R_MESSAGE_DB, RK_NOTICE_LIST, $id)){
                $db->rollBack();
                return false;
            }
        }
        
        $db->commit();
        return true;
    }
    
}