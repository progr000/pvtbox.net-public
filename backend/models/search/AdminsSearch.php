<?php

namespace backend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Admins;

/**
 * AdminsSearch represents the model behind the search form of `backend\models\Admins`.
 */
class AdminsSearch extends Admins
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['admin_id', 'admin_status', 'admin_role'], 'integer'],
            [['admin_name', 'admin_email', 'auth_key', 'password_hash', 'password_reset_token', 'admin_created', 'admin_updated'], 'safe'],
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
        $query = Admins::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'admin_id' => $this->admin_id,
            'admin_created' => $this->admin_created,
            'admin_updated' => $this->admin_updated,
            'admin_status' => $this->admin_status,
            'admin_role' => $this->admin_role,
        ]);

        $query->andFilterWhere(['ilike', 'admin_name', $this->admin_name])
            ->andFilterWhere(['ilike', 'admin_email', $this->admin_email])
            ->andFilterWhere(['ilike', 'auth_key', $this->auth_key])
            ->andFilterWhere(['ilike', 'password_hash', $this->password_hash])
            ->andFilterWhere(['ilike', 'password_reset_token', $this->password_reset_token]);

        return $dataProvider;
    }
}
