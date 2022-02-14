<?php

namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UserCollaborations;
use common\models\UserColleagues;

/**
 * UsersSearch represents the model behind the search form about common\models\Users.
 */
class UserCollaborationsSearch extends UserCollaborations
{
    public $tab;
    public $file_name;
    public $file_id;
    public $_file_name;
    public $collaboration_owner_or_colleague;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        //return parent::rules();
        return [
            [['tab', 'file_uuid', 'file_name', 'file_id', '_file_name', 'collaboration_id'], 'safe'],
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
        //$query = "SELECT co"
        $query = self::find()
            ->alias('t1')
            ->select('t1.collaboration_id, t2.file_id, t2.file_uuid, t2.file_name, t1.user_id')
            ->innerJoin('{{%user_files}} as t2', '(t1.file_uuid = t2.file_uuid) AND (t1.user_id = t2.user_id)')
            ->where(['t1.user_id' => $user_id])
            ->orWhere("(t1.collaboration_id IN (SELECT collaboration_id FROM {{%user_colleagues}} WHERE (user_id = :user_id) AND (colleague_permission != :owner)))", [
                'user_id' => $user_id,
                'owner'   => UserColleagues::PERMISSION_OWNER,
            ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'params' => array_merge($_GET, ['tab' => 'collaborations-info', '#' => 'collaborations-info']),
                'sortParam' => 'sort-p5',
                'defaultOrder' => ['collaboration_id'=>SORT_ASC],
                'attributes' => [
                    'collaboration_id',
                    //'t2.file_name',
                    '_file_name' => [
                        'asc'  => ['t2.file_name' => SORT_ASC],
                        'desc' => ['t2.file_name' => SORT_DESC],
                    ],
                ]
            ],
            'pagination' => [
                'params' => array_merge($_GET, ['tab' => 'collaborations-info', '#' => 'collaborations-info']),
                'pageParam' => 'p5',
                'pageSizeParam' => 'per-p5',
                'pageSize' => 100,
                //'route' => \yii\helpers\Url::current(['tab' => 'licenses-info', 'id' => $user_id])
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 't2.file_name', $this->file_name]);
        $query->andFilterWhere(['t1.collaboration_id' => $this->collaboration_id]);

        return $dataProvider;
    }
}
