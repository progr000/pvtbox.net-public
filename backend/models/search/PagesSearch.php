<?php

namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Pages;

/**
 * PagesSearch represents the model behind the search form about common\models\Pages.
 */
class PagesSearch extends Pages
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['page_id', 'page_status'], 'integer'],
            [['page_created', 'page_updated', 'page_lang', 'page_title', 'page_name', 'page_alias', 'page_keywords', 'page_description', 'page_text'], 'safe'],
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
        $query = Pages::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 100,
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
            'page_id' => $this->page_id,
            'page_created' => $this->page_created,
            'page_updated' => $this->page_updated,
            'page_status' => $this->page_status,
            'page_lang' => $this->page_lang,
        ]);

        $query->andFilterWhere(['like', 'page_title', $this->page_title])
            ->andFilterWhere(['like', 'page_name', $this->page_name])
            ->andFilterWhere(['like', 'page_alias', $this->page_alias])
            ->andFilterWhere(['like', 'page_keywords', $this->page_keywords])
            ->andFilterWhere(['like', 'page_description', $this->page_description])
            ->andFilterWhere(['like', 'page_text', $this->page_text]);

        return $dataProvider;
    }
}
