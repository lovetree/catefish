<?php


class GameiconController extends BaseController {

    public function listAction() {
        $gameicon = new GameiconModel();
        $list = $gameicon->getList();

        return $this->succ($list);
    }
}
