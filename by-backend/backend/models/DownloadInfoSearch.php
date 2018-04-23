<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\DownloadInfo;

/**
 * DownloadInfoSearch represents the model behind the search form about `backend\models\DownloadInfo`.
 */
class DownloadInfoSearch extends DownloadInfo
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'packagesize', 'uptime'], 'integer'],
            [['platform', 'version', 'lan', 'MD5', 'download1', 'download2'], 'safe'],
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
        $query = DownloadInfo::find();

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
            'packagesize' => $this->packagesize,
            'uptime' => $this->uptime,
        ]);

        $query->andFilterWhere(['like', 'platform', $this->platform])
            ->andFilterWhere(['like', 'version', $this->version])
            ->andFilterWhere(['like', 'lan', $this->lan])
            ->andFilterWhere(['like', 'MD5', $this->MD5])
            ->andFilterWhere(['like', 'download1', $this->download1])
            ->andFilterWhere(['like', 'download2', $this->download2]);

        return $dataProvider;
    }
}
