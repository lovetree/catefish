<?php

namespace System;

class MediaModel extends \BaseModel {

    /**
     * 获取列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
    public function _list(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_media')
                ->select('type')
                ->select('size')
                ->select('name')
                ->select('url')
                ->order('id', 'desc');
        
        $data = $db->fetchAllPage($select, $page, $pagesize);

        $data['list'] = $data['list']->toArray();
        return $data['list'];
    }
    
    public function _count(){
        $select = $this->DB()->newSelect('ms_media');
        return $this->DB()->rowCount($select);
    }
    
    /**
     * 创建
     * @param array $params
     * @return mixed false: 保存失败;
     */
    public function _create(array $params = []) {
        $params = array_fetch($params, ['type','size','name','url','create_time']);
        $model = $this->DB()->getTable('ms_media');
        $model->setData($params);
        $status = $model->save();
        if (!$status) {
            return false;
        }
        return true;
    }
}
