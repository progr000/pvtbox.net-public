<?php

namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Servers;

/**
 * ServersSearch represents the model behind the search form about common\models\Servers.
 */
class ServersSearch extends Servers
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['server_id', 'server_port', 'server_status'], 'integer'],
            [['server_type', 'server_title', 'server_url', 'server_ip'], 'safe'],
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
        $query = Servers::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => ['server_type'=>SORT_DESC],
                'attributes' => [
                    'server_id',
                    'server_type',
                    'server_status',
                ]
            ],
            'pagination' => [
                'pageSize' => 100,
                'route'=>'servers/index',
            ],

        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'server_id' => $this->server_id,
            'server_port' => $this->server_port,
            'server_status' => $this->server_status,
        ]);

        $query->andFilterWhere(['like', 'server_type', $this->server_type])
            ->andFilterWhere(['like', 'server_title', $this->server_title])
            ->andFilterWhere(['like', 'server_url', $this->server_url])
            ->andFilterWhere(['like', 'server_ip', $this->server_ip]);

        return $dataProvider;
    }
}
