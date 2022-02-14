<?php

use yii\db\Migration;

/**
 * Class m190130_121555_users
 */
class m190130_121555_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%users}} ADD COLUMN user_dop_status smallint NOT NULL DEFAULT '0'::smallint;");
        $this->execute("ALTER TABLE {{%users}} ADD COLUMN user_dop_log citext COLLATE pg_catalog.\"default\";");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190130_121555_users cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190130_121555_users cannot be reverted.\n";

        return false;
    }
    */
}
