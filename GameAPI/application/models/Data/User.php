<?php

namespace Data;

class UserModel extends DataModel {

    const KEY_EVALSHA = 'EVALSHA@USER_DATA';
    const KEY_GOLD = 'gold'; //金币
    const KEY_CREDIT = 'credit'; //钻石
    const KEY_TICKET = 'ticket'; //入场券;
    const KEY_EMERALD = 'emerald'; //绿宝石
    const KEY_SAFE_GOLD = 'emerald'; //钱柜金币
    const KEY_POINT = 'point'; //一指赢积分
    
    //道具
    const KEY_FROZEN = 'frozen'; //冰封
    const KEY_EAGLEEYE = 'eagleeye'; //鹰眼
    
    private $_data_key;
    private $_evalsha;
    private $user_id;
    protected $gold;
    protected $credit;
    protected $ticket;
    protected $emerald;
    protected $point;
    //钱柜金币
    protected  $safe_gold;

    public function __construct($user_id) {
        $this->user_id = $user_id;
        $this->_data_key = RK_USER_INFO . $user_id;
        $redis = $this->redis();
        if($redis->exists(self::KEY_EVALSHA)){
            $this->_evalsha = $redis->get(self::KEY_EVALSHA);
        }else{
            throw new \Exception('UserModel init faild. can not find the evalsha');
        }
    }

    protected function redis() {
        $redis = parent::redis();
        $redis->select(R_GAME_DB);
        return $redis;
    }
    
    public function get($name) {
        if (is_array($name)) {
            $arr = $this->redis()->hMget($this->getKey(), $name);
            if ($arr) {
                foreach($arr as $k => $v){
                    $this->$k = $v;
                }
            }
            return $arr;
        } else {
            if (is_null($this->$name)) {
                $this->$name = $this->redis()->hGet($this->getKey(), $name);
            }
            return $this->$name;
        }
        return false;
    }
    
    //修改金币
    public function addGold($value){
        return $this->redis()->evalsha($this->_evalsha, [$this->user_id, self::KEY_GOLD, $value], 2);
    }
    
    //修改钻石
    public function addCredit($value){
        return $this->redis()->evalsha($this->_evalsha, [$this->user_id, self::KEY_CREDIT, $value], 2);
    }
    
    //修改卷
    public function addTicket($value){
        return $this->redis()->evalsha($this->_evalsha, [$this->user_id, self::KEY_TICKET, $value], 2);
    }
    
    //修改绿宝石
    public function addEmerald($value){
        return $this->redis()->evalsha($this->_evalsha, [$this->user_id, self::KEY_EMERALD, $value], 2);
    }
    
    //修改冰封道具
    public function addFrozen($value){
        return $this->redis()->evalsha($this->_evalsha, [$this->user_id, self::KEY_FROZEN, $value], 2);
    }
    
    //修改鹰眼道具
    public function addEagleeye($value){
        return $this->redis()->evalsha($this->_evalsha, [$this->user_id, self::KEY_EAGLEEYE, $value], 2);
    }

    //修改积分
    public function addPoint($value){
        return $this->redis()->evalsha($this->_evalsha, [$this->user_id, self::KEY_POINT, $value], 2);
    }

    //通用修改多种货币记录
    public function addData($params, $values) {
        $keys = [count($params), $this->user_id];
        return $this->redis()->evalsha($this->_evalsha, array_merge($keys, $params, $values), count($params)+2);
    }
    
    public function save(array $data) {
        $this->redis()->hMset($this->getKey(), $data);
    }

    public function getKey() {
        return $this->_data_key;
    }
    
    /**
     * @return array
     */
    public function all(){
        return $this->get([self::KEY_GOLD, self::KEY_CREDIT, self::KEY_TICKET, self::KEY_EMERALD, self::KEY_SAFE_GOLD,self::KEY_POINT]);
        //return $this->get([self::KEY_GOLD, self::KEY_CREDIT, self::KEY_TICKET]);
    }

    /**
     * 获取用户代理
     */
    public function getAgent(){
        $redis = \PRedis::instance();
        $redis->select(R_AGENT_DB);
        $agent = $redis->hget(RK_AL_USER_AGENT . $this->user_id, 'agent_id');
        return $agent ? $agent : '官方';
    }



}
