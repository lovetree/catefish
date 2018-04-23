<?php

namespace console\controllers;

use yii\console\Controller;
use backend\models\News;

class PublishController extends Controller
{
    /*
    * 公告发布
    * */
    public function actionRun()
    {
        $news = News::findAll(['status'=>'daifabu']);
        foreach ($news as $new){
            if(strtotime($new->publish_time) <= time()){
                $new->publish_time = date('Y-m-d H:i:s');
                $new->status = 'yifabu';

                if($new->save()){
                    echo $new->title . "-发布成功";
                }
            }
        }
    }
}
