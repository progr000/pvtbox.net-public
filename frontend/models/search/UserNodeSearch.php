<?php

namespace frontend\models\search;

use Yii;
use yii\data\ActiveDataProvider;
use common\models\UserNode;
use yii\helpers\Json;

/**
 * TransfersSearch represents the model behind the search form about common\models\Transfers.
 */
class UserNodeSearch extends UserNode
{
    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = UserNode::find()->indexBy('node_id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => ['node_id'=>SORT_DESC],
                'attributes' => [
                    'node_id',
                    'node_name',
                    'node_last_ip',
                    'node_countrycode',
                    'node_country',
                    'node_city',
                    'node_online',
                    'node_updated',
                    'node_useragent',
                    'node_osname',
                    'node_ostype',
                    'node_devicetype',
                    'node_upload_speed',
                    'node_download_speed',
                    'node_disk_usage',
                    'node_status',
                ]
            ],

            //'pagination' => [ 'pageSize' => 10, ],

            'pagination' => false,
        ]);

        /*
        $query->andWhere('node_status NOT IN (:DELETED, :DEACTIVATED, :WIPED)', [
            'DELETED' => self::STATUS_DELETED,
            'DEACTIVATED' => self::STATUS_DEACTIVATED,
            'WIPED' => self::STATUS_WIPED,
        ]);
        */
        $query->andWhere('node_status NOT IN (:DELETED, :DEACTIVATED) AND NOT((node_prev_status = :DEACTIVATED) AND (node_status IN (:LOGGEDOUT, :WIPED)))', [
            'DELETED'     => self::STATUS_DELETED,
            'DEACTIVATED' => self::STATUS_DEACTIVATED,
            'LOGGEDOUT'   => self::STATUS_LOGGEDOUT,
            'WIPED'       => self::STATUS_WIPED,
        ]);
        //$query->andWhere('node_ostype!=:node_ostype', ['node_ostype' => self::OSTYPE_WEBFM]);
        //$query->andFilterWhere(['node_status' => UserNode::STATUS_ACTIVE]);
        //$query->andWhere('node_status != :node_status', ['node_status' => UserNode::STATUS_DELETED]);
        $query->andFilterWhere($params);

        return $dataProvider;
    }

    public function getAll()
    {
        return Json::encode(
            UserNode::find()
                ->select([
                    'node_city',
                    //'node_country',
                    //'node_countrycode',
                    //'node_created',
                    'node_devicetype',
                    'node_disk_usage',
                    'node_download_speed',
                    'node_id',
                    'node_last_ip',
                    'node_logout_status',
                    'node_name',
                    'node_online',
                    'node_osname',
                    'node_ostype',
                    'node_status',
                    //'node_updated',
                    'node_upload_speed',
                    'node_useragent',
                    'node_wipe_status',
                    //'user_id',
                ])
                ->andWhere('node_status NOT IN (:DELETED, :DEACTIVATED, :WIPED)', [
                    'DELETED' => self::STATUS_DELETED,
                    'DEACTIVATED' => self::STATUS_DEACTIVATED,
                    'WIPED' => self::STATUS_WIPED,
                ])
                ->andWhere(['user_id' => Yii::$app->user->identity->getId()])
                //->asArray()
                ->all()
        );
    }

    /**
     * @return integer
     */
    public static function countOnlineNodes()
    {
        return UserNode::find()
            ->where([
                'user_id'     => Yii::$app->user->identity->getId(),
                'node_online' => UserNode::ONLINE_ON,
            ])
            ->andWhere('node_status != :node_status', ['node_status' => UserNode::STATUS_DELETED])
            ->count();
    }

}
