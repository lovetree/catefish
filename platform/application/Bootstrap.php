<?php

/**
 * @name Bootstrap
 * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * 这些方法, 都接受一个参数:Yaf\Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf\Bootstrap_Abstract {

    use EasyBack;
    
    public function _initAutoload(Yaf\Dispatcher $dispatcher) {
        //注册自动加载
        spl_autoload_register(function($class) use($dispatcher) {
            $dir = $dispatcher->getApplication()->getAppDirectory() . '/modules/';
            if (preg_match('/(.*)(Model|Impl|Data)$/', $class, $matches)) {
                $splits = explode('\\', $matches[1]);
                if (count($splits) <= 1) {
                    return false;
                }
                $module_name = array_shift($splits);
                $path = implode(DIRECTORY_SEPARATOR, $splits);
                $file = $dir . $module_name . '/' . strtolower($matches[2]) . 's/' . $path . '.php';
                if (file_exists($file)) {
                    require_once $file;
                }
            }
        }, true, true);
    }

    public function _initRouter(Yaf\Dispatcher $dispatcher) {
        //注册对象
        Yaf\Registry::set('__INPUT__', new Input());
        Yaf\Registry::set('__DB__', function() {
            static $_db = null;
            if (null === $_db) {
                $_db = new Dao\Connection(Yaf\Application::app()->getConfig()->get('db'));
            }
            return $_db;
        });
        Yaf\Registry::set('__DB_GAMELOG__', function() {
            static $_db = null;
            if (null === $_db) {
                $_db = new Dao\Connection(Yaf\Application::app()->getConfig()->get('db_log'));
            }
            return $_db;
        });

        //关闭模版功能
        $dispatcher->disableView();
    }

    public function _initCore(Yaf\Dispatcher $dispatcher) {
        //脚本结束事件
        register_shutdown_function(function() {
            //PRedis::instance()->close();
        });

        //注册session
        @session_start();
        
        define('VIEW_COMMON', APP_PATH. '/application/modules/Common/');
    }

    public function _initPlugin(Yaf\Dispatcher $dispatcher) {
        $dispatcher->registerPlugin(new PerimissionFilterPlugin());
        $dispatcher->registerPlugin(new CgenFilterPlugin());
    }

    public function _initHook(Yaf\Dispatcher $dispatcher){
        //写日志
        WebApp::hook('Feedback', function($result, $msg){
            //记录日志
            $this->addSysLog($result, $msg);
        });
    }
    
}
