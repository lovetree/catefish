<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "{{%charge}}".
 *
 * @property integer $id
 * @property string $order_no
 * @property string $account
 * @property integer $amount
 * @property string $pay_type
 * @property string $pay_status
 * @property string $paid_at
 * @property string $created_at
 */
class Charge extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%charge}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_no', 'account', 'amount', 'pay_type', 'pay_status'], 'required'],
            [['amount'], 'integer'],
            [['paid_at', 'created_at'], 'safe'],
            [['order_no', 'account'], 'string', 'max' => 30],
            [['pay_type', 'pay_status'], 'string', 'max' => 15]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_no' => '订单号',
            'account' => '充值帐号',
            'amount' => '金额',
            'pay_type' => '支付方式',
            'pay_status' => '支付状态',
            'paid_at' => '支付时间',
            'created_at' => '创建时间',
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
