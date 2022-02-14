<?php

use yii\db\Migration;
use common\models\Preferences;

/**
 * Class m190529_091036_preferences
 */
class m190529_091036_preferences extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $pref_init = [
            'PriceOneTimeForLicenseProfessional' => ['category' => Preferences::CATEGORY_PRICES, 'title' => 'Professional license price One-time-Pay', 'value' => '69.99'],
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
        echo "m190529_091036_preferences cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190529_091036_preferences cannot be reverted.\n";

        return false;
    }
    */
}
