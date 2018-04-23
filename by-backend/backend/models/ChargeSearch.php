<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Charge;

/**
 * ChargeSearch represents the model behind the search form about `backend\models\Charge`.
 */
class ChargeSearch extends Charge
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'amount'], 'integer'],
            [['order_no', 'account', 'pay_type', 'pay_status', 'paid_at', 'created_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Charge::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'amount' => $this->amount,
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'order_no', $this->order_no])
            ->andFilterWhere(['like', 'account', $this->account])
            ->andFilterWhere(['like', 'pay_type', $this->pay_type])
            ->andFilterWhere(['like', 'pay_status', $this->pay_status]);

        return $dataProvider;
    }
}
