<?php

namespace backend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\BadLogins;

/**
 * SearchBadLogins represents the model behind the search form of `common\models\BadLogins`.
 */
class BadLoginsSearch extends BadLogins
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['bl_id', 'bl_count_tries', 'bl_last_timestamp', 'bl_locked', 'bl_lock_seconds'], 'integer'],
            [['bl_created', 'bl_updated', 'bl_ip', 'bl_type'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
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
        $query = BadLogins::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => [
                    'bl_locked'=>SORT_DESC,
                    'bl_lock_seconds' => SORT_DESC,
                    'bl_last_timestamp' => SORT_DESC,
                ],
                'attributes' => [
                    'bl_locked',
                    'bl_lock_seconds',
                    'bl_last_timestamp',
                    'bl_ip',
                    'bl_id',
                    'bl_count_tries',
                    'bl_created',
                    'bl_updated',
                    'bl_type',
                ]
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
            'bl_id' => $this->bl_id,
            'bl_created' => $this->bl_created,
            'bl_updated' => $this->bl_updated,
            'bl_count_tries' => $this->bl_count_tries,
            'bl_last_timestamp' => $this->bl_last_timestamp,
            'bl_locked' => $this->bl_locked,
            'bl_lock_seconds' => $this->bl_lock_seconds,
            'bl_type' => $this->bl_type,
        ]);

        $query->andFilterWhere(['ilike', 'bl_ip', $this->bl_ip]);

        return $dataProvider;
    }
}
