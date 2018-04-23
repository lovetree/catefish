<?php

class CgenFilterPlugin extends Yaf\Plugin_Abstract {

    use EasyBack;

    public function routerShutdown(Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response) {
        $module = $request->getModuleName();

        //检测模块
        if (strtolower($module) == 'cgen') {
            Yaf\Application::app()->getDispatcher()->enableView();
        }
    }

}
