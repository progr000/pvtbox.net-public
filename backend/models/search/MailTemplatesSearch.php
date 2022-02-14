<?php

namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\MailTemplates;

/**
 * MailTemplatesSearch represents the model behind the search form about common\models\MailTemplates.
 */
class MailTemplatesSearch extends MailTemplates
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['template_id'], 'integer'],
            [['template_key', 'template_lang', 'template_from_email', 'template_from_name', 'template_subject', 'template_body_html', 'template_body_text'], 'safe'],
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
        $query = MailTemplates::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'template_id' => $this->template_id,
        ]);

        $query->andFilterWhere(['like', 'template_key', $this->template_key])
            ->andFilterWhere(['like', 'template_lang', $this->template_lang])
            ->andFilterWhere(['like', 'template_from_email', $this->template_from_email])
            ->andFilterWhere(['like', 'template_from_name', $this->template_from_name])
            ->andFilterWhere(['like', 'template_subject', $this->template_subject])
            ->andFilterWhere(['like', 'template_body_html', $this->template_body_html])
            ->andFilterWhere(['like', 'template_body_text', $this->template_body_text]);

        return $dataProvider;
    }
}
