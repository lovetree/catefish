<?php
namespace Cgen;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/13 0013
 * Time: 19:59
 */
class HtmlGenModel extends \Cgen\GeneratorModel{
    public function gen()
    {
        $columns = $this->config['columns'];
        $title = "标题";
        $urlList = $this->config['module'] . '/' . $this->config['controller'] . '/list';
        $urlDel = $this->config['module'] . '/' . $this->config['controller'] . '/delete';
        $urlSave = $this->config['module'] . '/' . $this->config['controller'] . '/save';
        $tplContent = include $this->config['tpl_path'] . 'html_tpl.php';

        $savePath = APP_PATH . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR
            . lcfirst($this->config['module']) . '_'
            . ucfirst($this->config['controller']) . '.html';

        file_put_contents($savePath , $tplContent);
    }
}