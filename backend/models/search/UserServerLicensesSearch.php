<?php

namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UserServerLicenses;

/**
 * UsersSearch represents the model behind the search form about common\models\Users.
 */
class UserServerLicensesSearch extends UserServerLicenses
{
    public $tab;
    public $lic_srv_colleague_email;
    public $node_name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        //return parent::rules();
        return [
            [['tab', 'lic_srv_colleague_email', 'node_name'], 'safe'],
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
            ->alias('t1')
            ->select('t1.*, t2.user_email as lic_srv_colleague_email, t3.node_name')
            ->leftJoin('{{%users}} as t2', 't1.lic_srv_colleague_user_id = t2.user_id')
            ->leftJoin('{{%user_node}} as t3', 't1.lic_srv_node_id = t3.node_id')
            ->where(['lic_srv_owner_user_id' => $user_id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'params' => array_merge($_GET, ['tab' => 'licenses-info', '#' => 'licenses-info']),
                'sortParam' => 'sort-usl',
                'defaultOrder' => ['lic_srv_id'=>SORT_ASC],
                'attributes' => [
                    'lic_srv_id',
                    'lic_srv_colleague_email',
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

        $query->andFilterWhere(['like', 'lic_srv_colleague_email', $this->lic_srv_colleague_email]);

        return $dataProvider;
    }
}
