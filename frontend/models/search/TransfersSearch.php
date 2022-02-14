<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Transfers;

/**
 * TransfersSearch represents the model behind the search form about common\models\Transfers.
 */
class TransfersSearch extends Transfers
{
    public $_user_email;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['transfer_id', 'user_id', 'transfer_type', 'transfer_status'], 'integer'],
            [['transfer_sum'], 'number'],
            [['transfer_created', 'transfer_updated'], 'safe'],
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
        $query = self::find();
        $query->andFilterWhere($params);
        $query->addOrderBy(['transfer_created'=>SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $dataProvider;
    }
}
