<?php

use yii\db\Migration;

/**
 * Class m181019_201927_users
 */
class m181019_201927_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%users}} ADD COLUMN static_timezone smallint NOT NULL DEFAULT '0'::smallint;");
        $this->execute("ALTER TABLE {{%users}} ADD COLUMN dynamic_timezone smallint NOT NULL DEFAULT '0'::smallint;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181019_201927_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181019_201927_users cannot be reverted.\n";

        return false;
    }
    */
}
