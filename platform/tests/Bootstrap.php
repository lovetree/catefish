<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/7 0007
 * Time: 17:22
 */

define("APP_PATH", realpath(dirname(__FILE__) . '/../')); /* 指向public的上一级 */

define('MIRACLE_CACHE', false);
define('MIRACLE_CACHE_DIR', APP_PATH . '/dao_runtime/');
if (!file_exists(MIRACLE_CACHE_DIR)) {
    mkdir(MIRACLE_CACHE_DIR);
    chmod(MIRACLE_CACHE_DIR, 0777);
}

require_once APP_PATH . '/application/constant.php';
require_once APP_PATH . '/application/function/function.php';

$app = new Yaf\Application(APP_PATH . "/conf/application.ini");

define('DEBUG', boolval($app->getConfig()->get('application.debug')));

spl_autoload_register(function($class) use($app){
    $dir = $app->getAppDirectory() . '/modules/';
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
$app->getDispatcher()->autoRender(true);
$app->getDispatcher()->disableView();

//$app->run();