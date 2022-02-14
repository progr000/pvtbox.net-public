<?php

namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UserColleagues;

/**
 * UsersSearch represents the model behind the search form about common\models\Users.
 */
class ColleaguesForCollaborationSearch extends UserColleagues
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
     * @param \common\models\UserCollaborations $UserCollaboration
     * @return ActiveDataProvider
     */
    public function search($UserCollaboration)
    {
        $queryEvents = self::find()
            ->where([
                'collaboration_id' => $UserCollaboration->collaboration_id,
            ])
            /*
            ->andWhere('(user_id != :user_id) OR (user_id IS NULL)', [
                'user_id' => $UserCollaboration->user_id,
            ])*/;

        $dataProvider = new ActiveDataProvider([
            'query' => $queryEvents,
            'sort'=> false,
            'pagination' => false,
        ]);

        return $dataProvider;
    }

}
