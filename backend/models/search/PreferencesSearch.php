<?php

namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Preferences;

/**
 * PreferencesSearch represents the model behind the search form about common\models\Preferences.
 */
class PreferencesSearch extends Preferences
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pref_id', 'pref_category'], 'integer'],
            [['pref_title', 'pref_key', 'pref_value'], 'safe'],
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
        $query = Preferences::find()
            ->where('pref_category != :CATEGORY_HIDDEN', [
                'CATEGORY_HIDDEN' => self::CATEGORY_HIDDEN
            ])
            ->indexBy('pref_id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => ['pref_id'=>SORT_DESC],
                'attributes' => [
                    'pref_id',
                    'pref_key',
                    'pref_category',
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'pref_id' => $this->pref_id,
            'pref_category' => $this->pref_category,
        ]);

        $query->andFilterWhere(['like', 'pref_title', $this->pref_title])
              ->andFilterWhere(['like', 'pref_key', $this->pref_key]);

        return $dataProvider;
    }

    /**
     * @return array
     */
    public static function getPrefArray()
    {
        $preferences = [];
        $labels = self::categoriesLabels();

        foreach ($labels as $category => $category_name) {
            $preferences[$category] = self::find()
                ->where(['pref_category' => $category])
                ->orderBy(['pref_key' => SORT_ASC, 'pref_id' => SORT_ASC])
                ->all();
        }

        return $preferences;
    }
}
