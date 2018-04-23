<?php

class EmailController extends \BaseController {

    /**
     * 获取邮件列表
     */
    public function listAction() {
        $input = $this->input();
        //验证参数
        if (!$this->validation($input->json_stream(), array(
                    'last_id' => 'number|min:0',
                    'length' => 'number|min:1|max:20',
                ))) {
            return false;
        }

        $email = new EmailModel();
        $last_id = $input->json_stream('last_id');
        if(is_null($last_id)){
            $last_id = 0;
        }
        $length = $input->json_stream('length');
        if(is_null($length)){
            $length = 10;
        }
        $data = $email->getMailList($_SESSION['user_id'], $last_id, $length);
        if(false === $data){
            $data = array();
        }
        return $this->succ($data);
    }

    /**
     * 查看邮件详情
     */
    public function getAction() {
        $input = $this->input();
        //验证参数
        if (!$this->validation($input->json_stream(), array(
                    'm_id' => 'required|positive_number',
                ))) {
            return false;
        }

        $email = new EmailModel();
        $data = $email->readOne($_SESSION['user_id'], $input->json_stream('m_id'));
        if(false === $data){
            $data = array();
        }else{
            $data = json_decode($data[0], TRUE);
            $url = $this->config()->get('image.ip');
            $data['m_image'] = $url . $data['m_image'];
        }
        return $this->succ($data);
    }
    
}
