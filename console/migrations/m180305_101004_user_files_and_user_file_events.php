<?php

use yii\db\Migration;

/**
 * Class m180305_101004_user_files_and_user_file_events
 */
class m180305_101004_user_files_and_user_file_events extends Migration
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
            ALTER TABLE {$tablePrefix}user_files ALTER COLUMN node_id SET DEFAULT NULL;
            ALTER TABLE {$tablePrefix}user_files ALTER COLUMN node_id DROP NOT NULL;
            ALTER TABLE {$tablePrefix}user_file_events ALTER COLUMN node_id SET DEFAULT NULL;
            ALTER TABLE {$tablePrefix}user_file_events ALTER COLUMN node_id DROP NOT NULL;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180305_101004_user_files_and_user_file_events cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180305_101004_user_files_and_user_file_events cannot be reverted.\n";

        return false;
    }
    */
}
