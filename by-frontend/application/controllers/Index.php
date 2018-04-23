<?php


class IndexController extends BaseController {

    public function indexAction() {
        echo 'Hello Yaf!!';
        return false;
    }
    public function loginAction(){
        $data = $_POST;
        $name= $data['name'];
        $pwd = $data['pwd'];
        if(!trim($name)||!trim($pwd)){
            echo json_encode(array('res'=>'300','msg'=>'请填写完整信息'));
            exit;
        }

        $User = new UserModel();
        $result = $User->login($name,$pwd);
        echo json_encode($result);
        exit;
    }

    /**
     * @return bool
     * 获取用户信息
     */
    public function infoAction(){
        $info = new UserModel();
        $data = $info->baseInfo(Yaf\Session::getInstance()->get('user_id'));
        $userInfo = $info->userInfo(Yaf\Session::getInstance()->get('user_id'));
        $data['lasttime'] = $userInfo['last_time'];
        $data['head'] = $userInfo['avatar'];
        return $this->succ($data);
    }

    /**
     * 注册
     */
    public function regAction(){

        $data = $_POST;

        $post_data = array(
            'phone' => $data['phone'],
            'password' => $data['pwd'],
            'repassword'=>$data['repwd'],
            'code'=>$data['code'],
            'nickname'=>$data['nickname'],
            'realname'=>$data['realname']
        );
//        var_dump($post_data);
//        exit;
        $result = send_post('http://api.xihucg.cn:8081/v1/User/register',$post_data);

        $result = substr($result,0,-32);
        echo  $result;
        exit;
    }

    /**
     * @return bool
     *短信发送
     */
    public function sendSmsAction(){
        $input =  $_POST;
        $phone = $input['phone'];
        $type = $input['type'];

        $data = array(
            'phone'=>$phone,
            'type'=>$type
        );
        $result = send_post('http://api.xihucg.cn:8081/v1/Sms/sendSms',$data);
        $index = strpos($result,'}');
        $result = substr($result,0,$index+1);
        echo  $result;
        exit;
    }

    /**
     * 修改密码
     *
     */
    public function changePwdAction(){
        $input =  $_POST;
//        $input =array(
//            'old'=>'old',
//            'pwd'=>'pwd',
//            'repwd'=>'repwd'
//        );
        if(!$input['old']||!$input['pwd']||!$input['repwd']){
            $this->failed('请填写完整数据');
            exit;
        }
        if($input['pwd']!=$input['repwd']){
            $this->failed('密码不一致');
            exit;
        }
        $user_id = Yaf\Session::getInstance()->get('user_id');
        $User = new UserModel();
        $oldpwd = $User->getPwd($user_id)['password'];
        if($oldpwd!=md5($input['old'])){
            $this->failed('原密码错误');
            exit;
        }
        $result = $User->editPwd(array('password'=>md5($input['pwd'])),$user_id);
        if(!$result){
            $this->failed('修改失败');
            exit;
        }
        $this->succ();
    }

    /**
     * 修改资料
     */
    public function editInfoAction(){
        $input =  $this->input()->post();
        $pass = $input['pwd'];
        $user_id = Yaf\Session::getInstance()->get('user_id');
        $User = new UserModel();
        $pwd = $User->getPwd($user_id)['password'];
        if(md5($pass)!=$pwd){
            $this->failed('密码错误');
            exit;
        }
        $result = $User->editPwd(array('nickname'=>$input['nickname'],'email'=>$input['email']),$user_id);
        if(!$result){
            $this->failed('修改失败');
            exit;
        }
        $this->succ();
    }

    /**
     * 充值记录
     */
    public function rechargeAction(){
        $input =  $_POST;
        $user_id = Yaf\Session::getInstance()->get('user_id');
        $User = new UserModel();
        $data  = $User->getList($input,$user_id);
        $username = $User->baseInfo($user_id)['username'];
        $data+= array('username'=>$username);

        echo json_encode($data);
        exit;
    }
    /**
     * 确定用户是否已经登录
     */
    public function isLoginAction(){
        $user_id = Yaf\Session::getInstance()->get('user_id');
        if ($user_id){
            $this->succ();
        }else{
            $this->failed('请先登录');
        }
    }

    /**
     * 设置头像
     */
    public function setHeadAction(){
        $user_id = Yaf\Session::getInstance()->get('user_id');
        $User = new UserModel();
        $data  = $User->userInfo($user_id)['avatar'];
        if (!$data){
            $data='';
        }
        echo  json_encode($data);
        exit;
    }
    public function editHeadAction(){
        $user_id = Yaf\Session::getInstance()->get('user_id');
        $url = $_POST['url'];

        $obj = new UserModel();
        $result = $obj->editInfo(array('avatar'=>$url),$user_id);
        if(!$result){
            $this->failed('更新失败');
            exit;
        }
        $this->succ();


    }
    /**
     * 退出登录
     */
    public function logoutAction(){
        Yaf\Session::getInstance()->set('user_id','');
        $this->succ();
    }

    /**
     * 支付
     */
    public function payAction(){
        $input = $_POST;
        $aibei = $this->config()->get("aibei");
        $User = new UserModel();
        $user_id = $User->checkIsUser($input['user_id']);
        if(!$user_id){
            $this->failed('账号不存在或错误');
            exit;
        }

        $result = send_post('http://api.xihucg.cn:8081/v1/Aibei/aibeiForWeb',array('goods_id'=>$input['goods_id'],'user_id'=>$user_id));
        $result = substr($result,0,-32);
        $result = json_decode($result,true)['data']['transid'];

        $data = array(
            'tid'=>$result,
            'app'=>$aibei['appid'],
            'url_r'=>'http://www.xihucg.cn/recharge.html?suc=1',//成功回调地址
            'url_h'=>'http://www.xihucg.cn/recharge.html?suc=0',//放弃支付地址

        );

        $data = json_encode($data);
        $sign = base64_encode(sign( $data,$aibei['appkey'])) ;

        $url = 'https://web.iapppay.com/pay/gateway?data='.urlencode($data).'&sign='.urlencode($sign).'&sign_type=RSA';

        echo json_encode(array('result'=>0,'url'=>$url));
        exit;
    }

    /**
     * 商品/充值 列表
     */
    public function goodListAction(){
        $Obj = new UserModel();
        $data = $Obj->getGoods();
        $this->succ($data);

    }


    /**
     * 客服
     */
    public function serviceAction(){
        $obj = new UserModel();
        $data = $obj->service();
        $this->succ($data);

    }

    ///加载页面下载app页面
    public function downloadAction(){

        echo "download web view!!!";


        $data = array("platform"=>"","version"=>"1.0.3","packagesize"=>"win32","lan"=>"","MD5"=>"","download1"=>"","download2"=>"","uptime"=>1);
        $this->getView()->display("download",$data);
    }

}

