<?php
class AibeiController extends \BaseController {
    
    public function aibeiAction() {
        //验证参数
        if (!$this->validation($this->input()->json_stream(), [
            'goods_id' => 'required'
        ])) {
            return false;
        }

        $aibei = new AibeiModel();
        $res = $aibei->order($this->input()->json_stream('goods_id'));
        if ($res === true){
            return $this->succ(['transid'=>$aibei->_transid]);
        }
        return $this->failed($res);
    }
    
    public function pnoticeAction() {
        $aibei = new AibeiModel();
        echo $aibei->notify();
    }
    
}
