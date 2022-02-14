<?php

use yii\db\Migration;

/**
 * Class m180327_104659_user_file_events
 */
class m180327_104659_user_file_events extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tablePrefix = isset(Yii::$app->components['db']['tablePrefix'])
            ? Yii::$app->components['db']['tablePrefix']
            : '';

        $this->db->pdo->exec("
            ALTER TABLE {$tablePrefix}user_file_events ADD COLUMN prev_event_timestamp bigint DEFAULT '0'::bigint NOT NULL;
            ALTER TABLE {$tablePrefix}user_file_events ADD COLUMN prev_event_type smallint DEFAULT '0'::smallint NOT NULL;
            ALTER TABLE {$tablePrefix}user_file_events ADD COLUMN is_rollback smallint DEFAULT '0'::smallint NOT NULL;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180327_104659_user_file_events cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180327_104659_user_file_events cannot be reverted.\n";

        return false;
    }
    */
}
