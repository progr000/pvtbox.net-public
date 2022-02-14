<?php
namespace frontend\components;

use Yii;
use yii\caching\TagDependency;
use yii\web\UrlRule;
use yii\helpers\Url;
use common\models\Pages;

class UrlRouter extends UrlRule
{
    public function init()
    {

    }

    public function createUrl($manager, $route, $params)
    {
        return false;
    }

    public function parseRequest($manager, $request)
    {
        $url = explode('/', Url::to(''));
        $tmp = array_pop($url);
        $tmp = ($tmp) ? $tmp : 'index';
        $tmp = explode('?', $tmp);
        $alias = $tmp[0];
        $controller = array_pop($url);
        //var_dump($controller);exit;
        //var_dump(Yii::$app->language); exit;

        if (!Yii::$app->createControllerByID($alias)) {
            $controller = 'page';
        }
        //var_dump($alias); exit;
        //+++ Page +++
        if (in_array($controller, ['p', 'page', 'pages'])) {

            $model = Pages::getDb()->cache(
                function($db) use($alias) {
                    $Page = Pages::find()->where('(page_alias = :name) AND (page_lang = :lang) AND (page_status = :status)', [
                        ':name' => $alias,
                        ':status' => Pages::STATUS_ACTIVE,
                        ':lang' => Yii::$app->language
                    ])->one();
                    if (!$Page) {
                        $Page = Pages::find()->where('(page_alias = :name) AND (page_lang = :lang) AND (page_status = :status)', [
                            ':name' => $alias,
                            ':status' => Pages::STATUS_ACTIVE,
                            ':lang' => 'en'
                        ])->one();
                    }
                    return $Page;
                },
                null,
                new TagDependency(['tags'  => md5( 'page' . $alias . Yii::$app->language )])
            );

            //$model = Pages::find()->where('(page_alias = :name) AND (page_lang = :lang)', [':name' => $alias, ':lang' => Yii::$app->language])->one();
            if ($model !== null) {
                $params['alias'] = $model->page_alias;
                Yii::$app->session->set('page_controller_alias', $model->page_alias);
                return ['/page/index', $params];
            }
        }
        //--- Page ---

        return false;
    }
}

