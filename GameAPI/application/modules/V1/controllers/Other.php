<?php

class OtherController extends \BaseController {
    
    /**
     * 获取广播列表
     * 传递该时间内符合条件的广播
     * @source
     */
    public function broadcastAction() {
        $input = $this->input();
        //验证参数
        if (!$this->validation($input->json_stream(), array(
                    'source' => 'inside:0:1:2:3:4:5:6:7:8',
                ))) {
            return false;
        }
        $other = new OtherModel();
        $data = $other->getBroadcastList($input->json_stream('source'));
        return $this->succ($data);
    }
    
    /**
     * 用户发送广播
     * @source
     */
    public function sendBroadcastAction() {
        $input = $this->input();
        //验证参数
        if (!$this->validation($input->json_stream(), array(
                    'content' => 'required|length:50',
                    'b_type' => 'inside:0:1'
                ))) {
            return false;
        }
        
        $other = new OtherModel();
        if ($input->json_stream('b_type') == 1){
            $data = $other->sendBroadcast($input->json_stream('content'), $input->json_stream('b_type'), $input->json_stream('code'));
        }else{
            $data = $other->sendBroadcast($input->json_stream('content'), $input->json_stream('b_type'));
        }
        
        if ($data === true){
            $common = new CommonModel();
            
            //获取金币数
            $redis = PRedis::instance();
            $redis->select(R_GAME_DB);
            $user_info = $redis->hMget(RK_USER_INFO . $_SESSION['user_id'], array('gold', 'nickname'));
            $result = ['gold'=>$user_info['gold'], 'content'=>$user_info['nickname'] . "：" . $common->filterSensitiveWord($input->json_stream('content')),'type'=>'broadcast'];
            $content = json_encode($result , JSON_UNESCAPED_UNICODE);
            $url = $this->config()->get('push.ip') . "/index?cmd=broadcast&phpdata=$content";
            $res = $this->http_get_request($url);
            return $this->succ();
        }
        return $this->failed($data);
    }
    
}
