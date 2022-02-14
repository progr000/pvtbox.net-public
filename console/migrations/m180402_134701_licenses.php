<?php

use yii\db\Migration;

/**
 * Class m180402_134701_licenses
 */
class m180402_134701_licenses extends Migration
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
            ALTER TABLE {$tablePrefix}licenses ADD COLUMN license_count_available SMALLINT DEFAULT '0'::SMALLINT NOT NULL;
            ALTER TABLE {$tablePrefix}users ADD COLUMN license_count_available SMALLINT DEFAULT '0'::SMALLINT NOT NULL;
            ALTER TABLE {$tablePrefix}users ADD COLUMN license_count_used SMALLINT DEFAULT '0'::SMALLINT NOT NULL;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180402_134701_licenses cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180402_134701_licenses cannot be reverted.\n";

        return false;
    }
    */
}
