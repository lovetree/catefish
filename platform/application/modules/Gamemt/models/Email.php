<?php
namespace Gamemt;
class EmailModel extends \BaseModel {
    
    private $tb = 'ms_game_email';
    private $p_l = ['id', 'title', 'content', 'from_id', 'from_name', 'to_id', 'created_time', 'image'];
    private $p_s = ['id', 'title', 'content', 'to_id', 'image'];
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
        
        $db = $this->DB();
        $db->beginTransaction();
        $model = $db->getTable($this->tb);
        $params['from_id'] = 0;
        $params['from_name'] = "系统";
        $model->setData($params);
        $status = $model->save();
        if (!$status) {
            $db->rollBack();
            return false;
        }
        
        $redis = \PRedis::instance();
        $redis->select(R_EMAIL_DB);
        $redis_data = [];
        $redis_data['m_id'] = $status;
        $redis_data['m_title'] = $params['title'];
        $redis_data['m_content'] = $params['content'];
        $redis_data['m_image'] = $params['image'];
        $redis_data['m_from'] = 0;
        $redis_data['m_from_name'] = "系统";
        $redis_data['m_status'] = 0;
        $redis_data['m_time'] = date("Y-m-d H:i:s");
        
        //系统邮件   用户邮件
        if ($params['to_id'] == 0){
            if ($redis->zAdd(PK_USER_SYS_EMAIL, intval($status), json_encode($redis_data)) == FALSE){
                $db->rollBack();
                return false;
            }
        }else{
            $ids = explode(',', $params['to_id']);
            $redis->multi();
            foreach ($ids as $v){
                $redis->zAdd(PK_USER_EMAIL . $v, intval($status), json_encode($redis_data));
            }
            $redis->exec();
        }
        
        $db->commit();
        return $status;
    }

    /**
     * 修改
     * @param array $params
     * @return mixed false: 保存失败;
     */
    public function _edit(int $id, array $params = []) {
        $info = $this->_get(['id'=>$params['id']]);
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
        
        $redis = \PRedis::instance();
        $redis->select(R_EMAIL_DB);
        $redis_data = [];
        $redis_data['m_id'] = $status;
        $redis_data['m_title'] = $params['title'];
        $redis_data['m_content'] = $params['content'];
        $redis_data['m_image'] = $params['image'];
        $redis_data['m_from'] = 0;
        $redis_data['m_from_name'] = "系统";
        $redis_data['m_status'] = 0;
        $redis_data['m_time'] = $info['created_time'];
        
        $redis->multi();
        //先找出之前的记录
        $toid = $info['to_id'];
        if ($toid == 0){
            $redis->zRemRangeByScore(PK_USER_SYS_EMAIL, intval($params['id']), intval($params['id']));
        }else{
            $ids = explode(',', $toid);
            foreach ($ids as $v){
                $redis->zRemRangeByScore(PK_USER_EMAIL . $v, intval($params['id']), intval($params['id']));
            }
        }
        
        //系统邮件   用户邮件
        if ($params['to_id'] == 0){
            $redis->zRemRangeByScore(PK_USER_SYS_EMAIL, intval($params['id']), intval($params['id']));
            $redis->zAdd(PK_USER_SYS_EMAIL, intval($params['id']), json_encode($redis_data));
        }else{
            $ids = explode(',', $params['to_id']);
            foreach ($ids as $v){
                $redis->zRemRangeByScore(PK_USER_EMAIL . $v, intval($params['id']), intval($params['id']));
                $redis->zAdd(PK_USER_EMAIL . $v, intval($params['id']), json_encode($redis_data));
            }
        }
        $redis->exec();
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
        
        if (!$this->baseDelRedis(R_MESSAGE_DB, RK_BROADCAST_LIST_1)){
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
        if (!$this->baseDelRedis(R_MESSAGE_DB, RK_BROADCAST_LIST_1)){
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
        $data = $this->DB()->fetchAll($this->select);
        if (!$data) return;
        $list = $data->toArray();
        foreach ($list as $k=>$v){
            if (time() >= $v['end_time']){
                unset($list[$k]);
                continue;
            }
            unset($list[$k]['u_time']);
            unset($list[$k]['updated_uid']);
            $list[$k]['score'] = $v['start_time'];
        }
        if (!$list) return;
        $this->baseZadd(R_MESSAGE_DB, RK_BROADCAST_LIST_1, $list);
    }
}