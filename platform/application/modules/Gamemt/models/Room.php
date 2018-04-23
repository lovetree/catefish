<?php
namespace Gamemt;
class RoomModel extends \BaseModel {
	/**
     * 获取列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
	public function lists(int $page = 1, int $pagesize = 15, array $args = [],$type = 3): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_gm_room')
            ->join('ms_gm_gametype as gt', 'gt.id = main_table.game_type')
            ->join('ms_gm_gamemode as gm', 'gm.id = main_table.game_mode')
			->select('main_table.id')
			->select('room_name')
			->select('gt.game_name as type_name', true)
			->select('gm.mode_name as mode_name', true)
            ->select('game_type')
            ->select('game_mode')
			->select('minchip')
            ->select('maxchip')
            ->select('percent')
            ->select('broadcastgold')
            ->select('currentstock')
            ->select('stock_limit_down')
            ->select('stock')
            ->select('robot_switch')
            ->select('robot_num');
        $select = $select->whereNot('main_table.status', -1);

        if (is_array($args) && isset($args['filters'])) {
            //wehre
            foreach($args['filters'] as $filter=>$val){
                $select->whereLike($filter, '%' . $val . '%');
            }
        }
        if($type==1){
            $select = $select->whereIn('game_type',[40,41]);
        }else{
            $select = $select->where('game_type',$type);
        }


        $data = $db->fetchAllPage($select, $page, $pagesize);
//        $total = 0;
        $arr = $data['list']->toArray() ;

        $data['data'] = $arr;
//        $data['totals'] = $total;
        unset($data['list']);
        return $data;
    }

	/**
     * 创建
     * @param array $params
     * @return mixed false: 保存失败;
     */
    public function create(array $params = []) {
        $params = array_fetch($params, 'id','room_name','game_type','game_mode','tax_rate','base_score','gold_min',
            'top_red','blind_min','blind_max', 'base_num', 'dyn_ratio',
            'minchip', 'maxchip', 'percent', 'broadcastgold',
            'stock_limit_up','stock_limit_down','stock', 'water_ratio','robot_switch', 'robot_num', 'recycle_ratio');

        $model = $this->DB()->getTable('ms_gm_room');
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
        $params = array_fetch($params, 'id','room_name','game_type','game_mode','tax_rate','base_score','gold_min',
            'top_red','blind_min','blind_max', 'base_num', 'dyn_ratio',
            'minchip', 'maxchip', 'percent', 'broadcastgold','currentstock',
            'stock_limit_up','stock_limit_down','stock','water_ratio','robot_switch', 'robot_num', 'recycle_ratio');
        $model = $this->DB()->getTable('ms_gm_room');
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
        
        $select = $this->DB()->newSelect('ms_gm_room');
        $select->whereIn('id', $id);
        $select->setData('status', -1);
        
        if($this->DB()->exec($select->updateSql())){
            return true;
        }else{
            return false;
        }
    }
}