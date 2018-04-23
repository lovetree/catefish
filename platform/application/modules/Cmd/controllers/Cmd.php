<?php

class CmdController extends BaseController {
    /*
     * 查询列表
     */
    public function cmdAction() {
        $tb = 'ms_active';   //db
        $p_l = ['id', 'title', 'image', 'link', 'a_sort', 'start_time', 'status'];  //查询字段
        $p_s = ['id', 'title', 'image', 'link', 'a_sort', 'start_time'];    //更新插入字段
        
        $modules = 'Add';
        $model = 'Add';
        
        
        
        
        
        
        /******************************以下不必修改*******************************/
        $dir = dirname(dirname(dirname(__FILE__))) . "/" . $modules;
        if (!file_exists($dir)){
            mkdir($dir, 0777);
        }
        
        $c = $dir . "/controllers";
        $m = $dir . "/models";
        if (!file_exists($c)){
            mkdir($c, 0777);
        }
        if (!file_exists($m)){
            mkdir($m, 0777);
        }
        
        include 'c.php';
        $file_c = $c . "/" . $model . ".php";
        file_put_contents($file_c, $tpl);
        
        include 'm.php';
        $file_m = $m . "/" . $model . ".php";
        file_put_contents($file_m, $m_tpl);
    }

}
