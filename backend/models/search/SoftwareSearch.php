<?php
/****DELETE-IT-FILE-IN-SH****/
namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Software;

/**
 * SoftwareSearch represents the model behind the search form about common\models\Software.
 */
class SoftwareSearch extends Software
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['software_id', 'software_status'], 'integer'],
            [['software_type', 'software_description', 'software_file_name', 'software_version', 'software_created', 'software_updated'], 'safe'],
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
        $query = Software::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => ['software_sort'=>SORT_ASC],
                'attributes' => [
                    'software_id',
                    'software_sort',
                    'software_status',
                    'software_type',
                    'software_version',
                    'software_created',
                    'software_updated',
                ]
            ],
            /*
            'pagination' => [
                'pageSize' => 100,
                'route' => 'software/index',
            ],
            */
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'software_id' => $this->software_id,
            'software_created' => $this->software_created,
            'software_updated' => $this->software_updated,
            'software_status' => $this->software_status,
        ]);

        $query->andFilterWhere(['like', 'software_type', $this->software_type])
            ->andFilterWhere(['like', 'software_description', $this->software_description])
            ->andFilterWhere(['like', 'software_file_name', $this->software_file_name])
            ->andFilterWhere(['like', 'software_version', $this->software_version]);

        return $dataProvider;
    }
}
