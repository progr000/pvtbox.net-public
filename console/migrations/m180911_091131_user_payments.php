<?php

use yii\db\Migration;

/**
 * Class m180911_091131_user_payments
 */
class m180911_091131_user_payments extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tablePrefix = isset(Yii::$app->components['db']['tablePrefix'])
            ? Yii::$app->components['db']['tablePrefix']
            : '';

        $this->db->pdo->exec("
            ALTER TABLE {$tablePrefix}user_payments ADD COLUMN license_period smallint NOT NULL DEFAULT '0'::smallint;
            ALTER TABLE {$tablePrefix}user_payments ADD COLUMN license_expire timestamp without time zone;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180911_091131_user_payments cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180911_091131_user_payments cannot be reverted.\n";

        return false;
    }
    */
}
