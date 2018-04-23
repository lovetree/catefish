<?php

class UserController extends BaseController {

    /**
     * 加载用户数据列表
     */
    public function listAction() {
        $input = $this->input();
        $args = array();

        $query = $input->post_get('query');
        $query_type = $input->post_get('query_type');
        $sortBy = $input->post_get('sortby');
        $sortType = $input->post_get('sorttype');
        $lastip = $input->post_get('userlastip');

        if($lastip&&strlen($lastip)!=0){
            $args['filters']['userlastip'] = $lastip;
        }

        if (!is_null($query) && strlen($query) != 0) {
            if ($query_type == 'userid') {
                $args['filters']['userid'] = $query;
            }
            if ($query_type == 'username') {
                $args['filters']['username'] = $query;
            }
            if ($query_type == 'nickname') {
                $args['filters']['nickname'] = $query;
            }
            if ($query_type == 'phone') {
                $args['filters']['phone'] = $query;
            }
            if ($query_type == 'wx_unionid') {
                $args['filters']['wx_unionid'] = $query;
            }

        }
        if($query_type == 'lock'){
            $args['filters']['lock'] = 1;
        }
        if (!is_null($sortBy)) {
            if ($sortBy == 'userregisttime') {
                $args['order']['created_at'] = is_null($sortType) ? 'desc' : $sortType;
            }
        }

        $page = $input->post_get('pagenum') ?? 1;
        $pagesize = $input->post_get('pagesize') ?? 10;

        $user = new Player\UserModel();
        $data = $user->lists($page, $pagesize, $args);

        $list = $data['list']->toArray();
        unset($data['list']);
        $ret_list = [];
        foreach ($list as $item) {
            $new = array_change_keys($item, array(
                'id' => 'userid',
                'realname' => 'realname',
                'idcard' => 'idcard',
                'username' => 'username',
                'wx_unionid' => 'wx_unionid',
                'nickname' => 'usernickname',
                'gender' => 'usersex',
                'created_at' => 'userregisttime',
                'reg_ip' => 'userregistip',
                'status' => 'userstate',
                'comment' => 'userfrost',
                'last_time' => 'userlasttime',
                'last_ip' => 'userlastip',
                'login_times' => 'userloginprice'
            ));
            $ret_list[] = $new;
        }

        $data['data'] = $ret_list;
        return $this->succ($data, false);
    }
    
    public function infoAction() {
        $request = $this->input()->request();
        $info = [];
        $h_title = "个人信息";
        $params = [];
        $params['id'] = $request['id'];
        $player = new Player\UserModel();
        $info = $player->_getUser($params);
        $userInfo = $player->_getUserInfo(['user_id'=>$request['id']]);
        $estate = $player->_getUserEstate(['user_id'=>$request['id']]);
        $safe = $player->_getUserSafe(['user_id'=>$request['id']]);
        $this->getView()->assign('h_title', $h_title);
        $this->getView()->assign('info', $info);
        $this->getView()->assign('userInfo', $userInfo);
        $this->getView()->assign('estate', $estate);
        $this->getView()->assign('safe', $safe);
        $this->display('edit');
    }
    public function repwdAction() {
        $request = $this->input()->request();
        $info = [];
        $h_title = "重置密码";
        $params = [];
        $params['id'] = $request['id'];
        $player = new Player\UserModel();
        $info = $player->_getUser($params);
        $this->getView()->assign('h_title', $h_title);
        $this->getView()->assign('info', $info);

        $this->display('repwd');
    }
    public function savepwdAction() {
        $request = $this->input()->request();
        if (!$this->validation($request, [
            'id'=>'require',
            'newpwd'=>'require',
        ])) {
            return false;
        }
//        if ($request['status'] == 0){
//            if (!$request['comment']){
//                return $this->failed("请输入冻结的原因");
//            }
//        }
        $user = new Player\UserModel();


            $userArr['password'] = md5($request['newpwd']);

        $user->update('ms_user', $request['id'],  $userArr);

        return $this->succ();

    }
    public function resafeAction() {
        $request = $this->input()->request();
        $info = [];
        $h_title = "重置保险箱密码";
        $params = [];
        $params['user_id'] = $request['id'];
        $player = new Player\UserModel();
        $info = $player->_getUserSafe($params);
        $this->getView()->assign('h_title', $h_title);
        $this->getView()->assign('info', $info);

        $this->display('resafe');
    }
    public function savesafeAction() {
        $request = $this->input()->request();
        if (!$this->validation($request, [
            'id'=>'require',
            'newpwd'=>'require',
        ])) {
            return false;
        }
//        if ($request['status'] == 0){
//            if (!$request['comment']){
//                return $this->failed("请输入冻结的原因");
//            }
//        }
        $user = new Player\UserModel();


        $userArr['password'] = md5($request['newpwd']);

        $user->update('ms_safe', $request['id'],  $userArr);

        return $this->succ();

    }
    public function stockAction() {
        $request = $this->input()->request();
        $info = [];
        $h_title = "冻结用户";
        $params = [];
        $params['id'] = $request['id'];
        $player = new Player\UserModel();
        $info = $player->_getUser($params);
        $this->getView()->assign('h_title', $h_title);
        $this->getView()->assign('info', $info);
        $this->display('stock');
    }
    public function savestockAction() {
        $request = $this->input()->request();
        if (!$this->validation($request, [
            'id'=>'require',
            'comment'=>'require',
        ])) {
            return false;
        }
        $user = new Player\UserModel();
        $userArr['comment'] = $request['comment'];
        $userArr['status'] = $request['status']?$request['status']:0;
        $user->update('ms_user', $request['id'],  $userArr);
        return $this->succ();
    }
    public function estateAction(){
        $request = $this->input()->request();
        $info = [];
        $h_title = "修改资产";
        $params = [];
        $params['id'] = $request['id'];
        $player = new Player\UserModel();
        $safe = $player->_getUserSafe(['user_id'=>$request['id']]);
        $estate = $player->_getUserEstate(['user_id'=>$request['id']]);
        $this->getView()->assign('id',  $request['id']);
        $this->getView()->assign('h_title', $h_title);
        $this->getView()->assign('estate', $estate);
        $this->getView()->assign('safe', $safe);
        $this->display('estate');
    }
    public function saveestateAction() {
        $request = $this->input()->request();
        if (!$this->validation($request, [
            'id'=>'require',
        ])) {
            return false;
        }
        if(!$request['id']){
            return $this->failed('丢失关键参数');
            exit;
        }

        if(is_numeric($request['user_level'])&&(intval($request['user_level'])!=0)){
            $user = new Player\UserModel();
            if(!$user->saveUserLevel($request)){
                return $this->failed('用户vip等级更改失败');
                exit;
            }
        }
        if(is_numeric($request['gold'])&&(intval($request['gold'])!=0)){
            //更新gold_log
            $user = new Player\UserModel();
            if(!$user->saveGoldLog($request)){
                return $this->failed('金币充值记录保存失败');
                exit;
            }
        }

        if(is_numeric($request['safe_gold'])&&(intval($request['safe_gold'])!=0)){
            //更新gold_log
            $user = new Player\UserModel();
            if(!$user->saveSafeLog($request)){
                return $this->failed('保险柜金币记录保存失败');
                exit;
            }
        }

        if(is_numeric($request['credit'])&&(intval($request['credit'])!=0)){
            //更新gold_log
            $user = new Player\UserModel();
            if(!$user->saveCreditLog($request)){
                return $this->failed('钻石记录保存失败');
                exit;
            }
        }

        if(is_numeric($request['emerald'])&&(intval($request['emerald'])!=0)){
            //更新gold_log
            $user = new Player\UserModel();
            if(!$user->saveEmeraldLog($request)){
                return $this->failed('绿钻石记录保存失败');
                exit;
            }
        }

        //更新user_estate
        $this->_updateUserEstate($request);
        $this->_updateUserSafe($request);
        return $this->succ();

    }
    public function ajaxInfoAction() {
        $request = $this->input()->request();
        if (!$this->validation($request, [
            'id'=>'require',

        ])) {
            return false;
        }
//        if ($request['status'] == 0){
//            if (!$request['comment']){
//                return $this->failed("请输入冻结的原因");
//            }
//        }
        $user = new Player\UserModel();
        
        //更新user表
        $userArr = [
            'nickname' =>$request['nickname'],
            'phone' => $request['phone'],

        ];
        if($request['rs_pass'] == 1){
            $userArr['password'] = md5(config_item('biz.default_login_pass'));
        }
        $user->update('ms_user', $request['id'],  $userArr);


        //更新user_info表
        $userinfoArr = [
            'user_level' => $request['user_level'],
            'realname'=>$request['realname'],
            'idcard'=>$request['idcard'],
            'nickname' => $request['nickname']
        ];
        $select = $this->DB()->newSelect('ms_user_info');
        $select->where('user_id', $request['id']);
        $select->setData($userinfoArr);
        $this->DB()->exec($select->updateSql());
        $redis = \PRedis::instance();
        $redis->hSet('info@'.$request['id'],'vip',$request['user_level']);
        return $this->succ();
        
    }
    private function _updateUserSafe($paramArr){

        $redis = \PRedis::instance();
        $redis->select(R_GAME_DB);
        //更新数据库
        $select = $this->DB()->newSelect('ms_safe');
        $select->where('user_id', $paramArr['id']);
        $old = $redis->hGet('info@'.$paramArr['id'],'safe_gold');

        if($old==false){
            $old = 0;
        }
        $userinfoArr = [
            'safe_gold' => $paramArr['safe_gold']+$old
        ];
        $select->setData($userinfoArr);
        $status = $this->DB()->exec($select->updateSql());

        if($status > 0){
            //更新redis
            $redis->hSet(RK_USER_INFO.$paramArr['id'], 'safe_gold', $paramArr['safe_gold']+$old);

            //实时推送金币
            realTimePush($paramArr['id'], json_encode(['type'=>'safe_gold', 'data'=>['total'=>$paramArr['safe_gold']+$old, 'o_time'=>time()]]));

        }
    }
    private function _updateUserEstate($paramArr){
        $redis = \PRedis::instance();
        $redis->select(R_GAME_DB);

        //更新数据库
        $select = $this->DB()->newSelect('ms_user_estate');
        $select->where('user_id', $paramArr['id']);
        $old = $redis->hMGet('info@'.$paramArr['id'],['gold','credit','emerald']);
        $userinfoArr = [
            'gold' => $paramArr['gold']+$old['gold'],
            'credit' => $paramArr['credit']+$old['credit'],
            'emerald' => $paramArr['emerald']+$old['emerald']
        ];
        $select->setData($userinfoArr);
        $status = $this->DB()->exec($select->updateSql());

        if($status > 0){
            //更新redis

            $redis->hSet(RK_USER_INFO.$paramArr['id'], 'gold', $paramArr['gold']+$old['gold']);
            $redis->hSet(RK_USER_INFO.$paramArr['id'], 'credit', $paramArr['credit']+$old['credit']);
            $redis->hSet(RK_USER_INFO.$paramArr['id'], 'emerald', $paramArr['emerald']+$old['emerald']);

            //实时推送金币
            realTimePush($paramArr['id'], json_encode(['type'=>'gold', 'data'=>['total'=>$paramArr['gold']+$old['gold'], 'o_time'=>time()]]));
            realTimePush($paramArr['id'], json_encode(['type'=>'credit', 'data'=>['total'=>$paramArr['credit']+$old['credit'], 'o_time'=>time()]]));
            realTimePush($paramArr['id'], json_encode(['type'=>'emerald', 'data'=>['total'=>$paramArr['emerald']+$old['emerald'], 'o_time'=>time()]]));
        }
    }

    /**
     * 删除用户
     */
    public function delAction() {
        $input = $this->input();
        //验证参数
        if (!$this->validation($input->request(), array(
                    'userid' => 'required',
                    'comment' => 'required'
                ))) {
            return false;
        }

        $user = new Player\UserModel();
        $status = $user->delete($input->post_get('userid'), $input->post_get('comment'));
        if (!$status) {
            return $this->failed('无法删除指定用户');
        }
        return $this->succ();
    }

    /**
     * 冻结/解冻用户
     */
    public function freezeAction() {
        $input = $this->input();
        //验证参数
        if (!$this->validation($input->request(), array(
                    'userid' => 'required',
                    'status' => 'required|inside:0:1',
                    'comment' => 'required'
                ))) {
            return false;
        }

        $user = new Player\UserModel();
        $status = $user->freeze($input->post_get('userid'), $input->post_get('status') == 1 ? true : false, $input->post_get('comment'));
        if (!$status) {
            return $this->failed('无法操作指定用户');
        }
        return $this->succ();
    }

    /**
     * 获取用户的详细信息
     */
    public function detailAction() {
        $request = $this->input()->request();
        //验证参数
        if (!$this->validation($request, array(
                    'userid' => 'required|min:1',
                    'type' => 'required|inside:baseinfo'
                ))) {
            return false;
        }

        $userid = $request['userid'];
        $type = $request['type'];

        $result = [];
        $user = new Player\UserModel();

        //基本信息
        if ($type == 'baseinfo') {
            $result = $user->getUserData($userid);
            $result = $result->toArray();
            $result = array_pop($result);
            if (!empty($result['created_at'])) {
                $result['created_at'] = date('Y-m-d H:i:s', $result['created_at']);
            }
            if (!empty($result['last_time'])) {
                $result['last_time'] = date('Y-m-d H:i:s', $result['last_time']);
            }
            $result = array_change_keys($result, [
                'id' => 'user_id',
                'created_at' => 'reg_time',
                'reg_ip' => 'reg_ip',
                'comment' => 'freeze_comment',
                'last_time' => 'last_time',
                'last_ip' => 'last_ip',
                'mobile' => 'reg_phone',
                'hardcode' => 'reg_hardcode'

                    ], true);
        }

        return $this->succ($result);
    }

    public function save_baseinfoAction() {
        $request = $this->input()->request();
        //验证参数
        if (!$this->validation($request, array(
                    'userid' => 'required|min:1',
                    'password' => 'length:4:20',
                    'gender' => 'inside:0:1:2',
                    'nickname' => 'length:1:63',
                    'idcard' => 'length:18:18',
                    'signature' => 'length:1:50',
                    'avatar' => 'length:1:255'
                ))) {
            return false;
        }

        $fields = [];
        isset($request['gender']) && ($fields['gender'] = $request['gender']);
        isset($request['nickname']) && ($fields['nickname'] = $request['nickname']);
        isset($request['idcard']) && ($fields['idcard'] = $request['idcard']);
        isset($request['signature']) && ($fields['signature'] = $request['signature']);
        isset($request['avatar']) && ($fields['avatar'] = $request['avatar']);
        if (isset($request['password']) && $request['password'] !== '******') {
            $fields['password'] = md5($request['password']);
        }

        $model = new Player\UserModel();
        $status = $model->save($request['userid'], $fields);
        if (!$status) {
            return $this->failed('保存失败');
        }
        return $this->succ();
    }

    /**
     * 设置/取消测试员
     */
    public function setTesterAction(){
        $input = $this->input()->request();

        //验证参数
        if (!$this->validation($input, array(
            'userid' => 'required',
            'is_tester' => 'required|inside:0:1'
        ))) {
            return false;
        }

        $user = new Player\UserModel();
        $status = $user->setTester($input['userid'], $input['is_tester']);
        if (!$status) {
            return $this->failed('无法操作指定用户');
        }
        return $this->succ();
    }

    /**
     * 赠送金币
     */
    public function addGoldAction(){
        $input = $this->input()->request();

        //验证参数
        if (!$this->validation($input, array(
           // 'user_id[]' => 'required',
            'golds' => 'required|number'
        ))) {
            return false;
        }

        $this->DB()->beginTransaction();
        //修改用户金币信息
        $user = new Player\UserModel();
        $user->update('ms_user_estate', $input['user_id'], [
            'gold' => $input['golds']
        ]);
        //修改redis金币信息
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        $key = RK_USER_INFO.$input['user_id'][0];
        $gold = $redis->hGet($key, 'gold');
        $status = $redis->hSet($key, 'gold', $gold+$input['golds']);

        if ($status === false) {
            $this->DB()->rollBack();
            return $this->failed('无法操作指定用户');
        }

        $this->DB()->commit();

        return $this->succ();
    }

    /**
     * 清空逃跑率
     */
    public function clearEscapeAction(){
        $input = $this->input()->request();

        //验证参数
        if (!$this->validation($input, array(
            'id' => 'required|number',
        ))) {
            return false;
        }

        $user = new Player\UserModel();
        $status = $user->clearEscape($input['id']);;
        if (!$status) {
            return $this->failed('无法操作指定用户');
        }
        return $this->succ();
    }

    /**
     * 赠送会员
     */
    public function MemberPresentAction(){
        $input = $this->input()->request();

        //验证参数
        if (!$this->validation($input, array(
            'usre_id' => 'required',
            'type' => 'required',
            'effected_days' => 'required|number'
        ))) {
            return false;
        }

        $this->DB()->beginTransaction();
        //修改用户会员信息
        $user = new Player\UserModel();
        $user->update('ms_user', $input['user_id'], [
            'is_member' => 1,
            'member_expire' => strtotime('+' . $input['effected_days'] . ' days')
        ]);
        //赠送会员记录
        $log = new Player\MemberpreModel();
        $log->batchAdd($input['usre_id'], $input['type'], $input['effected_days'], $input['reason']);

        $status = $this->DB()->commit();

        if (!$status) {
            $this->DB()->rollBack();
            return $this->failed('无法操作指定用户');
        }
        return $this->succ();
    }

    /**
     * 赠送钻石
     */
    public function DiamondPresentAction(){
        $input = $this->input()->request();

        //验证参数
        if (!$this->validation($input, array(
            'usre_id' => 'required',
            'diamonds' => 'required|number'
        ))) {
            return false;
        }

        $this->DB()->beginTransaction();
        //修改用户钻石信息
        $user = new Player\UserModel();
        $user->update('ms_user_estate', $input['user_id'], [
            'diamonds' => $input['diamonds']
        ]);
        //赠送钻石记录
        $log = new Player\DiamondpreModel();
        $log->batchAdd($input['usre_id'], $input['diamonds'], $input['reason']);

        $status = $this->DB()->commit();

        if (!$status) {
            $this->DB()->rollBack();
            return $this->failed('无法操作指定用户');
        }
        
        return $this->succ();
    }
}
