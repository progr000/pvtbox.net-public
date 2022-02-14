<?php

use yii\db\Migration;
use common\models\Preferences;

/**
 * Class m180526_203042_preferences
 */
class m180526_203042_preferences extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $pref_init = [
            'BonusTrialForEmailConfirm'     => ['category' => Preferences::CATEGORY_PRICES,    'title' => 'Бонус к триал лицензии за подтверждение емела (в днях)', 'value' => '14'],
            'CountSharesForFreeIn24Hours'   => ['category' => Preferences::CATEGORY_PRICES,    'title' => 'Количество разрешенных шар для FREE лицензии', 'value' => '4'],
        ];

        foreach ($pref_init as $k=>$v) {
            $pref = new Preferences();
            $pref->pref_key = $k;
            $pref->pref_category = $v['category'];
            $pref->pref_title = mb_substr($v['title'], 0, 255);
            $pref->pref_value = $v['value'];
            if ($pref->save()) {
                echo "\nCreated new record in {{%preferences}} with key <{$k}> ; \n\n";
            } else {
                echo "\n\n";
                var_dump($pref->getErrors());
                echo "\n\n";
                //return false;
            }
            unset($pref);
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180526_203042_preferences cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180526_203042_preferences cannot be reverted.\n";

        return false;
    }
    */
}
