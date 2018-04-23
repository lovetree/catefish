<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 2017/6/2
 * Time: 21:37
 */

class GoldModel extends BaseModel {
    /**
     * 记录金币流水
     * @param $dataArr ['user_id'=>'', 'gold_change'=>'', 'gold_after'=>, 'type'=>'', 'created_at'=>'']
     */
    static function log($dataArr,$db){
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $res = $redis->lPush(RK_GL_GOLD_LOG, serialize($dataArr)); //失败返回false
        if($res!==false){
       	    $data = [
                'create_time'=>$dataArr['created_at']
            ];
            unset($dataArr['created_at']);
	        $newData = array_merge($dataArr,$data);
	        if(!$db->insert('ms_gold_log',$newData)){
	        	return false;
	        }
        }else{
        	return false;
        }

    }
    static function emerald($dataArr,$db){
        if(!$db->insert('ms_emerald_log',$dataArr)){
            return false;
        }
    }
    static function renqi($dataArr,$db){
        if(!$db->insert('ms_renqi_log',$dataArr)){
            return false;
        }
    }
}