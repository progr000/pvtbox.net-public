<?php

use yii\db\Migration;

/**
 * Class m181109_143837_colleagues_reports
 */
class m181109_143837_colleagues_reports extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%colleagues_reports}} ADD column is_rollback smallint NOT NULL DEFAULT 0;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181109_143837_colleagues_reports cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181109_143837_colleagues_reports cannot be reverted.\n";

        return false;
    }
    */
}
