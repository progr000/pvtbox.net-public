<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use common\models\Tikets;
use common\models\TiketsMessages;

/**
 * TiketsSearch represents the model behind the search form about common\models\Tikets.
 */
class TiketsSearch extends Tikets
{
    public $showNew;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tiket_id', 'showNew', 'tiket_count_new_user', 'tiket_count_new_admin', 'user_id'], 'integer'],
            [['tiket_created', 'tiket_theme', 'tiket_email', 'tiket_name'], 'safe'],
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
        $query = Tikets::find()->where(['user_id' => Yii::$app->user->identity->getId()]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => ['tiket_count_new_admin'=>SORT_DESC],
                'attributes' => [
                    'tiket_id',
                    'tiket_created',
                    'tiket_email',
                    'tiket_theme',
                    'tiket_count_new_admin',
                ]
            ],
            'pagination' => [
                'pageSize' => 20,
                'route'=>'tikets/index',
            ],

        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'tiket_id' => $this->tiket_id,
            'tiket_created' => $this->tiket_created,
            'user_id' => $this->user_id,
        ]);

        if ($this->showNew > 0) {
            $query->andWhere('tiket_count_new_user >= 1');
        }

        $query->andFilterWhere(['like', 'tiket_theme', $this->tiket_theme]);
            //->andFilterWhere(['like', 'tiket_email', $this->tiket_email])
            //->andFilterWhere(['like', 'tiket_name', $this->tiket_name]);

        return $dataProvider;
    }

    /**
     * @param \common\models\Tikets $tiket
     * @return ArrayDataProvider
     */
    public function viewTiketsMessages($tiket)
    {
        $user_id = Yii::$app->user->identity->getId();

        $query = TiketsMessages::find()
            ->alias('t1')
            ->select('
                t1.*,
                t2.tiket_theme, t2.tiket_email, t2.tiket_name,
                t3.user_name, t3.user_email
            ')
            ->innerJoin('{{%tikets}} as t2', 't1.tiket_id = t2.tiket_id')
            ->leftJoin('{{%users}} as t3', 't1.user_id = t3.user_id')
            ->andwhere(['t2.tiket_id' => $tiket->tiket_id])
            ->andWhere(['t2.user_id' => $user_id])
            ->orderBy(['message_created' => SORT_ASC])
            ->asArray()->all();

        $dataProvider = new ArrayDataProvider([
            'allModels' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],

        ]);

        $tiket->tiket_count_new_user = 0;
        $tiket->save();
        TiketsMessages::updateAll(['message_read_user' => 1], ['tiket_id' => $tiket->tiket_id]);

        return $dataProvider;
    }

    /**
     * @return integer
     */
    public static function countUnreadTikets()
    {
        return Tikets::find()
                ->andWhere(['user_id' => Yii::$app->user->identity->getId()])
                ->andWhere('tiket_count_new_user > 0')
                ->count();
    }
}
