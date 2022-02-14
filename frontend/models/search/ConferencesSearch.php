<?php

namespace frontend\models\search;

use common\models\ConferenceParticipants;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UserConferences;

/**
 * ColleaguesSearch represents the model behind the search form about common\models\UserColleagues.
 */
class ConferencesSearch extends UserConferences
{

    public $your_user_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'conference_id', 'conference_status', 'your_user_id'], 'integer'],
            [[
                'conference_created',
                'conference_updated',
                'conference_participants',
                'room_uuid',
                'conference_name',
            ], 'safe'],
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
     * @param integer $user_id
     * @return ActiveDataProvider
     */
    public function search($user_id)
    {
        $query = self::find()
            ->select('t1.*, t2.participant_email, t2.participant_status, t2.user_id as your_user_id')
            ->alias('t1')
            ->innerJoin('{{%conference_participants}} as t2', 't1.conference_id = t2.conference_id')
            ->where([
                't2.user_id' => $user_id,
                't2.participant_status' => [
                    ConferenceParticipants::STATUS_OWNER,
                    ConferenceParticipants::STATUS_JOINED,
                    //ConferenceParticipants::STATUS_INVITED,
                ],
            ]);

        // add conditions that should always apply here
        //self::countToRedis($user_id, 0);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => ['conference_name'=>SORT_ASC],
                'attributes' => [
                    'conference_name',
                ]
            ],
            'pagination' => false,
//            'pagination' => [
//                'pageSize' => 17,
//            ],

        ]);


        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        return $dataProvider;
    }
}
