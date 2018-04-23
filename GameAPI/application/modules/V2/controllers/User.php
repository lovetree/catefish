<?php

class UserController extends \BaseController {

    /**
     * 用户登录
     */
    public function loginAction() {
        $input = $this->input();
        $cmd = $input->json_stream('cmd');
        $rule = array(
            'cmd' => 'required',
            'game_version' => 'required'
//            'device_id' => 'required'
        );

        if ($cmd === UserModel::LOGIN_TYPE_PW) {
            $rule['account'] = 'required';
            $rule['password'] = 'required';
        }
        
        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }

        //游戏版本比较
        if(str_replace('.', '',  $input->json_stream('game_version')) < getPlatVer()){
            return $this->failed('版本过低', RCODE_UNSUPPORT);
        }

        $user = new UserModel();
        $data = $user->login($input->json_stream('cmd'), $input->json_stream());
        if (false === $data) {
            return $cmd === UserModel::LOGIN_TYPE_PW ? $this->failed('用户名或密码不正确', RCODE_BUSY) : $this->failed('服务器繁忙', RCODE_BUSY);
        }else if(RCODE_ACCOUNT_FREEZING === $data){
            return $this->failed('帐号被冻结', RCODE_ACCOUNT_FREEZING);
        }else if(RCODE_LOGIN_AUTH_EXPIRED === $data){
            return $this->failed('授权信息过期', RCODE_LOGIN_AUTH_EXPIRED);
        }
        
        //构造数据
        $user_id = $data['u_id'];
        $data['u_email_count'] = $data['u_email_count'] ?? 0;
        $data['proxy_ip'] = 'www.yueyouqp.com';
        $data['proxy_port'] = '8001';
        $data['u_token'] = session_id();
        $data['u_vip'] = intval($data['u_vip'] ?? 0);
        $data['u_gender'] = intval($data['u_gender'] ?? 0);
        $data['u_city'] = GetIpLookup($_SERVER['REMOTE_ADDR']);
        $data['u_ip'] = $this->input()->ip_address();

        WebApp::inform(WebApp::AFTER_LOGIN, [$user_id]);
        return $this->succ($data);
    }

    /**
     * 用户信息获取
     */
    public function infoAction() {
        $input = $this->input();
        //验证参数
        if (!$this->validation($input->json_stream(), array(
                    't_uid' => 'required'
                ))) {
            return false;
        }
        $user = new UserModel();
        $data = $user->getUserInfo($input->json_stream('t_uid'));
        if (!$data) {
            return $this->failed('服务器繁忙', RCODE_BUSY);
        }
        return $this->succ($data);
    }

    public function baseInfoAction() {
        $input = $this->input();
        //验证参数
        if (!$this->validation($input->json_stream(), array(
                    't_uid' => 'required'
                ))) {
            return false;
        }
        //需要获取到用户redis中的值，如果不存在就提示系统繁忙，存在做处理
        $redis = PRedis::instance();
        $redis->select(R_GAME_DB);
        if (!$redis->exists(RK_USER_INFO . $input->json_stream('t_uid'))) {
            return $this->failed(_1000106);
        }
        
        $user_info = $redis->hMget(RK_USER_INFO . $input->json_stream('t_uid'), ['nickname', 'gold', 'popularity', 'photo']);
        return $this->succ([
            'u_id' => $input->json_stream('t_uid'),
            'u_nickname' => $user_info['nickname'],
            'u_photo' => $user_info['photo'],
            'gold' => $user_info['gold'],
            'popularity' => $user_info['popularity']
        ]);
    }
    
    /**
     * 用户信息修改
     */
    public function editAction() {
        //检测登录
        $user = new UserModel();
        $fields = [];
        $input = $this->input();
        $input->json_stream('name') && ($fields['nickname'] = $input->json_stream('name'));
        $input->json_stream('photo') && ($fields['avatar'] = $input->json_stream('photo'));
        if (!$user->edit($_SESSION['user_id'], $fields)) {
            return $this->failed('修改失败');
        }
        return $this->succ();
    }

    /**
     * 获取用户战绩
     */
    public function exploitsAction() {

        $input = $this->input();
        //验证参数
        if (!$this->validation($input->json_stream(), array(
                    't_uid' => 'required'
                ))) {
            return false;
        }
        
        $user = new UserModel();
        $data = $user->getExploits($input->json_stream('t_uid'));
        if (!$data) {
            return $this->failed('服务器繁忙', RCODE_BUSY);
        }
        return $this->succ($data);
    }

    /**
     * 用户反馈
     */
    public function feedbackAction(){
        WebApp::inform(WebApp::INIT_SESSION);
        $request = $this->input()->json_stream();
        //验证参数
        if (!isset($request['content']) || !$request['content']){
            return $this->failed(_1000301);
        }
        $userModel = new UserModel();
        $status = $userModel->addFeedback($userModel->getLoginUserID(), $request['content']);
        if(-1 === $status){
            return $this->failed(_1000004);
        }else if(false == $status){
            return $this->failed(_1000001, RCODE_BUSY);
        }
        return $this->succ();
    }
    
    /**
     * 找回密码，重置密码
     */
    public function resetAction(){
        $input = $this->input();
        $rule = array(
            'phone' => 'required|phone',
            'password'  => 'required',
            'repassword'  => 'required',
            'code'  => 'required'
        );
        
        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }
        
        if (!checkPassword($input->json_stream('password'))){
            return $this->failed(_1000007);
        }
        
        $phone = $input->json_stream("phone");
        $password = $input->json_stream("password");
        $repassword = $input->json_stream("repassword");
        $code = $input->json_stream("code");
        
        if ($password != $repassword){
            return $this->failed(_1000103);
        }

        $smsModel = new SmsModel();
        //$userModel->sendSms($input->json_stream("phone"));
        $sms_info = $smsModel->getOne($phone, 2, $code);
        if (!$sms_info){
            return $this->failed(_1000202);
        }

        if ($sms_info['status'] == 0){
            return $this->failed(_1000202);
        }

        if ($sms_info['is_used'] == 1){
            return $this->failed(_1000202);
        }

        if (time() > intval($sms_info['c_time'] + $sms_info['expire'])){
            return $this->failed(_1000205);
        }

        if (!$smsModel->updateUsed($sms_info['id'])){
            return $this->failed(_1000003);
        }
        
        //验证手机号是否注册’
        $userModel = new UserModel();
        $userInfo = $userModel->getInfoByPhone($phone);
        if (!$userInfo){
            return $this->failed(_1000101);
        }
        $data = array();
        $data['password'] = md5($password);
        $data['updated_at'] = time();
        if (!$userModel->resetPass($userInfo['id'], $data)){
            return $this->failed(_1000002);
        }
        
        $this->succ();
    }
    
    /**
     * 手机号注册
     */
    public function registerAction(){
        $input = $this->input();
        $rule = array(
            'phone' => 'required|phone',
            'password'  => 'required',
            'repassword'  => 'required',
        );
        
        //验证参数
        if (!$this->validation($input->json_stream(), $rule)) {
            return false;
        }
        
        if (!checkPassword($input->json_stream('password'))){
            return $this->failed(_1000007);
        }
        
        $phone = $input->json_stream("phone");
        $password = $input->json_stream("password");
        $repassword = $input->json_stream("repassword");
        
        if ($password !== $repassword){
            return $this->failed(_1000103);
        }
        
        //验证手机号是否注册
        $userModel = new UserModel();
        $userInfo = $userModel->getInfoByPhone($phone);
        if ($userInfo){
            return $this->failed(_1000102);
        }
        
        $data = array();
        $data['username'] = 'gu' . date('d') . bin2hex(random_bytes(4));
        $data['password'] = md5($password);
        $data['nickname'] = '游客' . random_int(100000, 999999);
        $data['type'] = 0;
        $data['phone'] = $phone;
        $data['avatar'] = toUrl(DEF_AVATAR); //头像
        if (!$userModel->register($data)){
            return $this->failed(_1000104);
        }
        
        $this->succ();
    }

    /**
     * 导出游客数据临时脚本
     * @throws Exception
     */
    public function dumpAIDataAction(){
        $sql = <<<SQL
                select id
                 from ms_user where type=100
                  
SQL;
        $users = $this->DB()->query($sql);
        $redis = PRedis::instance2();

        $usermodel = new UserModel();

        $dataArr = [];
        foreach ($users as $user){
            $redis->lPush('uid_list', $user['id']);

            //基本数据
            $data = $usermodel->getLoginUserInfo($user['id']);
            //财产数据
            $sql = <<<SQL
                    select gold, credit,ticket from ms_user_estate where user_id=? 
SQL;
            $estateData = $this->DB()->query_fetch($sql, [$user['id']]);
            if(!$estateData){
                $estateData = [
                    'gold' => 0,
                    'credit' => 0,
                    'ticket' => 0
                ];
            }

            $data = array_merge($data, $estateData);
            $redis->set('aidata@' .$user['id'],  json_encode($data));
        }
    }

    public function updateRobotAction(){
        $redis = PRedis::instance2();
        $redis->select(0);
        $keys = $redis->keys("info@*");
        $keyArr = [];
        foreach ($keys as $key){
            $key = explode('@', $key)[1];
            $keyArr[] = $key;
            if(count($keyArr) == 1000){
                $this->DB()->update('ms_user', ['type'=>100], 'where id in ('.implode(',', $keyArr).')');
                $keyArr = [];
            }
        }

    }

    /**
     * 推广用户注册接口
     */
    public function proUserAction(){
        $request = $this->input()->json_stream();
        $user = new UserModel();
        $userId = $user->register($request);

        return $this->succ(['user_id'=>$userId]);
    }

    public function updateUserGoldAction(){
        $request = $this->input()->json_stream();
        //签名校验
        if($request['sign'] && $request['sign'] == md5($request['user_id'].$request['gold'].'MszhangY')){
              $user = new Data\UserModel($request['user_id']);
              $user->addGold($request['gold']);
              return $this->succ();
        }
    }
}
