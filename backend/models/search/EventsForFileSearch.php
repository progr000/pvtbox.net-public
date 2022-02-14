<?php

namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UserFileEvents;

/**
 * UsersSearch represents the model behind the search form about common\models\Users.
 */
class EventsForFileSearch extends UserFileEvents
{
    public $_node_name;

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
        $queryEvents = UserFileEvents::find()->with('node')
            ->where([
                'user_id' => $UserFile->user_id,
                'file_id' => $UserFile->file_id
            ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $queryEvents,
            'sort'=> false,
            'pagination' => false,
        ]);

        return $dataProvider;
    }

}
