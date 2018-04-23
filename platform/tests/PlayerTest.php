<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/7 0007
 * Time: 16:20
 */
use PHPUnit\Framework\TestCase;

class PlayerTest extends TestCase{
    private $_app = null;
    private $_dispatcher = null;
    private $_request = null;

    public function setup(){
        $this->_app = Yaf\Application::app();
        $this->_dispatcher = $this->_app->getDispatcher();
        $this->_request = new Yaf\Request\Simple();
    }

    /**
     * 设置测试员
     */
    public function testSetTester(){
        ob_start();

        $_REQUEST['userid'] = 300007;
        $_REQUEST['is_tester'] = '1';
        $this->_request->setModuleName('Player');
        $this->_request->setControllerName('User');
        $this->_request->setActionName('setTester');
        $this->_dispatcher->returnResponse(true)->dispatch($this->_request);

        $content = json_decode(ob_get_contents(),true);
        ob_end_clean();

        $this->assertEquals(0, $content['result']);
    }

    /**
     * 赠送金币
     */
    public function testAddGold(){
        /***正常流程***/
        ob_start();
        $_REQUEST['userid'] = 300007;
        $_REQUEST['golds'] = 200;
        $this->_request->setModuleName('Player');
        $this->_request->setControllerName('User');
        $this->_request->setActionName('addGold');
        $this->_dispatcher->returnResponse(true)->dispatch($this->_request);

        $content = json_decode(ob_get_contents(),true);
        ob_end_clean();
        $this->assertEquals(0, $content['result']);

        /**异常流程***/
        ob_start();
        $_REQUEST['userid'] = 300007;
        $_REQUEST['golds'] = -200;
        $this->_dispatcher->returnResponse(true)->dispatch($this->_request);

        $content = json_decode(ob_get_contents(),true);
        ob_end_clean();
        $this->assertEquals(1004, $content['result']);
    }

    
    public function test(){
        /***正常流程***/
        ob_start();
        $_REQUEST['userid'] = 300007;
        $_REQUEST['amount'] = 200;
        $this->_request->setModuleName('Player');
        $this->_request->setControllerName('User');
        $this->_request->setActionName('addGold');
        $this->_dispatcher->returnResponse(true)->dispatch($this->_request);

        $content = json_decode(ob_get_contents(),true);
        ob_end_clean();
        $this->assertEquals(0, $content['result']);

        /**异常流程***/
        ob_start();
        $_REQUEST['userid'] = 300007;
        $_REQUEST['amount'] = -200;
        $this->_dispatcher->returnResponse(true)->dispatch($this->_request);

        $content = json_decode(ob_get_contents(),true);
        ob_end_clean();
        $this->assertEquals(1004, $content['result']);
    }
}