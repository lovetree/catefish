<?php


class IndexController extends BaseController {

    public function indexAction() {
        $this->getView()->assign('action', 'http://' . $_SERVER['HTTP_HOST'].'/cgen/index/gen');
    }

    public function genAction() {
        $input = $this->input()->request();

        //验证参数
        if (!$this->validation($input, array(
            'module' => 'required',
            'controller' => 'required',
            'model' => 'required',
            'table_name' => 'required'
        ))) {
            return false;
        }

        $config = [
            'module' => $input['module'],
            'controller' => $input['controller'],
            'model' => $input['model'],
            'table_name' => $input['table_name']
        ];

        $controllerGen = new Cgen\ControllerGenModel($config);
        $controllerGen->gen();
        echo "controller gen success\n";

        $modelGen = new Cgen\ModelGenModel($config);
        $modelGen->gen();
        echo "model gen success\n";

        $htmlGen = new Cgen\HtmlGenModel($config);
        $htmlGen->gen();
        echo "html gen success\n";

        return false;
    }
}
