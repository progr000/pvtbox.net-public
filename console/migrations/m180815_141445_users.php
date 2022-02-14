<?php

use yii\db\Migration;

/**
 * Class m180815_141445_users
 */
class m180815_141445_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE ONLY {{%users}} ALTER COLUMN pay_type SET DEFAULT 'not_set';");
        $this->execute("UPDATE {{%users}} SET pay_type = 'not_set';");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180815_141445_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180815_141445_users cannot be reverted.\n";

        return false;
    }
    */
}
