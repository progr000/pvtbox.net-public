<?php

use yii\db\Migration;

/**
 * Class m180823_084608_user_files
 */
class m180823_084608_user_files extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%user_files}} ADD COLUMN last_event_uuid character varying(32) COLLATE pg_catalog.\"default\" DEFAULT NULL;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180823_084608_user_files cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180823_084608_user_files cannot be reverted.\n";

        return false;
    }
    */
}
