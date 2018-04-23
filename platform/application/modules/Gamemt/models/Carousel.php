<?php
namespace Gamemt;
class CarouselModel extends \BaseModel {
	/**
     * 获取列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
	public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_carousel')
			->select('id')
			->select('img_url')
			->select('order')
			->select('game_type')
			->select('game_mode')
			->select('is_hide')
			->select('jump_url')
			->select('created_at')
			->select('updated_at');
        $select = $select->whereNot('status', -1);

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
        $params = array_fetch($params, 'id','img_url','order','game_type','game_mode','is_hide','jump_url','created_at');
        $model = $this->DB()->getTable('ms_carousel');
        $params['created_at'] = date('Y-m-d H:i:s');
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
        $model = $this->DB()->getTable('ms_carousel');
        if (!$model->load($id)) {
            return false;
        }

        if($params['img_url'] == ''){
            unset($params['img_url']);
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
    public function delete($id) {
        if (!is_array($id)) {
            $id = [$id];
        }
        
        $select = $this->DB()->newSelect('ms_carousel');
        $select->whereIn('id', $id);
        $select->setData('status', -1);
        
        if($this->DB()->exec($select->updateSql())){
            return true;
        }else{
            return false;
        }
    }
}