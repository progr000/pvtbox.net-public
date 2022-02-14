<?php

namespace frontend\modules\cryptonator\controllers;

use Yii;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\Controller;
use frontend\models\CryptonatorApi;
use yii\web\BadRequestHttpException;

/**
 * Default controller for the PayPal module
 */
class DefaultController extends Controller
{

    /**
     * Позволяет приходить запросам на этот скрипт и акшены перечисленные в массиве
     * с других доменов а не только с домена где стоит этот скрипт
     *
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (in_array($action->id, ['index'])) {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /*
    public function actionIndex()
    {
        $str = $this->noIndex();

        Yii::$app->paypal->logging('IPN_DEBUG.txt', $str);

        return $str;
    }
    */

    /**
     * @return string
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_RAW;

        $cryptonatorApi = new CryptonatorApi([
            'merchant_id',
            'invoice_id',
            'invoice_amount',
            'invoice_currency',
            'invoice_status',
            'checkout_address',
            'checkout_amount',
            'checkout_currency',
            'order_id',
            'secret_hash',
        ]);
        $post = Yii::$app->request->post();
        if ($cryptonatorApi->load(['CryptonatorApi' => $post]) && $cryptonatorApi->validate()) {

            /* проверка хеша */
            $calc_hash = $cryptonatorApi->generateHash();
            if ($calc_hash === $cryptonatorApi->secret_hash) {

                $cryptonatorApi->raw_data = var_export($post, true);
                $ret = $cryptonatorApi->registerNotification();
                if ($ret['status']) {
                    /* ++Запись в лог данных о платеже */
                    $messageLog = [
                        'status' => 'Платеж успешно прошел.',
                        'post' => $post,
                    ];
                    Yii::info($messageLog, 'payment_success');
                    /* -- */
                } else {
                    /* ++Запись в лог данных о платеже */
                    $messageLog = [
                        'status' => 'Платеж не удалось завершить.',
                        'post' => $post,
                        'info' => $ret['info'],
                    ];
                    Yii::warning($messageLog, 'payment_fail');
                    /* -- */
                }
                return Json::encode($ret);
                //return "OK";
            } else {
                /* ++Запись в лог данных о платеже */
                $messageLog = [
                    'status' => 'Платеж не прошел. Не совпадает secret_hash',
                    'post' => $post,
                    'secret_hash' => [
                        'received'   => $cryptonatorApi->secret_hash,
                        'calculated' => $calc_hash,
                    ],
                    'errors' => $cryptonatorApi->getErrors(),
                ];
                Yii::warning($messageLog, 'payment_fail'); //запись в лог
                /* -- */

                throw new ForbiddenHttpException('403 - wrong secret_hash');
            }

        } else {
            /* ++Запись в лог данных о платеже */
            $messageLog = [
                'status' => 'Платеж не прошел.',
                'post' => $post,
                'errors' => $cryptonatorApi->getErrors(),
            ];
            Yii::warning($messageLog, 'payment_fail'); //запись в лог
            /* -- */

            throw new BadRequestHttpException('400 - _POST data validation error (' . Json::encode($cryptonatorApi->getErrors()) . ')');

        }

    }

}
