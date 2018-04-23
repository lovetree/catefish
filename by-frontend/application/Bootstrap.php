<?php

/**
 * @name Bootstrap
 * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * 这些方法, 都接受一个参数:Yaf\Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf\Bootstrap_Abstract {

    static private $_db = null;
    static private $_db1 = null;
    public function _initRouter(Yaf\Dispatcher $dispatcher) {
        //注册对象
        Yaf\Registry::set('__INPUT__', new Input());
        Yaf\Registry::set('__DB__', function() {
            if (self::$_db == null) {
                self::$_db = new DB(Yaf\Application::app()->getConfig()->get('db'));
            }
            return self::$_db;
        });
        Yaf\Registry::set('__DB1__', function() {
            if (self::$_db1 == null) {
                self::$_db1 = new DB1(Yaf\Application::app()->getConfig()->get('db1'));
            }
            return self::$_db1;
        });
        //关闭模版功能
        $dispatcher->disableView();
    }

    public function _initCore(Yaf\Dispatcher $dispatcher) {
        Yaf\Dispatcher::getInstance()->setErrorHandler("myErrorHandler");

        //脚本结束事件
        register_shutdown_function(function() {
            PRedis::instance()->close();
        });
    }

    public function _initPlugin(Yaf\Dispatcher $dispatcher) {
//        $dispatcher->registerPlugin(new TokenFilterPlugin());
    }

    public function _initHook() {
        //初始化session
//        WebApp::hook(WebApp::INIT_SESSION, function() {
//            //注册session
//            session_set_save_handler(new RedisSessionHandler(), true);
//            $token = Yaf\Registry::get('__INPUT__')->json_stream('token');
//            session_id($token ? $token : unique_id());
//            @session_start();
//        });

        //玩家登录后
        WebApp::hook(WebApp::AFTER_LOGIN, function($user_id) {
            //日志
            $user = new \UserModel();
            $user->addLoginLog($user_id);
            $user->recordLastLogin($user_id);
        });

        //购买商品后并成功支付后
        WebApp::hook(WebApp::AFTER_PAYMENT, function($order) {
            $model = new OrderModel();
            $model->finishOrder($order);
        });
    }

}
