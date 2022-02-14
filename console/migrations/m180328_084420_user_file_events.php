<?php

use yii\db\Migration;

/**
 * Class m180328_084420_user_file_events
 */
class m180328_084420_user_file_events extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $schema   = isset(Yii::$app->components['db']['schemaMap']['pgsql']['defaultSchema'])
            ? Yii::$app->components['db']['schemaMap']['pgsql']['defaultSchema']
            : 'public';

        $tablePrefix = isset(Yii::$app->components['db']['tablePrefix'])
            ? Yii::$app->components['db']['tablePrefix']
            : '';

        $this->db->pdo->exec("
            SET search_path TO {$schema};

            ALTER TABLE {$tablePrefix}user_file_events ADD COLUMN event_creator_user_id bigint DEFAULT '0'::bigint NOT NULL;
            ALTER TABLE {$tablePrefix}user_file_events ADD COLUMN event_creator_node_id bigint DEFAULT '0'::bigint NOT NULL;
            CREATE INDEX event_creator_idx
                ON {$tablePrefix}user_file_events USING btree
                (event_creator_user_id, event_creator_node_id)
                TABLESPACE pg_default;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180328_084420_user_file_events cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180328_084420_user_file_events cannot be reverted.\n";

        return false;
    }
    */
}
