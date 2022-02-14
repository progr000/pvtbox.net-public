<?php

namespace backend\models\search;

use Yii;
use yii\helpers\Json;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\helpers\Functions;
use common\models\UserFiles;
use common\models\UserNode;

/**
 * UsersSearch represents the model behind the search form about common\models\Users.
 */
class NodesWithFileSearch extends Model
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
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
     * @param \common\models\UserFiles $UserFile
     * @return ActiveDataProvider
     */
    public function search($UserFile)
    {
        $queryNode = UserNode::find()
            ->where(['node_id'   => $UserFile->node_id]);


        $lastEvent = UserFiles::last_event_as_object($UserFile->file_id);
        if ($lastEvent) {
            //$lastEvent->event_uuid = 'eebf84feacbe497496546f9e3fc74ab5';
            //$lastEvent->diff_file_uuid = 'd6a8d6e6145a3ed0314af5c25f8a6247';

            $query = "SELECT object_id, nid FROM \"download:end\" WHERE (object_id='{$lastEvent->event_uuid}') OR (object_id='{$lastEvent->diff_file_uuid}')";

            $url = "http://ip2.2nat.biz:8086/query?pretty=true&db=telegraf&q=" . urlencode($query);
            $res = Functions::HttpGet($url, Yii::$app->params['LogAuthUser'], Yii::$app->params['LogAuthPasswd']);

            $node_hash = [];
            $jsonres = Json::decode($res);
            if (is_array($jsonres) && isset($jsonres['results'][0]['series'][0]['columns'], $jsonres['results'][0]['series'][0]['values'])) {
                $columns = $jsonres['results'][0]['series'][0]['columns'];
                $values  = $jsonres['results'][0]['series'][0]['values'];
                foreach ($values as $k=>$v) {
                    $node_hash[] = $v[2];
                }
            }

            if (sizeof($node_hash)) {
                $queryNode->orWhere(['node_hash' => $node_hash]);
            }

        }

        $dataProvider = new ActiveDataProvider([
            'query' => $queryNode,
            'sort'=> false,
            'pagination' => false,
        ]);

        return $dataProvider;
    }

}
