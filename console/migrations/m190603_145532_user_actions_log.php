<?php

use yii\db\Migration;

/**
 * Class m190603_145532_user_actions_log
 */
class m190603_145532_user_actions_log extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("DELETE FROM {{%user_actions_log}};");
        $this->execute("ALTER TABLE {{%user_actions_log}} ADD COLUMN site_url character varying(255) COLLATE pg_catalog.\"default\" NOT NULL");
        $this->execute("ALTER TABLE {{%user_actions_log}} ADD COLUMN site_absolute_url character varying(255) COLLATE pg_catalog.\"default\" NOT NULL");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190603_145532_user_actions_log cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190603_145532_user_actions_log cannot be reverted.\n";

        return false;
    }
    */
}
