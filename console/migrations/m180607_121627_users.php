<?php

use yii\db\Migration;

/**
 * Class m180607_121627_users
 */
class m180607_121627_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%users}}
                        ADD COLUMN first_event_uuid_after_cron character varying(32) COLLATE pg_catalog.\"default\" DEFAULT NULL;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180607_121627_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180607_121627_users cannot be reverted.\n";

        return false;
    }
    */
}
