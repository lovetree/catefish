<?php

class GameController extends \BaseController {

    private $rank_count = 20;
    /**
     * 获取排行榜
     */
    public function ranklistAction() {
        $input = $this->input();
        //验证参数
        if (!$this->validation($input->json_stream(), array(
                    'u_type' => 'inside:0:1:2:3:4'
                ))) {
            return false;
        }
        
        $game = new GameModel();
        $type = $input->json_stream('u_type');
        switch ($type) {
            case 1:
                $data = $game->getWinnerRanklist(0, $this->rank_count);
                break;
            case 2:
                //钻石
                $data = $game->getCreditRanklist(0, $this->rank_count);
                break;
            case 3:
                $data = $game->getEmeraldRanklist(0, $this->rank_count);
                //绿宝石
                break;
            case 4:
                //人气
                $data = $game->getPopularityRanklist(0, $this->rank_count);
                break;
            default:
                $data = $game->getGoldRanklist(0, $this->rank_count);
                break;
        }

        if (false === $data) {
            return $this->failed('服务器繁忙', RCODE_BUSY);
        }
        return $this->succ(['u_type'=>intval($type),'list'=>$data]);
    }

    /**
     * 获取游戏列表
     */
    public function listAction() {        
        $game = new GameModel();
        $data = $game->getGameList();
        return $this->succ($data);
    }
    
    /**
     * 获取广播列表
     * 传递该时间内符合条件的广播
     * @source
     */
    public function broadcastAction() {
        $input = $this->input();
        //验证参数
        if (!$this->validation($input->json_stream(), array(
                    'source' => 'inside:0:1',
                ))) {
            return false;
        }
        $game = new GameModel();
        $data = $game->getBroadcastList($input->json_stream('source'));
        return $this->succ($data);
    }
    
    /**
     * 获取公告列表
     * 需要传递上一次的id号，传送公告列表
     */
    public function noticesAction() {   
        $input = $this->input();
        //验证参数
        if (!$this->validation($input->json_stream(), array(
                    'last_id' => 'number|min:0',
                    'length' => 'number|min:1|max:20',
                ))) {
            return false;
        }
        
        $last_id = $input->json_stream('last_id');
        if(is_null($last_id)){
            $last_id = 0;
        }
        $length = $input->json_stream('length');
        if(is_null($length)){
            $length = 10;
        }
        
        $game = new GameModel();
        $data = $game->getNoticeList($last_id, $length);
        return $this->succ($data);
    }

    /**
     * 获取轮播图列表
     */
    public function carouselAction(){
        $input = $this->input();
        //验证参数
        if (!$this->validation($input->json_stream(), array(
            'length' => 'number|min:1|max:20',
            'version' => 'number|min:1'
        ))) {
            return false;
        }

        //图片数量
        $length = $input->json_stream('length');
        if(is_null($length)){
            $length = 5;
        }
        //数据版本
        $version = $input->json_stream('version');
        if(is_null($version)){
            $version = 1;
        }

        $game = new GameModel();
        $data = $game->getCarouselList($version, $length);
        return $this->succ($data);
    }

    /**
     * 获取活动
     */
    public function activityAction(){
        $game = new GameModel();
        $data = $game->getActivity();
        return $this->succ($data);
    }

    /**
     * 充值
     */
    public function rechargeAction(){
        $input = $this->input();
        //验证参数
        if (!$this->validation($input->json_stream(), array(
            'amount' => 'required|number',
        ))) {
            return false;
        }

        //先存数据库
        if(!$this->DB()->insert('ms_recharge_log', [
            'recharge_time' => time(),
            'order_amount' =>$input->json_stream('amount'),
            'payed_amount' => $input->json_stream('amount'),
            'recharge_type' => $input->json_stream('type'),
            'gold_recharge' => $input->json_stream('amount'),
            'order_flow' => $_SESSION['user_id'] . date('ymdhis'),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id'=>$_SESSION['user_id']
        ])){
            return $this->failed('服务器繁忙', RCODE_BUSY);
        };

        $userData = new Data\UserModel($_SESSION['user_id']);
        $result = $userData->addGold($input->json_stream('amount') * 100);

        if (false === $result) {
            return $this->failed('服务器繁忙', RCODE_BUSY);
        }

        return $this->succ(['gold' => $userData->get('gold')]);

    }

    /**
     * 兑换
     */
    public function exchangeAction(){
        $input = $this->input();
        //验证参数
        if (!$this->validation($input->json_stream(), array(
            'amount' => 'required|number',
            'type' => 'required',
        ))) {
            return false;
        }

        $game = new GameModel();
        $result = $game->addExchangeLog($_SESSION['user_id'], $input->json_stream('amount'), $input->json_stream('type'));
        if(!$result){
            return $this->failed('服务器繁忙', RCODE_BUSY);
        }

        return $this->succ();
    }

    /**
     * 兑换记录
     */
    public function exchangeLogAction(){
        $game = new GameModel();
        $data = $game->getExchangeLog($_SESSION['user_id']);

        return $this->succ($data);
    }

    /**
     * 房间列表
     */
    public function roomlistAction(){
        $game = new GameModel();

        if(!$data = $game->getRoomlist()){
            return $this->failed('服务器繁忙', RCODE_BUSY);
        }

        return $this->succ(["list"=>$data]);
    }

    /*临时添加 将所有redis用户添加u_safe_gold属性
     * */

    public function addsafegAction(){
        $game = new GameModel();
        $game->addsafeg();
    }
    
    /**
     * 202活动列表
     */
    public function activeAction(){
        $game = new GameModel();
        $data = $game->get202Activity();
        return $this->succ($data);
    }

    /**
     * 绑定代理
     */
    public function bindAgentAction(){
        $input = $this->input();
        //验证参数
        if (!$this->validation($input->json_stream(), array(
            'agent_id' => 'required',
        ))) {
            return false;
        }

        try{
            $game = new GameModel();
            $times = $game->bindAgent($_SESSION['user_id'], $input->json_stream('agent_id'));
        }catch (\Exception $e){
                logMessage($e->getMessage(), LOG_ERR);
                return $this->failed($e->getMessage());
        }

        return $this->succ(['remain_up_times'=>$times, 'agent_id'=>$input->json_stream('agent_id')]);
    }
}
