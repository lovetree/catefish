<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "by_faq".
 *
 * @property integer $id
 * @property string $question
 * @property string $answer
 * @property string $created_at
 * @property string $updated_at
 */
class Faq extends \common\components\CommonAR
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'by_faq';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['question', 'answer'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['question', 'answer'], 'string', 'max' => 2000]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'question' => '问题',
            'answer' => '回答',
            'created_at' => '创建时间',
            'updated_at' => '最近修改',
        ];
    }

    /**
     * @inheritdoc
     * @return ByQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ByQuery(get_called_class());
    }
}
