<?php
namespace frontend\widgets\news;

use Yii;
use frontend\models\search\NewsSearch;

use yii\base\Widget;
use yii\helpers\Html;
use yii\widgets\ListView;


class NewsWidget extends Widget
{
    public $message;
    private $dataProvider;

    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = 'Hello World';
        }

        $searchModel = new NewsSearch();
        $this->dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    }

    public function run()
    {
        //return Html::encode($this->message);
        return ListView::widget([
            'dataProvider' => $this->dataProvider,
            'itemOptions' => ['class' => 'item'],
            'layout' => "{items}",
            //'layout' => "{pager}\n{summary}\n{items}\n{pager}",
            //'summary' => 'Показано {count} из {totalCount}',
            'itemView' => function ($model, $key, $index, $widget) {
                return "<br /><div style='background-color: #9acfea; padding: 2px; color: #0000CC; font-weight: bold;'>".
                //Html::a(Html::encode($model->news_name." ({$model->news_created})"), ['view', 'id' => $model->news_id]).
                Html::encode($model->news_name." ({$model->news_created})").
                "</div><div style='border: 1px dotted #000000; padding: 5px;'>".nl2br($model->news_text)."</div>";
            },
        ]);
    }
}
