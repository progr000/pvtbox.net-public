<?php

use yii\db\Migration;

/**
 * Class m190214_101557_record_copy
 */
class m190214_101557_record_copy extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TYPE record_copy ADD ATTRIBUTE event_group_timestamp bigint;");
        $this->execute("ALTER TYPE record_copy ADD ATTRIBUTE event_group_id bigint;");
    }


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190214_101557_record_copy cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190214_101557_record_copy cannot be reverted.\n";

        return false;
    }
    */
}
