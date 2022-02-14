<?php

namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UserLicenses;

/**
 * UsersSearch represents the model behind the search form about common\models\Users.
 */
class UserLicensesSearch extends UserLicenses
{
    public $tab;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        //return parent::rules();
        return [
            [['tab', 'lic_colleague_email'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return parent::attributeLabels();
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
     * @param integer $user_id
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($user_id, $params)
    {
        $query = self::find()
            ->where(['lic_owner_user_id' => $user_id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'params' => array_merge($_GET, ['tab' => 'licenses-info', '#' => 'licenses-info']),
                'sortParam' => 'sort-p4',
                'defaultOrder' => ['lic_id'=>SORT_ASC],
                'attributes' => [
                    'lic_id',
                    'lic_colleague_email',
                ]
            ],
            'pagination' => false,
            /*
            'pagination' => [
                'params' => array_merge($_GET, ['tab' => 'licenses-info', '#' => 'licenses-info']),
                'pageParam' => 'p-lic-inf',
                'pageSizeParam' => 'per-p-lic-inf',
                'pageSize' => 100,
                //'route' => \yii\helpers\Url::current(['tab' => 'licenses-info', 'id' => $user_id])
            ],
            */
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'lic_colleague_email', $this->lic_colleague_email]);

        return $dataProvider;
    }
}
