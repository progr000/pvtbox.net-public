<?php

use yii\db\Migration;

/**
 * Class m181118_204252_user_files
 */
class m181118_204252_user_files extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    //public function up()
    {
        //$this->execute("SELECT __repair_to_fill_user_file_events_for_last_and_first_event_id();");

        $this->execute("ALTER TABLE {{%user_files}} ALTER COLUMN first_event_id SET NOT NULL;");
        $this->execute("ALTER table {{%user_files}} ALTER COLUMN last_event_id SET NOT NULL;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181118_204252_user_files cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181118_204252_user_files cannot be reverted.\n";

        return false;
    }
    */
}
