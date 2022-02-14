<?php

use yii\db\Migration;

/**
 * Class m180206_100054_record_copy
 */
class m180206_100054_record_copy extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute(" ALTER TYPE record_copy ADD ATTRIBUTE parent_before_event bigint;");
        $this->execute(" ALTER TYPE record_copy ADD ATTRIBUTE parent_after_event bigint;");
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180206_100054_record_copy cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180206_100054_record_copy cannot be reverted.\n";

        return false;
    }
    */
}
