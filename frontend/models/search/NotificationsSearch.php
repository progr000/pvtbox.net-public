<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Notifications;

/**
 * NotificationsSearch represents the model behind the search form about common\models\Notifications.
 *
 * @property integer $_notif_date_ts
 *
 */
class NotificationsSearch extends Notifications
{
    public $_notif_date_ts;
    public $showNew;

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
     * @return ActiveDataProvider
     */
    public function search()
    {
        $user_id = Yii::$app->user->identity->getId();
        $query = self::find()->where(['user_id' => $user_id]);

        // add conditions that should always apply here
        //self::countToRedis($user_id, 0);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => ['notif_date'=>SORT_DESC, 'notif_id' => SORT_DESC],
                'attributes' => [
                    'notif_date',
                    'notif_id',
                ]
            ],
            'pagination' => [
                'pageSize' => 10,
            ],

        ]);


        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        return $dataProvider;
    }



    /**
     * @return integer
     */
    public static function countNewNotifications()
    {
        return self::find()
            ->andWhere('(user_id=:user_id) AND (notif_isnew=:notif_isnew)', [
                'user_id'     => Yii::$app->user->identity->getId(),
                'notif_isnew' => self::IS_NEW,
            ])
            ->count();
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
        $this->_notif_date_ts = strtotime($this->notif_date) + Yii::$app->session->get('UserTimeZoneOffset', 0);
    }
}
