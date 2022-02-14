<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Transfers;

/**
 * TransfersSearch represents the model behind the search form about common\models\Transfers.
 */
class TrafSearch extends Transfers
{
    /**
     * @return array
     */
    public static function search()
    {
        $query_check = "SELECT to_regclass('dl_traf_info_t1') as t1;";
        $res_check = Yii::$app->db->createCommand($query_check)->queryOne();
        if (isset($res_check['t1'])) {
            $query = "SELECT * FROM traf_get_info();";
            $res = Yii::$app->db->createCommand($query)->queryOne();
            $check_sum = md5(serialize($res));
            $res['checksum'] = $check_sum;
            return $res;
        } else {
            Yii::$app->session->set('traf_today_amount_prev', Yii::$app->session->get('traf_today_amount_current', 500061));
            Yii::$app->session->set('traf_today_amount_current', Yii::$app->session->get('traf_today_amount_current', 500061) + 60);
            Yii::$app->session->set('traf_month_amount_prev', Yii::$app->session->get('traf_month_amount_current', 3000061));
            Yii::$app->session->set('traf_month_amount_current', Yii::$app->session->get('traf_month_amount_current', 3000061) + 60);
            Yii::$app->session->set('traf_total_amount_prev', Yii::$app->session->get('traf_total_amount_current', 40000061));
            Yii::$app->session->set('traf_total_amount_current',  Yii::$app->session->get('traf_total_amount_current', 40000061) + 60);
            $res = [
                "today_amount_prev"    => Yii::$app->session->get('traf_today_amount_prev', 500001),
                "today_amount_current" => Yii::$app->session->get('traf_today_amount_current', 500061),
                "month_amount_prev"    => Yii::$app->session->get('traf_month_amount_prev', 3000001),
                "month_amount_current" => Yii::$app->session->get('traf_month_amount_current', 3000061),
                "total_amount_prev"    => Yii::$app->session->get('traf_total_amount_prev', 40000001),
                "total_amount_current" => Yii::$app->session->get('traf_total_amount_current', 40000061),
                "time_interval" => 60,
            ];
            $check_sum = md5(serialize($res));
            $res['checksum'] = $check_sum;
            return $res;
        }
    }
}
