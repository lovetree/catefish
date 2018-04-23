<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/13 0013
 * Time: 17:00
 */
namespace  Cgen;

Abstract class GeneratorModel{
    use \EasyBack;

    public $config = [];

    public function __construct($config){
        $this->init($config);
    }

    public function init( $config){
        //检查表是否有效
        $db = \Yaf\Registry::get('__DB__')();
        if(!$columns = $db->getTableColumnsDetail($config['table_name'])){
            $this->failed('表不存在');
        }

        $config['columns'] = $columns;

        $this->config = $config;

        if(!$this->config['module']){
            $this->config['module'] = 'Index';
            $this->config['save_path_pre'] = \Yaf\Application::app()->getAppDirectory . DIRECTORY_SEPARATOR;
        }else{
            $path = \Yaf\Application::app()->getAppDirectory() . DIRECTORY_SEPARATOR
                . 'modules'. DIRECTORY_SEPARATOR . ucfirst($this->config['module']);
            if(!is_dir($path)){
                mkdir($path, 0755, true);
            }
            $this->config['save_path_pre'] = $path;
        }

        if(!isset($config['tpl_path'])) {
            $this->config['tpl_path'] = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'templates'
                . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR;
        }
    }

    public Abstract function gen();
}