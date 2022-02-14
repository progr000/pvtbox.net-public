<?php
/****DELETE-IT-FILE-IN-SH****/
namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\News;

/**
 * NewsSearch represents the model behind the search form about common\models\News.
 */
class NewsSearch extends News
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['news_id', 'news_status'], 'integer'],
            [['news_name', 'news_text', 'news_created', 'news_updated'], 'safe'],
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
        $query = News::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => ['news_id'=>SORT_DESC],
                'attributes' => [
                    'news_id',
                    'news_status',
                    'news_created',
                    'news_updated',
                ]
            ],
            'pagination' => [
                'pageSize' => 100,
                'route'=>'news/index',
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'news_id' => $this->news_id,
            'news_status' => $this->news_status,
            'news_created' => $this->news_created,
            'news_updated' => $this->news_updated,
        ]);


        $query->andFilterWhere(['like', 'news_name', $this->news_name])
              ->andFilterWhere(['like', 'news_text', $this->news_text]);

        //$query->addOrderBy(['news_created'=>SORT_DESC]);

        return $dataProvider;
    }
}
