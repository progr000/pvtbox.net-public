<?php

use yii\db\Migration;
use common\models\Preferences;

/**
 * Class m181203_133042_licenses
 */
class m181203_133042_licenses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tablePrefix = isset(Yii::$app->components['db']['tablePrefix'])
            ? Yii::$app->components['db']['tablePrefix']
            : '';

        Yii::$app->db->pdo->exec("
            ALTER TABLE {$tablePrefix}licenses ADD COLUMN license_shares_count_in24 SMALLINT DEFAULT '0'::SMALLINT NOT NULL;
            ALTER TABLE {$tablePrefix}licenses ADD COLUMN license_max_shares_size bigint DEFAULT '0'::bigint NOT NULL;
        ");

        Preferences::deleteAll(['pref_key' => 'CountSharesForFreeIn24Hours']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181203_133042_licenses cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181203_133042_licenses cannot be reverted.\n";

        return false;
    }
    */
}
