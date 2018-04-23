<?php
/**
 * Model
 */

class ModelController extends \BaseController {

    /**
     * 获取模块信息
     */
    public function infoAction() {
        $model = new ModelModel();
        $this->succ($model->info());
    }
    
}
