<?php

namespace System;

class GoodsModel extends \BaseModel {

    /**
     * 获取商品列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
    public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_goods')
                ->select(['main_table.id', 'main_table.name', 'main_table.type', 'main_table.desc', 'main_table.aibei_waresid',
                    'main_table.show_name', 'main_table.total_price', 'main_table.img_url', 'main_table.worth', 'main_table.source'])
                ->select(['main_table.status',
                     'main_table.created_time'])
                ->select('FROM_UNIXTIME(main_table.end_time,\'%Y-%m-%d %H:%i:%s\') as end_time', true)
                ->select('FROM_UNIXTIME(main_table.start_time,\'%Y-%m-%d %H:%i:%s\') as start_time', true)
                ->whereNot('main_table.status', -1)
                ->order('main_table.id', 'desc');

        if (is_array($args)) {
            //wehre
            if (isset($args['filters']['name'])) {
                $select->whereLike('main_table.name', '%' . $args['filters']['name'] . '%');
            }
        }

        $data = $db->fetchAllPage($select, $page, $pagesize);

        return $data;
    }

    /**
     * 创建商品
     * @param array $params
     * @return mixed false: 保存失败;
     */
    public function create(array $params = []) {
        $params = array_fetch($params, 'name', 'type','show_name','source', 'desc', 'total_price', 'img_url', 'start_time', 'end_time', 'img_url', 'items', 'worth', 'aibei_waresid');
        $model = $this->DB()->getTable('ms_goods');
        $model->setData($params);
        $status = $model->save();
        if (!$status) {
            return false;
        }
        if (isset($params['items'])) {
            $this->linkItem($model->getPrimary(), $params['items']);
        }
        return true;
    }

    /**
     * 修改商品
     * @param array $params
     * @return mixed false: 保存失败;
     */
    public function edit(int $id, array $params = []) {
        $params = array_fetch($params, 'name', 'show_name', 'type', 'source','desc', 'total_price', 'img_url', 'start_time', 'end_time', 'img_url', 'items', 'worth', 'aibei_waresid');
        $model = $this->DB()->getTable('ms_goods');
        if (!$model->load($id)) {
            return false;
        }
        $model->setData($params);
        $model->setData('version', ['version + 1']);
        $status = $model->save();
        if (!$status) {
            return false;
        }
        if (isset($params['items'])) {
            $this->linkItem($model->getPrimary(), $params['items']);
        }
        return true;
    }

    /**
     * 连接道具
     * @param int $goods_id
     * @param array $items
     */
    protected function linkItem(int $goods_id, array $items) {
        $data = [];
        foreach ($items as $one) {
            if(!isset($one['item_id']) || !isset($one['item_count'])){
                continue;
            }
            $tmp = [$goods_id, $one['item_id'], $one['item_count']];
            array_push($data, $tmp);
        }
        return $this->DB()->trancation(function($db, &$rollback) use($goods_id, $data) {
                    $map = $db->newSelect('ms_map_item_goods');
                    //删除原有关联
                    $db->exec($map->whereIn('goods_id', [$goods_id])->deleteSql());
                    //增加新关联
                    if (!empty($data)) {
                        $status = $db->exec($map->insert(['goods_id', 'item_id', 'item_count'], $data));
                        if (!$status) {
                            $rollback = true;
                        }
                    }
                    return true;
                });
    }

    /**
     * 设置商品状态
     */
    public function setStatus(int $id, $status) {
        if (!in_array($status, [-1, 0, 1])) {
            return false;
        }
        $table = $this->DB()->getTable('ms_goods');
        if (!$table->load($id)) {
            return false;
        }
        $table->setData('status', $status);
        return $table->save();
    }

    /**
     * 启用/禁用商品
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
        $this->DB()->trancation(function($db, &$rollback) use($id) {
            $select = $db->newSelect('ms_goods');
            $select->whereIn('id', $id);
            $select->setData('status', -1);
            $db->exec($select->updateSql());
            //移除和商品的关联关系
            $db->exec($db->newSelect('ms_map_item_goods')->whereIn('goods_id', $id)->deleteSql());
        });

        return true;
    }

}
