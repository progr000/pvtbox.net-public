<?php

use yii\db\Migration;

/**
 * Class m181205_102812_user_files
 */
class m181205_102812_user_files extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("CREATE INDEX idx_user_file_first_last_event_id
                        ON {{%user_files}} USING btree
                        (first_event_id, last_event_id)
                        TABLESPACE pg_default;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181205_102812_user_files cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181205_102812_user_files cannot be reverted.\n";

        return false;
    }
    */
}
