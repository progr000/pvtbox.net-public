<?php

use yii\db\Migration;

/**
 * Class m190208_151355_user_alerts_log
 */
class m190208_151355_user_alerts_log extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%user_alerts_log}} ADD COLUMN alert_action character varying(100) COLLATE pg_catalog.\"default\" DEFAULT NULL;");

        $this->execute("CREATE INDEX idx_user_alerts_log_alert_action
                ON {{%user_alerts_log}} USING btree
                (alert_action COLLATE pg_catalog.\"default\")
                TABLESPACE pg_default;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190208_151355_user_alerts_log cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190208_151355_user_alerts_log cannot be reverted.\n";

        return false;
    }
    */
}
