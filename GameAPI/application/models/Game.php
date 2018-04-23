<?php

class GameModel extends BaseModel {
    const AGENT_UPDATE_TIMES_LIMIT = 2;

    /**
     * 同步金币排行信息
     */
    private function _initGold() {
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $gold_cron = $redis->zRange(RK_RANK_GOLD_CRON, 0, -1, 'WITHSCORES');
        if ($gold_cron){
            $user_ids = [];
            foreach ($gold_cron as $k=>$v){
                $user_ids[] = $k;
            }
            $user_ids = implode(',', $user_ids);
            //查询会员信息
            $sql = <<<SQL
                    select user_id,nickname,avatar from ms_user_info where user_id in($user_ids);
SQL;
            $db = $this->DB();
            $list = $db->fetchAll($sql);
            if ($list){
                $user = [];
                foreach ($list as $v){
                    $user[$v['user_id']] = $v;
                }
                
                foreach ($gold_cron as $k=>$v){
                    $rank = [];
                    $rank['user_id'] = $k;
                    $rank['nickname'] = $user[$k]['nickname'];
                    $rank['amount'] = $v;
                    $rank['avatar'] = $user[$k]['avatar'];
                    $redis->lPush(RK_RANK_GOLD, json_encode($rank));
                }
            }
        }
    }
    
    /**
     * 获取金币排行榜
     */
    public function getGoldRanklist($start, $size) {
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
//        if ($redis->exists(PK_RANK_GOLD_DATE)){
//            $redis->del(PK_RANK_GOLD_DATE);
//            $this->_initGold();
//        }
        
        $rank_list = $redis->lrange(RK_RANK_GOLD, $start, $size);
        if (false === $rank_list) {
            logMessage('找不到金币排行榜数据');
            return array();
        }
        //构建数据
        array_walk($rank_list, function(&$item, $inx) {
            $data = json_decode($item, true);
            $item = [];
            $item['r_inx'] = $inx + 1;
            $item['u_nickname'] = $data['nickname'] ?? 'null';
            $item['u_avatar'] = $data['avatar'] ?? toUrl(DEF_AVATAR);
            $item['u_data'] = $data['amount'] ?? '-1';
        });
        return $rank_list;
    }

    
    /**
     * 获取钻石排行榜
     */
    public function getCreditRanklist($start, $size) {
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        
        $rank_list = $redis->lrange(RK_RANK_CREDIT, $start, $size);
        if (false === $rank_list) {
            logMessage('找不到钻石排行榜数据');
            return array();
        }
        //构建数据
        array_walk($rank_list, function(&$item, $inx) {
            $data = json_decode($item, true);
            $item = [];
            $item['r_inx'] = $inx + 1;
            $item['u_nickname'] = $data['nickname'] ?? 'null';
            $item['u_avatar'] = $data['avatar'] ?? toUrl(DEF_AVATAR);
            $item['u_data'] = $data['amount'] ?? '-1';
        });
        return $rank_list;
    }
    
    /**
     * 获取绿宝石排行榜
     */
    public function getEmeraldRanklist($start, $size) {
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        
        $rank_list = $redis->lrange(RK_RANK_EMERALD, $start, $size);
        if (false === $rank_list) {
            logMessage('找不到绿宝石排行榜数据');
            return array();
        }
        //构建数据
        array_walk($rank_list, function(&$item, $inx) {
            $data = json_decode($item, true);
            $item = [];
            $item['r_inx'] = $inx + 1;
            $item['u_nickname'] = $data['nickname'] ?? 'null';
            $item['u_avatar'] = $data['avatar'] ?? toUrl(DEF_AVATAR);
            $item['u_data'] = $data['amount'] ?? '-1';
        });
        return $rank_list;
    }
    
    /**
     * 获取赢家排行榜
     */
    public function getWinnerRanklist($start, $size) {
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $rank_list = $redis->lrange(RK_RANK_WINNER, $start, $size);
        if (false === $rank_list) {
            logMessage('找不到赢家排行榜数据');
            return array();
        }
        //构建数据
        array_walk($rank_list, function(&$item, $inx) {
            $data = json_decode($item, true);
            $item = [];
            $item['r_inx'] = $inx + 1;
            $item['u_name'] = $data['nickname'] ?? 'null';
            $item['u_data'] = $data['amount'] ?? '-1';
        });
        return $rank_list;
    }

    /**
     * 获取游戏列表
     */
    public function getGameList() {
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $gameinfo = $redis->get(RK_GAME_LIST);
        if (false === $gameinfo) {
            return array();
        }
        $list = json_decode($gameinfo, true);
        if (json_error()) {
            logMessage('json 解析错误: ' . $gameinfo);
            return array();
        }
        $game_list = [];
        foreach ($list as $k => $v) {
            if (is_array($v)) {
                $game_list[] = array(
                    'g_id' => $v['gid'],
                    'g_name' => $v['name'],
                    'g_version' => $v['version'],
                    'playmode' => $v['mode']
                );
            }
        }
        return $game_list;
    }
    
    /**
     * 获取人气排行榜
     */
    public function getPopularityRanklist($start, $size) {
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        //有序集合总，取出排行的前20名
        $rank_list = $redis->zRevRangeByScore(PK_RANK_POPULARITY, '+inf', "(0", 
                array('withscores'=>true, 'limit' => array($start, $size)));
        if (false === $rank_list) {
            logMessage('找不到赢家排行榜数据');
            return array();
        }
        //构建数据
        $inx = 1;
        $user = new UserModel();
        $return = [];
        foreach ($rank_list as $k=>$v){
            $user_info = $redis->hMget(RK_USER_INFO . $k, ['nickname', 'photo']);
            if (!$user_info) continue;
            $return[$k]['r_inx'] = $inx;
            $return[$k]['u_nickname'] = $user_info['nickname'];
            $return[$k]['u_avatar'] = $user_info['photo'] ?? toUrl(DEF_AVATAR);
            $return[$k]['u_data'] = $v;
            $inx++;
        }
        
        return array_values($return);
    }
    /**
     * 获取积分排行榜
     */
    public function getPointRanklist($start, $size) {

        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);

        $rank_list = $redis->lrange(RK_RANK_POINT, $start, $size);
        if (false === $rank_list) {
            logMessage('找不到一指赢积分排行榜数据');
            return array();
        }
        //构建数据
        array_walk($rank_list, function(&$item, $inx) {
            $data = json_decode($item, true);
            $item = [];
            $item['r_inx'] = $inx + 1;
            $item['u_nickname'] = $data['nickname'] ?? 'null';
            $item['u_avatar'] = $data['avatar'] ?? toUrl(DEF_AVATAR);
            $item['u_data'] = $data['amount'] ?? '-1';
        });
        return $rank_list;
    }
    
    /**
     * 获取广播数据
     */
    public function getBroadcastList(int $source) {
        //获取redis中所有符合条件的广播数据
        $data_list = self::_getBroadcastList();
//        if ($data_list){
//            //梳理出source的数据
//            $source_list = [];
//            $del_ids = [];
//            $redis = PRedis::instance();
//            $redis->select(R_MESSAGE_DB);
//            foreach ($data_list as $k=>$v){
//                $message = json_decode($v, TRUE);
//                if (intval($message['end_time']) < time()){
//                    $redis->zRem(RK_BROADCAST_LIST, $v);
//                    $del_ids[] = $message['id'];
//                    unset($data_list[$k]);
//                    continue;
//                }
//
//                if ($message['source'] == $source){
//                    $source_list[] = $message;
//                }
//            }
//
//            if ($source_list){
//                //获取redis自己的发送记录，梳理出能发送的数据，包括间隔，次数
//                $key = RK_USER_BROADCAST . $_SESSION['user_id'];
//                $user_broadcast = $redis->hGet($key, $source);
//                $broadcast = [];
//                if ($user_broadcast){
//                    //数组格式，已id号为键值
//                    $broadcast = json_decode($user_broadcast, $v);
//                    if ($del_ids){
//                        foreach ($del_ids as $v){
//                            if (isset($broadcast[$v])){
//                                unset($broadcast[$v]);
//                            }
//                        }
//                    }
//                }
//                foreach ($source_list as $k=>$v){
//                    if ($broadcast && isset($broadcast[$v['id']])){
//                        if (isset($v['intervals']) && $v['intervals'] > 0){
//                            //发送时间间隔有限制
//                            if (($broadcast[$v['id']]['last_time'] + $v['intervals']) >= time()){
//                                unset($source_list[$k]);
//                                continue;
//                            }
//                            $broadcast[$v['id']]['last_time'] = time();
//                        }
//                        if (isset($v['times']) && $v['times'] > 0){
//                            //发送次数有限制
//                            if ($broadcast[$v['id']]['times'] >= $v['times']){
//                                unset($source_list[$k]);
//                                continue;
//                            }
//                            $broadcast[$v['id']]['times'] ++;
//                        }
//                    }else{
//                        $broadcast[$v['id']]['times'] = 0;
//                        if ($v['times'] > 0){
//                            $broadcast[$v['id']]['times'] = 1;
//                        }
//                        $broadcast[$v['id']]['last_time'] = 0;
//                        if ($v['intervals'] > 0){
//                            $broadcast[$v['id']]['last_time'] = time();
//                        }
//                    }
//                }
//
//                //重新设置
//                $redis->hSet($key, $source, json_encode($broadcast));
//            }
//
//            $return = [];
//            if ($source_list){
//                foreach ($source_list as $v){
//                    $return[] = [
//                        'id' => $v['id'],
//                        'content' => $v['content']
//                    ];
//                }
//            }
//
//            $data_list = $return;
//        }
        
        return $data_list ? $data_list : [];
    }
    
    public function _getBroadcastList() {
        $redis = PRedis::instance();
        $redis->select(R_MESSAGE_DB);
        //score保存的是有效时间，获取time()值以下的都算是能开始广播
        $key = RK_BROADCAST_LIST;
        //判断是否存在
        if (!$redis->exists($key)){
            $data = [
                [
                    'id' => '3',
                    'content' => '欢迎来到游戏中心，祝您玩得愉快！'
                ]
            ];

            $redis->set($key, json_encode($data, JSON_UNESCAPED_UNICODE));
        }

//        $time = time();
//        return $redis->zRangeByScore($key, "(0", "$time");
        return json_decode($redis->get($key), true);
    }
    
    /**
     * 获取公告数据
     */
    public function getNoticeList(int $offset = 0, int $length = 10) {
        $data_list = self::_getNoticeList($offset, $length);
        $last_id = $offset;
        $resultArr = [];
        if ($data_list){
            //去除过期时间的数据
            $url = $this->config()->get('image.ip');
            foreach ($data_list as $k=>$v){
                $message = json_decode($v, TRUE);
                $message['image']= $message['images']=='/files/official.jpg'?'':$url . $message['images'];
                $message['a_type']=$message['type'];
                $resultArr[] = $message;
                $last_id = $message['id'];
            }
        }

        $resultArr = [
            'last_id' => $last_id,
            'list' => $resultArr ? $resultArr : []
        ];

        return $resultArr;
        
//        $list = $this->_getNoticeListByCache();
//        if (!$list) {
//            //缓存中没有，则取DB
//            $db = $this->DB();
//            $sql = 'select id as nid, content, type, comment from ms_game_message where start_time <= ? and end_time >= ? and `status` = ? order by created_date desc';
//            $list = $db->query($sql, array(
//                time(), time(), 1
//            ));
//            if (!$list) {
//                return [];
//            }
//            //存入缓存
//            $redis = PRedis::instance();
//            if ($redis) {
//                $redis->select(R_GAME_DB);
//                $redis->set(RK_NOTICE_LIST, json_encode($list, JSON_UNESCAPED_UNICODE));
//                $redis->expire(RK_NOTICE_LIST, R_NOTICE_TIMEOUT);
//            }
//        }
//
//        //重构数据
//        $data_list = [];
//        foreach ($list as $k => $v) {
//            if (is_array($v)) {
//                $data_list[] = array(
//                    'n_id' => $v['nid'],
//                    'n_content' => $v['content'],
//                    'n_comment' => $v['comment'],
//                    'n_type' => $v['type']
//                );
//            }
//        }
//        return $data_list;
    }
    
    protected function _getNoticeList(int $offset = 0, int $length = 10) {
        $redis = PRedis::instance();
        $redis->select(R_MESSAGE_DB);
        //score保存的是u_id，也就是offset的值，获取大于score的邮件
        $key = RK_NOTICE_LIST;
        //判断是否存在
        if (!$redis->exists($key)){
            $data = [
                ['id'=>1, 'title'=>'奖励翻倍', 'content'=>'国企期间奖励一律翻倍', 'c_time'=>'2017-03-24 11:22:22'],
                ['id'=>2, 'title'=>'放假', 'content'=>'明天一日游', 'c_time'=>'2017-03-22 11:22:22'],
            ];

            $redis->set($key, json_encode($data));
        }
        return $redis->zRangeByScore($key, "($offset", "+inf", array('limit' => array(0, $length)));
        
//        return json_decode($redis->get($key), true);
    }
    
    protected function _getNoticeListByCache() {
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $cache_data = $redis->get(RK_NOTICE_LIST);
        if (false === $cache_data) {
            return false;
        }
        $list = json_decode($cache_data, true);
        if (json_error()) {
            logMessage('json 解析错误: ' . $cache_data);
            return false;
        }
        return $list;
    }

    /**
     * 获取轮播图数据
     */
    public function getCarouselList(int $version = 1, int $length = 5)
    {
        $redis = PRedis::instance();
        $redis->select(R_MESSAGE_DB);
        //score保存的是order
        $key = RK_CAROUSEL_LIST;
        //判断是否存在
        if (!$redis->exists($key)){
            //模拟数据
            $carouselData = [
                'version' => 2,
                'list' => [
                   ['url'=>'http://120.77.56.99:8181/upload/image/carousel1.jpg',
                    'order'=>1,
                       'game_type'=>1,
                       'game_mode'=>1
                    ],
                   [
                       'url'=>'http://120.77.56.99:8181/upload/image/carousel2.jpg',
                       'order'=>2,
                       'game_type'=>13,
                       'game_mode'=>2
                   ]
                ]
            ];

            $redis->set($key, json_encode($carouselData, JSON_UNESCAPED_UNICODE));

        }

        $carouselData = json_decode($redis->get($key), true);
        //比较客户端和服务端版本号，没有更新则不返回数据
        if($carouselData['version'] <= $version){
            $carouselData = [];
        }

        return $carouselData;
    }

    /**
     * 获取活动
     */
    public function getActivity(int $version = 1)
    {
        $redis = PRedis::instance();
        $redis->select(R_MESSAGE_DB);
        $key = RK_ACTIVITY;
        //判断是否存在
        if (!$redis->exists($key)){
            //模拟数据
            $carouselData = [
                'version' => 2,
                'list' => [
                    ['url'=>'http://120.77.56.99:8181/upload/image/activity1.jpg',
                        'order'=>1,
                        'game_type'=>1,
                        'game_mode'=>1,
                        'tpl' => ''
                    ],
                    ['url'=>'http://120.77.56.99:8181/upload/image/activity2.jpg',
                        'order'=>2,
                        'game_type'=>2,
                        'game_mode'=>1,
                        'tpl' => ''
                    ],
                    ['url'=>'http://120.77.56.99:8181/upload/image/activity3.jpg',
                        'order'=>3,
                        'game_type'=>1,
                        'game_mode'=>1,
                        'tpl' => ''
                    ],
                    ['url'=>'http://120.77.56.99:8181/upload/image/activity4.jpg',
                        'order'=>4,
                        'game_type'=>1,
                        'game_mode'=>1,
                        'tpl' => ''
                    ],
                    ['url'=>'http://120.77.56.99:8181/upload/image/activity5.jpg',
                        'order'=>5,
                        'game_type'=>1,
                        'game_mode'=>1,
                        'tpl' => ''
                    ]
                ]
            ];

            $redis->set($key, json_encode($carouselData));
        }

        $activitylData = json_decode($redis->get($key), true);
        //比较客户端和服务端版本号，没有更新则不返回数据
        if($activitylData['version'] <= $version){
            $activitylData = [];
        }

        return $activitylData;
    }

    /**
     * 兑换
     */
    public function addExchangeLog($user_id, $amount, $type='wx'){
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $key = PK_USER_EXLOG . $user_id;

        if($redis->exists($key)){
            $valArr = json_decode($redis->get($key), true);
        }else{
            $valArr = [];
        }

        $flowno = $user_id.date('YmdHis');
        $now = time();
        //新增记录
        $data = [
            'e_flowno' => $flowno,
            'e_date' => date('Y-m-d H:i:s', $now),
            'e_amount' => $amount,
            'e_type' => $type,
            'e_remark'=> ''
        ];

        //先保证mysql成功，再插入redis
        if(!$this->DB()->insert('ms_withdraw_log', [
            'withdraw_flow' => $flowno,
            'withdraw_at' => $now,
            'username' => $redis->hGet(RK_USER_INFO.$user_id, 'username'),
            'nickname' => $redis->hGet(RK_USER_INFO.$user_id, 'nickname'),
            'withdraw_amount' => $amount,
            'withdraw_type' => $type,
        ])){
            return false;
        }

        $valArr[] = $data;

        return $redis->set($key, json_encode($valArr, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 获取兑换记录
     */
    public function getExchangeLog($user_id){
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $key = PK_USER_EXLOG . $user_id;
        //判断是否存在
        if (!$redis->exists($key)){
            $data = [
                'version' => 1,
                'list' => [
                    ['e_flowno'=>'201712923232323',
                    'e_date'=>'2017-03-21 23:23:21',
                    'e_amount'=>'2000',
                    'e_type'=>'wx',
                    'e_remark'=>'xx'],
                    ['e_flowno'=>'20171293232325',
                        'e_date'=>'2017-03-22 23:23:21',
                        'e_amount'=>'2000',
                        'e_type'=>'zfb',
                        'e_remark'=>'xddsx']
                ]
            ];

            $redis->set($key,json_encode($data));
        }

        return json_decode($redis->get($key), true);
    }

    /**
     * 获取房间列表
     */
    public function getRoomlist(){
        $redis = PRedis::instance();
        $redis->select(R_MESSAGE_DB);
        $key = RK_ROOM_LIST;
        //判断是否存在
        if (!$redis->exists($key)){
            //模拟数据
            $data = [
                ['game_name'=>'麻将', 'room_name'=>'新手金币场','game_type'=>1, 'game_mode'=>1,'player_gold'=>10,'play_gold'=>0.5,'tax_rate'=>5, 'player_num'=>13392],
                ['game_name'=>'麻将', 'room_name'=>'初级金币场','game_type'=>1, 'game_mode'=>2,'player_gold'=>20,'play_gold'=>1,'tax_rate'=>5, 'player_num'=>13492],
                ['game_name'=>'麻将', 'room_name'=>'中级金币场','game_type'=>1, 'game_mode'=>3,'player_gold'=>100,'play_gold'=>5,'tax_rate'=>5, 'player_num'=>13292],
                ['game_name'=>'麻将', 'room_name'=>'高级金币场','game_type'=>1, 'game_mode'=>4,'player_gold'=>200,'play_gold'=>10,'tax_rate'=>5, 'player_num'=>13192],
                ['game_name'=>'麻将', 'room_name'=>'贵族金币场','game_type'=>1, 'game_mode'=>5,'player_gold'=>1000,'play_gold'=>50,'tax_rate'=>5, 'player_num'=>12392],
                ['game_name'=>'麻将', 'room_name'=>'皇家级金币场','game_type'=>1, 'game_mode'=>6,'player_gold'=>2000,'play_gold'=>500,'tax_rate'=>5, 'player_num'=>14392],
                 
                ['game_name'=>'牛牛', 'room_name'=>'新手金币场','game_type'=>2, 'game_mode'=>1,'player_gold'=>1000,'play_gold'=>10,'tax_rate'=>20, 'player_num'=>13392],
                ['game_name'=>'牛牛', 'room_name'=>'初级金币场','game_type'=>2, 'game_mode'=>2,'player_gold'=>3000,'play_gold'=>50,'tax_rate'=>20, 'player_num'=>13492],
                ['game_name'=>'牛牛', 'room_name'=>'中级金币场','game_type'=>2, 'game_mode'=>3,'player_gold'=>5000,'play_gold'=>500,'tax_rate'=>20, 'player_num'=>13292],
                ['game_name'=>'牛牛', 'room_name'=>'高级金币场','game_type'=>2, 'game_mode'=>4,'player_gold'=>7000,'play_gold'=>1000,'tax_rate'=>20, 'player_num'=>13192],
                ['game_name'=>'牛牛', 'room_name'=>'贵族金币场','game_type'=>2, 'game_mode'=>5,'player_gold'=>9000,'play_gold'=>50,'tax_rate'=>20, 'player_num'=>12392],
                ['game_name'=>'牛牛', 'room_name'=>'皇家级金币场','game_type'=>2, 'game_mode'=>6,'player_gold'=>11000,'play_gold'=>500,'tax_rate'=>20, 'player_num'=>14392],
               
                ['game_name'=>'炸金花', 'room_name'=>'新手金币场','game_type'=>13, 'game_mode'=>1,'player_gold'=>1000,'play_gold'=>10,'tax_rate'=>20, 'player_num'=>13392],
                ['game_name'=>'炸金花', 'room_name'=>'初级金币场','game_type'=>13, 'game_mode'=>2,'player_gold'=>3000,'play_gold'=>50,'tax_rate'=>20, 'player_num'=>13492],
                ['game_name'=>'炸金花', 'room_name'=>'中级金币场','game_type'=>13, 'game_mode'=>3,'player_gold'=>5000,'play_gold'=>500,'tax_rate'=>20, 'player_num'=>13292],
                ['game_name'=>'炸金花', 'room_name'=>'高级金币场','game_type'=>13, 'game_mode'=>4,'player_gold'=>7000,'play_gold'=>1000,'tax_rate'=>20, 'player_num'=>13192],
                ['game_name'=>'炸金花', 'room_name'=>'贵族金币场','game_type'=>13, 'game_mode'=>5,'player_gold'=>9000,'play_gold'=>50,'tax_rate'=>20, 'player_num'=>12392],
                ['game_name'=>'炸金花', 'room_name'=>'皇家级金币场','game_type'=>13, 'game_mode'=>6,'player_gold'=>11000,'play_gold'=>500,'tax_rate'=>20, 'player_num'=>14392],
              
                ['game_name'=>'斗地主', 'room_name'=>'新手金币场','game_type'=>14, 'game_mode'=>1,'player_gold'=>10,'play_gold'=>0.5,'tax_rate'=>5, 'player_num'=>13392],
                ['game_name'=>'斗地主', 'room_name'=>'初级金币场','game_type'=>14, 'game_mode'=>2,'player_gold'=>40,'play_gold'=>2,'tax_rate'=>5, 'player_num'=>13492],
                ['game_name'=>'斗地主', 'room_name'=>'中级金币场','game_type'=>14, 'game_mode'=>3,'player_gold'=>200,'play_gold'=>10,'tax_rate'=>5, 'player_num'=>13292],
                ['game_name'=>'斗地主', 'room_name'=>'高级金币场','game_type'=>14, 'game_mode'=>4,'player_gold'=>1000,'play_gold'=>50,'tax_rate'=>5, 'player_num'=>13192],
                ['game_name'=>'斗地主', 'room_name'=>'贵族金币场','game_type'=>14, 'game_mode'=>5,'player_gold'=>4000,'play_gold'=>200,'tax_rate'=>5, 'player_num'=>12392],
                ['game_name'=>'斗地主', 'room_name'=>'皇家级金币场','game_type'=>14, 'game_mode'=>6,'player_gold'=>10000,'play_gold'=>500,'tax_rate'=>5, 'player_num'=>14392],
                
                ['game_name'=>'梭哈', 'room_name'=>'新手金币场','game_type'=>15, 'game_mode'=>1,'player_gold'=>1000,'play_gold'=>10,'tax_rate'=>20, 'player_num'=>13392],
                ['game_name'=>'梭哈', 'room_name'=>'初级金币场','game_type'=>15, 'game_mode'=>2,'player_gold'=>3000,'play_gold'=>50,'tax_rate'=>20, 'player_num'=>13492],
                ['game_name'=>'梭哈', 'room_name'=>'中级金币场','game_type'=>15, 'game_mode'=>3,'player_gold'=>5000,'play_gold'=>500,'tax_rate'=>20, 'player_num'=>13292],
                ['game_name'=>'梭哈', 'room_name'=>'高级金币场','game_type'=>15, 'game_mode'=>4,'player_gold'=>7000,'play_gold'=>1000,'tax_rate'=>20, 'player_num'=>13192],
                ['game_name'=>'梭哈', 'room_name'=>'贵族金币场','game_type'=>15, 'game_mode'=>5,'player_gold'=>9000,'play_gold'=>50,'tax_rate'=>20, 'player_num'=>12392],
                ['game_name'=>'梭哈', 'room_name'=>'皇家级金币场','game_type'=>15, 'game_mode'=>6,'player_gold'=>11000,'play_gold'=>500,'tax_rate'=>20, 'player_num'=>14392],
             
                ['game_name'=>'德州扑克', 'room_name'=>'新手金币场','game_type'=>16, 'game_mode'=>1,'player_gold'=>1000,'play_gold'=>10,'tax_rate'=>20, 'player_num'=>13392],
                ['game_name'=>'德州扑克', 'room_name'=>'初级金币场','game_type'=>16, 'game_mode'=>2,'player_gold'=>3000,'play_gold'=>50,'tax_rate'=>20, 'player_num'=>13492],
                ['game_name'=>'德州扑克', 'room_name'=>'中级金币场','game_type'=>16, 'game_mode'=>3,'player_gold'=>5000,'play_gold'=>500,'tax_rate'=>20, 'player_num'=>13292],
                ['game_name'=>'德州扑克', 'room_name'=>'高级金币场','game_type'=>16, 'game_mode'=>4,'player_gold'=>7000,'play_gold'=>1000,'tax_rate'=>20, 'player_num'=>13192],
                ['game_name'=>'德州扑克', 'room_name'=>'贵族金币场','game_type'=>16, 'game_mode'=>5,'player_gold'=>9000,'play_gold'=>50,'tax_rate'=>20, 'player_num'=>12392],
                ['game_name'=>'德州扑克', 'room_name'=>'皇家级金币场','game_type'=>16, 'game_mode'=>6,'player_gold'=>11000,'play_gold'=>500,'tax_rate'=>20, 'player_num'=>14392],
             
                ['game_name'=>'百家乐', 'room_name'=>'新手金币场','game_type'=>17, 'game_mode'=>1,'player_gold'=>1,'play_gold'=>1,'tax_rate'=>20, 'player_num'=>13392],
                ['game_name'=>'百家乐', 'room_name'=>'初级金币场','game_type'=>17, 'game_mode'=>2,'player_gold'=>2,'play_gold'=>2,'tax_rate'=>20, 'player_num'=>13492],
                ['game_name'=>'百家乐', 'room_name'=>'中级金币场','game_type'=>17, 'game_mode'=>3,'player_gold'=>5,'play_gold'=>5,'tax_rate'=>20, 'player_num'=>13292],
                ['game_name'=>'百家乐', 'room_name'=>'高级金币场','game_type'=>17, 'game_mode'=>4,'player_gold'=>10,'play_gold'=>10,'tax_rate'=>20, 'player_num'=>13192],
                ['game_name'=>'百家乐', 'room_name'=>'贵族金币场','game_type'=>17, 'game_mode'=>5,'player_gold'=>50,'play_gold'=>50,'tax_rate'=>20, 'player_num'=>12392],
                ['game_name'=>'百家乐', 'room_name'=>'皇家级金币场','game_type'=>17, 'game_mode'=>6,'player_gold'=>100,'play_gold'=>100,'tax_rate'=>20, 'player_num'=>14392],
                 
                ['game_name'=>'捕鱼', 'room_name'=>'新手金币场','game_type'=>3, 'game_mode'=>1,'player_gold'=>1000,'play_gold'=>10,'tax_rate'=>20, 'player_num'=>13392],
                ['game_name'=>'捕鱼', 'room_name'=>'初级金币场','game_type'=>3, 'game_mode'=>2,'player_gold'=>3000,'play_gold'=>50,'tax_rate'=>20, 'player_num'=>13492],
                ['game_name'=>'捕鱼', 'room_name'=>'中级金币场','game_type'=>3, 'game_mode'=>3,'player_gold'=>5000,'play_gold'=>500,'tax_rate'=>20, 'player_num'=>13292],
                ['game_name'=>'捕鱼', 'room_name'=>'高级金币场','game_type'=>3, 'game_mode'=>4,'player_gold'=>7000,'play_gold'=>1000,'tax_rate'=>20, 'player_num'=>13192],
                ['game_name'=>'捕鱼', 'room_name'=>'贵族金币场','game_type'=>3, 'game_mode'=>5,'player_gold'=>9000,'play_gold'=>50,'tax_rate'=>20, 'player_num'=>12392],
                ['game_name'=>'捕鱼', 'room_name'=>'皇家级金币场','game_type'=>3, 'game_mode'=>6,'player_gold'=>11000,'play_gold'=>500,'tax_rate'=>20, 'player_num'=>14392]
            ];
            $redis->set($key, json_encode($data));
        }

        //房间列表
        $roomList = json_decode($redis->get($key), true);
        //获取房间人数
        $onlineModel = new OnlineModel();
        $olData = $onlineModel->getOLPlayerNum(count($roomList));

        foreach ($roomList as $key=>$room){
            foreach ($olData as $item){
                if($room['game_type'] == $item['gametype'] && $room['game_mode'] == $item['gamemod']){
                    $roomList[$key]['player_num'] = $room['base_num'] + $item['onlinecount'] * $room['dyn_ratio'];
                    break;
                }
            }
        }

        return $roomList;
    }

    public function addsafeg(){
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $keys= $redis->keys("info@*");
        foreach ($keys as $key){
            $result = $redis->hset($key, 'safe_gold', 0);
        }
    }
    
    public function get202Activity(){
        $time = time();
        $sql = <<<SQL
                select title,image,a_type from ms_activity where status = 1 and start_time <= $time and end_time >= $time order by a_sort ASC
SQL;
        $data = $this->DB()->fetchAll($sql);
        if (!$data) return [];
        foreach ($data as $k=>$v){
            $url = $this->config()->get('image.ip');
            $data[$k]['image'] = $url . $v['image'];
        }
        return $data;
    }

    public  function  getUnreadActivity($userid)
    {
        $time=time();
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $key = RK_USER_INFO.$userid;
        if ($redis->hExists($key, 'readtime')) {
            $u_time=$redis->hGet($key,'readtime');
            $sql = <<<SQL
                select count(*) as activityCount from ms_activity where status = 1 and start_time <= $time and end_time >=$time and u_time>= $u_time
SQL;
        } else
        {
            $sql = <<<SQL
                select count(*) as activityCount from ms_activity where status = 1 and start_time <= $time and end_time >= $time
SQL;
        }
        $data = $this->DB()->query($sql);
        if (!$data) return ['activityCount'=>'0'];
        return $data[0];
    }

    public  function  updateReadtime($userid)
    {
        $time=time();
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $key = RK_USER_INFO.$userid;
        $redis->hSet($key, 'readtime',$time);
    }

    public function bindAgent($userId, $agentId){
        //检查代理是否存在
        $sql = <<<SQL
               select id, a_pid, a_percent from ms_agent where id=?                  
SQL;
        $agentInfo = $this->DB()->query_fetch($sql, [$agentId]);
        if(!$agentInfo){
            throw new \Exception('代理不存在');
        }
        //修改次数限制
        $sql = <<<SQL
               select agent_update_times from ms_user_info where user_id=?                  
SQL;
        $result = $this->DB()->query_fetch($sql, [$userId]);
        if($result['agent_update_times'] > self::AGENT_UPDATE_TIMES_LIMIT){
            throw new \Exception('修改机会已用完');
        }

        $remain_up_times = $result['agent_update_times']-1;
        //mysql user 数据更新
        $this->DB()->update('ms_user', [
            'agent_id'=>$agentInfo['id'],
            'agent_pid'=>$agentInfo['a_pid']
        ], 'id=' . $userId);
        //mysql userinfo数据更新
        $this->DB()->update('ms_user_info', [
            'agent_update_times'=>$remain_up_times
        ], 'user_id=' . $userId);
        
        //redis用户代理信息更新
        $redis = PRedis::instance();
        $redis->select(R_AGENT_DB);
        $redis->hSet(RK_AL_USER_AGENT.$userId, 'agent_id', $agentId);
        $redis->hSet(RK_AL_USER_AGENT.$userId, 'agent_pid', $agentInfo['a_pid']);
        $redis->hSet(RK_AL_USER_AGENT.$userId, 'percent', $agentInfo['a_percent']);

        //返回剩余次数
        return $remain_up_times;
    }

    public  function  getArena($type){

        $condition='';
        if($type!=0)
        {
            $condition=' and type='.$type;
        }

        $sql = <<<SQL
                select main_table.id, main_table.name, main_table.match_start_time,main_table.game_type,main_table.game_mode, main_table.match_end_time,main_table.is_loop, main_table.date,
                main_table.match_number,main_table.rematch_times,main_table.bullet_number,main_table.first_match_fee,main_table.repeat_match_fee,
                main_table.effect,main_table.status,main_table.award,main_table.create_time,model.mode_name as model_name,type.game_name as type_name
                from ms_match_rule as main_table LEFT JOIN ms_gm_gamemode as model  on model.id=main_table.game_mode LEFT JOIN  ms_gm_gametype as type ON type.id=main_table.game_type 
                where effect = 1 $condition
SQL;
        $data = $this->DB()->fetchAll($sql);
        if (!$data) return [];
        return $data;
    }
}
