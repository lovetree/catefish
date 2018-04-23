<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/7 0007
 * Time: 16:20
 */
use PHPUnit\Framework\TestCase;

class LoginlogTest extends TestCase{
    private $_app = null;
    private $_dispatcher = null;
    private $_request = null;

    public function setup(){
        $this->_app = Yaf\Application::app();
        $this->_dispatcher = $this->_app->getDispatcher();
        $this->_request = new Yaf\Request\Simple();
    }

    /**
     * 查询
     */
    public function testList(){
        /***异常流程***/
        ob_start();
        $_REQUEST['start_time'] = '2015-3-15';
        $_REQUEST['end_time'] = '2015-3-16';
        $this->_request->setModuleName('Player');
        $this->_request->setControllerName('Loginlog');
        $this->_request->setActionName('list');
        $this->_dispatcher->returnResponse(true)->dispatch($this->_request);

        $content = json_decode(ob_get_contents(),true);
        ob_end_clean();

        $this->assertEquals(0, $content['total']);

        /***正常流程***/
        
    }
}