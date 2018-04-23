<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/20 0020
 * Time: 21:44
 */

class GamelogController extends \BaseController {

    /**
     * 战绩
     */
    public function winloseAction()
    {
        try{
            $winloseLog = GamelogModel::getWinloseLog($_SESSION['user_id']);
            //重构数据
            $dataArr = [];
            foreach ($winloseLog as $item){
                $dataArr[] = [
                    'game_type' => intval($item['game_id']),
                    'game_mode' => intval($item['game_mode']),
                    'win_gold' => intval($item['win_gold'] - $item['gold_tax']),
                    'gold' => $item['gold'] + $item['win_gold'] - $item['gold_tax'],
                    'e_time' =>  $item['created_date']
                ];
            }
        }catch (\Exception $e){
            return $this->failed($e->getMessage());
        }

        return $this->succ(['list'=>$dataArr]);
    }

    /**
     * 金币流水
     */
    public function goldflowAction(){
        $input = $this->input()->json_stream();
        //验证参数
        if (!$this->validation($input, array(
            'start_date' => 'required',
            'end_date' => 'required',
            'type' => 'required|inside:1:2:3:4'
        ))) {
            return false;
        }

        try{
            $goldflow = GamelogModel::getGoldflow(
                (isset($input['page']) ? $input['page'] : 1)
                , (isset($input['page_size']) ? $input['page_size'] : 1)
                , $_SESSION['user_id']
                , $input['start_date']
                , $input['end_date']
                , $input['type']);
        }catch (\Exception $e){
            return $this->failed($e->getMessage());
        }

        return $this->succ(['list'=>$goldflow]);
    }
}