<?php

use yii\db\Migration;

/**
 * Class m181119_205221_users
 */
class m181119_205221_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%users}} ADD COLUMN expired_notif_sent smallint NOT NULL DEFAULT '0'::smallint;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181119_205221_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181119_205221_users cannot be reverted.\n";

        return false;
    }
    */
}
