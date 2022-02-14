<?php

use yii\db\Migration;

/**
 * Class m190309_011846_user_file_events
 */
class m190309_011846_user_file_events extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%user_file_events}} ALTER event_uuid SET NOT NULL;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190309_011846_user_file_events cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190309_011846_user_file_events cannot be reverted.\n";

        return false;
    }
    */
}
