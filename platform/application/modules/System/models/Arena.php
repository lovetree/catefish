<?php

namespace System;

class ArenaModel extends \BaseModel {

    /**
     * 获取竞技场列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
    public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_match_rule')
                ->joinLeft('ms_gm_gamemode as model','model.id=main_table.game_mode')
                ->joinLeft('ms_gm_gametype as type','type.id=main_table.game_type')
                ->select(['main_table.id', 'main_table.name', 'main_table.match_start_time', 'main_table.match_end_time','main_table.game_type','main_table.game_mode',
                    'main_table.is_loop', 'main_table.date','main_table.match_number','main_table.rematch_times','main_table.bullet_number',
                    'main_table.first_match_fee','main_table.repeat_match_fee','main_table.effect','main_table.status','main_table.award','main_table.create_time'])
                ->select('model.mode_name as model_name' , true)
                ->select('type.game_name as type_name' , true);
        $select->whereNot("main_table.effect",-1);
        if (is_array($args)) {
            //wehre
            if (isset($args['filters']['name'])) {
                $select->whereLike('main_table.name', '%' . $args['filters']['name'] . '%');
            }

            if (isset($args['filters']['gamemode'])) {
                $select->where('main_table.game_mode', $args['filters']['gamemode']);
            }
        }

        $data = $db->fetchAllPage($select, $page, $pagesize);
        $data['list'] = $data['list']->toArray();
        return $data;
    }

    /**
     * 创建竞技场
     * @param array $params
     * @return mixed false: 保存失败;
     */
    public function create(array $params = []) {
        $params = array_fetch($params,'name', 'type', 'match_start_time', 'match_end_time',
            'is_loop', 'date','match_number','rematch_times','bullet_number','game_mode','game_type',
            'first_match_fee','repeat_match_fee','effect','award');
        $params['status']=0;
        $params['is_loop']=1;
        $params['createtime']=time();
        $model = $this->DB()->getTable('ms_match_rule');
        $model->setData($params);
        $status = $model->save();
        if (!$status) {
            return false;
        }
        return true;
    }

    /**
     * 设置显示不显示
     */
    public function setShow(int $id, $isshow) {
        if (!in_array($isshow, [-1, 0, 1])) {
            return false;
        }
        $params['effect']=$isshow;
        $params['updatetime']=time();
        $table = $this->DB()->getTable('ms_match_rule');
        if (!$table->load($id)) {
            return false;
        }
        $table->setData($params);
        return $table->save();
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
            $select = $db->newSelect('ms_match_rule');
            $select->whereIn('id', $id);
            $select->setData('effect', -1);
            $db->exec($select->updateSql());
        });

        return true;
    }

}
