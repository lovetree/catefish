<?php
namespace Gamemt;
class HorsemsgModel extends \BaseModel {
	/**
     * 获取列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
	public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_gm_horsemsg')
			->select('id')
			->select('msg_content')
			->select('game_type')
			->select('game_mode')
			->select('is_forbid')
			->select('remark')
			->select('created_at')
			->select('updated_at')
            ->order('id');
        $select = $select->whereNot('is_forbid', 1);

        if (is_array($args) && isset($args['filters'])) {
            //wehre
            foreach($args['filters'] as $filter=>$val){
                $select->whereLike($filter, '%' . $val . '%');
            }
        }

        $data = $db->fetchAllPage($select, $page, $pagesize);

        $data['list'] = $data['list']->toArray();
        return $data;
    }

	/**
     * 创建
     * @param array $params
     * @return mixed false: 保存失败;
     */
    public function create(array $params = []) {
        $params = array_fetch($params, 'id','msg_content','game_type','game_mode','is_forbid','remark','created_at','updated_at');
        $model = $this->DB()->getTable('ms_gm_horsemsg');
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
    public function edit(int $id, array $params = []) {
        $params = array_fetch($params, 'id','msg_content','game_type','game_mode','is_forbid','remark','created_at','updated_at');
        $model = $this->DB()->getTable('ms_gm_horsemsg');
        if (!$model->load($id)) {
            return false;
        }
        $model->setData($params);
        $status = $model->save();
        if (!$status) {
            return false;
        }
        return true;
    }/**
     * 删除记录
     * @param int|array $id
     * @return boolean
     */
    public function delete($id) {
        if (!is_array($id)) {
            $id = [$id];
        }
        
        $select = $this->DB()->newSelect('ms_gm_horsemsg');
        $select->whereIn('id', $id);
        $select->setData('status', -1);
        
        if($this->DB()->exec($select->updateSql())){
            return true;
        }else{
            return false;
        }
    }
}