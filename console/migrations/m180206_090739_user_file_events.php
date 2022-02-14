<?php

use yii\db\Migration;

/**
 * Class m180206_090739_user_file_events
 */
class m180206_090739_user_file_events extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%user_file_events}}
                        ADD COLUMN parent_before_event bigint DEFAULT '0'::bigint NOT NULL;");
        $this->execute("ALTER TABLE {{%user_file_events}}
                        ADD COLUMN parent_after_event bigint DEFAULT '0'::bigint NOT NULL;");
        /*
        $this->execute("
            UPDATE {{%user_file_events}} SET
            parent_before_event = f.file_parent_id,
            parent_after_event = f.file_parent_id
            FROM {{%user_files}} as f
            WHERE {{%user_file_events}}.file_id=f.file_id;
        ");
        */
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180206_090739_user_file_events cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180206_090739_user_file_events cannot be reverted.\n";

        return false;
    }
    */
}
