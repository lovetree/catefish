<?php
/**
 * Created by PhpStorm.
 * User: grace
 * Date: 2017/6/14
 * Time: 9:06
 */
require_once __DIR__ . '/init.php';
$redis = PRedis::instance();
$redis->select(R_GAME_DB);
//数据库
$db = new Dao\Connection(Yaf\Application::app()->getConfig()->get('db'));
var_dump(sendbroad());
exit;
function sendbroad(){
    global $db;
    $data = array();
    //查询满足时间段内的有效广播
    $sql = 'select * from ms_game_broadcast WHERE status = 1 and start_time <='.time().' and end_time >='.time();
    $broadcast = $db->search($sql);
    if(count($broadcast)>0){
        foreach ($broadcast as $item){
            if($item['intervals']!='0'){//如果存在间隔时间
                $is_interval = checkIntervals($item['id'],$item['intervals']);
                if($is_interval){
                    if($item['times']==='0'){//如果无限次播放
                        $data[] = array(
                            'content'=>$item['content'],
                            'source'=>$item['source'],
                            'id'=>$item['id']

                        );
                    }else{//否则用redis记录次数
                        $is_times = checkIsTime($item['id'],$item['times']);
                        if($is_times){
                            $data[] = array(
                                'content'=>$item['content'],
                                'source'=>$item['source'],
                                'id'=>$item['id']
                            );
                        }
                    }
                }
            }


        }
    }
    $result = ['content'=>$data,'type'=>'lbroadcast'];
    $content = json_encode($result , JSON_UNESCAPED_UNICODE);
    $url = Yaf\Application::app()->getConfig()->get('push.ip') . "/index?cmd=broadcast&phpdata=$content";
    $res = http_get_request($url);

    return $res;
}

/**
 * @param $id
 * @param $intervals
 * @return bool
 * 确认是否到了间隔时间
 */
function checkIntervals($id,$intervals){
    global $redis;
    global $db;
    $had_int = $redis->hGet('broadcast_interval',$id);
    if(!$had_int){
        $redis->hSet('broadcast_interval',$id,time());
        return true;
    }elseif ($had_int+$intervals<time()){//间隔时间已到
        $redis->hSet('broadcast_interval',$id,time());
        return true;
    }else{//次数发完 下线广播 删除相应redis

        return false;
    }
}

/**
 * @param $id
 * @param $all_times
 * 验证是否在有效次数内
 */
function checkIsTime($id,$all_times){
    global $redis;
    global $db;
    $times = $redis->hGet('broadcast_times',$id);
    if(!$times){
        $redis->hSet('broadcast_times',$id,1);
        return true;
    }elseif ($times<$all_times){//次数没有发完 次数+1
        $redis->hSet('broadcast_times',$id,$times+1);
        return true;
    }else{//次数发完 下线广播 删除相应redis
        $sql = 'update ms_game_broadcast set status = 0 WHERE  id='.$id;
        $db->exec($sql);
        $redis->hDel('broadcast_times',$id);
        $redis->hDel('broadcast_interval',$id);
        return false;
    }

}


/**
 * get请求
 */
function http_get_request($url, $param = NULL, $header = NULL) {
    if(!empty($param)) {
        if(strpos($url, "?") ===false) $url .= "?".http_build_query($param);
        else $url .= "&".http_build_query($param);
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    if(is_array($header)) curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    $res = curl_exec( $ch );
    curl_close( $ch );
    return $res;
}