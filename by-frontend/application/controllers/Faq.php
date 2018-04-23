<?php


class FaqController extends BaseController {

    public function listAction() {
        $news = new FaqModel();
        $list = $news->getList();

        return $this->succ($list);
    }

    public function detailAction() {
        $input = $this->input();
        //验证参数
        if (!$this->validation($input->get(), array(
            'id' => 'number|min:1'
        ))) {
            return false;
        }

        $news = new FaqModel();
        $detail = $news->getDetail($input->get('id'));

        return $this->succ($detail);
    }
    public function subQuAction(){
        $last = Yaf\Session::getInstance()->get('feq');
//
        if($last&&time()-$last<60){
            $this->failed('一分钟内只能反馈一次');
            exit;
        }
        Yaf\Session::getInstance()->set('feq',time());
        $data = $this->input()->post();
        $info = new UserModel();
        $username = $info->baseInfo(Yaf\Session::getInstance()->get('user_id'))['username'];
        $data['account'] = $username;
        $data['created_at'] = date('Y-m-d H:i:s',time());
        $data['remote_ip'] = $this->input()->ip_address();

        $obj = new FaqModel();
        $obj->addFeed($data);
        $this->succ();


    }
}
