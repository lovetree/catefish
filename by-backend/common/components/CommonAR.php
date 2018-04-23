<?php

namespace common\components;

use Yii;

/**
 * This is the common model class 
 *
 */
class CommonAR extends \yii\db\ActiveRecord
{
    public function behaviors(){
        return [
            [
                'class' => \yii\behaviors\TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }
}
