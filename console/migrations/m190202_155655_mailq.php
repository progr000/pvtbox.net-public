<?php

use yii\db\Migration;

/**
 * Class m190202_155655_mailq
 */
class m190202_155655_mailq extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%mailq}} ADD COLUMN remote_ip bigint NOT NULL DEFAULT '0'::bigint;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190202_155655_mailq cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190202_155655_mailq cannot be reverted.\n";

        return false;
    }
    */
}
