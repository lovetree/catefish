<?php

class PerimissionFilterPlugin extends Yaf\Plugin_Abstract {

    use EasyBack;

    public function routerShutdown(Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response) {
        $module = $request->getModuleName();
        $action = $request->getActionName();

        if(config_item('application.debug') == 1){
            error_log("[".date('Y-m-d H:i:s')."]"
                .$request->getModuleName() . '/'
                .$request->getControllerName() . '/'
                .$request->getActionName() . ' get:'
                .implode(' ', array_keys($_GET)) .'--'
                .implode(' ', $_GET)
                .file_get_contents("php://input")."\n", 3,  '/data/www/platform/var/api.log');
        }

        //代理后台接口
        if($module == 'Outer'){
            return true;
        }

        //检测登录
        if ($action !== 'login' && $action !== 'logout'&&$action!=='recharge'&&$action!=='sendsms') {
            $user = new UserModel();
            if (!$user->isLogin()) {
                if ($this->input()->is_ajax_request()){
                    $this->failed('请先登录', RCODE_NEED_LOGIN);
                    exit;
                }
                header('Location:' . 'http://moshen.cn:200/sign-in-m.html');
            }
        }
        
        //检测权限
        if ($module !== 'Index') {
            if (!$user->checkPermission()) {
                $this->failed('无权限操作', RCODE_DENY);
                exit;
            }
        }
    }

}
