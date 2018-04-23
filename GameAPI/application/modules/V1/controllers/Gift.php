<?php
/**
 * 礼物
 */

class GiftController extends \BaseController {
    
    public function testAction() {
        $user_id = $_GET['id'];
        $data = [];
        $data['push_type'] = 'gift'; //
        $data['data'] = [
            'g_type' => 10001
        ];
        
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        $content = $json;
        $url = config_item('push.ip')."/phppushtouser?userid=$user_id&phpdata=$content";
        //$url = "http://moshen.cn/abcd.php?userid=$user_id&phpdata=$content";
        $weixin = new WeixinModel();
        $res = $weixin->http_get_request($url);
        var_dump($res);
    }
    
    public function infoAction() {
        $gift = new GiftModel();
        $data = $gift->info();
        return $this->succ($data);
    }
    
    /**
     * 获取礼物列表
     */
    public function giftAction() {
        $input = $this->input();
        $rule = array(
            't_uid'  => 'required|positive_number'
        );
        
        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }
        
        $gift = new GiftModel();
        $data = $gift->gift();
        if ($data){
            array_walk($data, function(&$item){
                $info = json_decode($item, TRUE);
                $item = [];
                $item['name'] = $info['name'];
                $item['g_type'] = $info['g_type'];
                $item['popularity'] = $info['popularity'];
                $item['gold'] = $info['gold'];
                $item = json_encode($item);
            });
        }
        //获取对方的信息
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $userInfo = $redis->hMget(RK_USER_INFO . $input->json_stream('t_uid'), ['popularity']);
        
        $return = [];
        $return['list'] = $data;
        $return['popularity'] = intval($userInfo['popularity']);
        return $this->succ($return);
    }
    
    public function giveAction() {
        $input = $this->input();
        $rule = array(
            'touid'  => 'required|positive_number',
            'g_type'  => 'required|positive_number',
            'count'  => 'required|positive_number'
        );
        
        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }
        $gift = new GiftModel();
        $res = $gift->giveGift($input->json_stream());
        if (is_array($res)){
            return $this->succ($res);
        }
        
        return $this->failed($res);
    }
    
    /**
     * 兑换
     */
    public function exchangeAction() {
        $input = $this->input();
        $rule = array(
            'popularity'  => 'required|positive_number',
        );
        
        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }
        $popularity = $input->json_stream('popularity');
        
        $gift = new GiftModel();
        $res = $gift->exchange($popularity);
        if ($res === TRUE){
            $user = new UserModel();
            return $this->succ($user->getInfoByRedis(['gold', 'credit', 'emerald', 'popularity']));
        }
        return $this->failed($res);
    }
    
    /**
     * 获取礼物的记录
     */
    public function recordAction() {
        $input = $this->input();
        $rule = array(
            'r_type'  => 'required|positive_number',
        );
        
        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }
        
        $gift = new GiftModel();
        $res = $gift->getRecord($input->json_stream('r_type'));
        return $this->succ($res);
    }
    
    /**
     * 获取得到未发送礼物的记录
     */
    public function nosendAction() {
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $res = $redis->hMget(PK_GIFT_NOSEND, [$_SESSION['user_id']]);
        $redis->hDel(PK_GIFT_NOSEND, $_SESSION['user_id']);
        return $this->succ($res[$_SESSION['user_id']] ? json_decode($res[$_SESSION['user_id']], TRUE) : []);
    }


    
}
