<?php

namespace System;

class ItemModel extends \BaseModel {

    /**
     * 获取道具列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
    public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_item')
                ->select(['id', 'name', 'type'])
                ->whereNot('status', -1)
                ->order('created_date', 'desc');

        if (is_array($args)) {
            //wehre
            if (isset($args['filters']['name'])) {
                $select->whereLike('main_table.name', '%' . $args['filters']['name'] . '%');
            }
        }

        $data = $db->fetchAllPage($select, $page, $pagesize);

        $data['list'] = $data['list']->toArray();
        return $data;
    }

    /**
     * 创建道具
     * @param array $params
     * @return mixed false: 保存失败;
     */
    public function create(array $params = []) {
        $params = array_fetch($params, 'name', 'type');
        $model = $this->DB()->getTable('ms_item');
        $model->setData($params);
        return $model->save();
    }

    /**
     * 修改道具
     * @param array $params
     * @return mixed false: 保存失败;
     */
    public function edit(int $id, array $params = []) {
        $params = array_fetch($params, 'name', 'type');
        $model = $this->DB()->getTable('ms_item');
        if (!$model->load($id)) {
            return false;
        }
        $model->setData($params);
        return $model->save();
    }

    /**
     * 设置道具状态
     */
    public function setStatus(int $id, $status) {
        if (!in_array($status, [-1, 0, 1])) {
            return false;
        }
        $table = $this->DB()->getTable('ms_item');
        if (!$table->load($id)) {
            return false;
        }
        $table->setData('status', $status);
        return $table->save();
    }

    /**
     * 启用/禁用道具
     * @param int $id
     * @param bool $active
     */
    public function active(int $id, bool $active) {
        return $this->setStatus($id, $active == true ? 1 : 0);
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
        $this->DB()->trancation(function($db, &$rollback) use($id){
            $select = $db->newSelect('ms_item');
            $select->whereIn('id', $id);
            $select->setData('status', -1);
            $db->exec($select->updateSql());
            //移除和商品的关联关系
            $db->exec($db->newSelect('ms_map_item_goods')->whereIn('item_id', $id)->deleteSql());
        });
        
        return true;
    }

}
