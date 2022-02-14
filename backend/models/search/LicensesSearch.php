<?php
/****DELETE-IT-FILE-IN-SH****/
namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Licenses;

/**
 * LicensesSearch represents the model behind the search form about common\models\Licenses.
 */
class LicensesSearch extends Licenses
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['license_id'], 'integer'],
            [['license_type', 'license_description'], 'safe'],
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
        $query = Licenses::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => ['license_id'=>SORT_DESC],
                'attributes' => [
                    'license_id',
                    'license_description',
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
            'license_id' => $this->license_id,
        ]);

        $query->andFilterWhere(['like', 'license_type', $this->license_type])
            ->andFilterWhere(['like', 'license_description', $this->license_description]);

        return $dataProvider;
    }
}
