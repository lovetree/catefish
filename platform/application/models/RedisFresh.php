<?php
/**
 * redis刷新类
 * User: jim
 * Date: 2017/3/29 0029
 * Time: 10:40
 */

class RedisFreshModel {
    /**
     * 刷新活动
     */
    public static  function refreshActivity(){
        $db = Yaf\Registry::get('__DB__')();
        $select = $db->newSelect('ms_active')
            ->select('title, concat("http://'.$_SERVER['HTTP_HOST'].'", image) as url, a_sort as "order", game_type, game_mode', true)
            ->where('status', 1);
        $result = $db->search($select->toString());

        $dataArr = ["list"=>$result, "version"=>1];
        if($result !== false){
            $redis = PRedis::instance();
            $redis->select(R_MESSAGE_DB);
            if($redis->exists(RK_ACTIVITY)){
                $oriDataArr = json_decode($redis->get(RK_ACTIVITY), true);
                $dataArr["version"] = $oriDataArr["version"] + 1;
            }

            $redis->set(RK_ACTIVITY, json_encode($dataArr, JSON_UNESCAPED_UNICODE));
            return true;
        }

        return false;
    }

    public static function refreshRoomList(){
        //redis数据修改
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);

        $model = new \Gamemt\RoomModel();
        $result = $model->lists(1, 100);

        foreach ($result['data'] as $data){
            $key = 'config_'.$data['game_type'] . '_'. $data['game_mode'];
            $redis->hSet($key, 'minchip', $data['minchip']);
            $redis->hSet($key, 'maxchip', $data['maxchip']);
            $redis->hSet($key, 'percent', $data['percent']);
            $redis->hSet($key, 'broadcastgold', $data['broadcastgold']);
            $redis->hSet($key, 'stockMin', $data['stock_limit_down']);
            $redis->hSet($key, 'stockMax', $data['stock_limit_up']);
//            $redis->hSet($key, 'stock', $data['stock']);
            $redis->hSet($key, 'stock', $data['stock']);
            $redis->hSet($key, 'needrobot', $data['robot_switch']);
            $redis->hSet($key, 'robotcount', $data['robot_num']);

        }

        return true;
    }

    public static function refreshSysconfig($hashKey, $hashVal){
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);

        return $redis->hSet(RK_SYS_CONFIG, $hashKey, $hashVal);
    }

}