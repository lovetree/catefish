<?php
namespace Admin;
class SiteConfigModel extends \BaseModel {
	/**
     * 获取列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
	public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_config_site')
			->select('id')
			->select('name')
			->select('code')
			->select('value');

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
        $params = array_fetch($params, 'id','name','code','value');
        $model = $this->DB()->getTable('ms_config_site');
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
        $params = array_fetch($params, 'id','name','code','value');
        $model = $this->DB()->getTable('ms_config_site');
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
        $this->DB()->trancation(function($db, &$rollback) use($id) {
            $select = $db->newSelect('ms_config_site');
            $select->whereIn('id', $id);
            $select->setData('status', -1);
            $db->exec($select->updateSql());
        });

        return true;
    }
}