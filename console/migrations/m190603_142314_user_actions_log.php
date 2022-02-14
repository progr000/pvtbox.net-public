<?php

use yii\db\Migration;

/**
 * Class m190603_142314_user_actions_log
 */
class m190603_142314_user_actions_log extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            CREATE INDEX idx_user_actions_log_user_id
                ON {{%user_actions_log}} USING btree
                (user_id)
                TABLESPACE pg_default;
        ");

        $this->execute("
            CREATE INDEX idx_user_alerts_log_user_id
                ON {{%user_alerts_log}} USING btree
                (user_id)
                TABLESPACE pg_default;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190603_142314_user_actions_log cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190603_142314_user_actions_log cannot be reverted.\n";

        return false;
    }
    */
}
