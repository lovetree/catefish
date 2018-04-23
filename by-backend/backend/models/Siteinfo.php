<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "by_siteinfo".
 *
 * @property integer $id
 * @property string $name
 * @property string $value
 * @property string $created_at
 * @property string $updated_at
 */
class Siteinfo extends \common\components\CommonAR
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'by_siteinfo';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 16],
            [['value'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '栏目名称',
            'value' => '栏目内容',
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
