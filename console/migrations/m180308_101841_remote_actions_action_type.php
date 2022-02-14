<?php

use yii\db\Migration;

/**
 * Class m180308_101841_remote_actions_action_type
 */
class m180308_101841_remote_actions_action_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function Up()
    {
        $this->execute("ALTER TYPE {{%remote_actions_action_type}} ADD VALUE 'credentials' AFTER 'wipe';");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180308_101841_remote_actions_action_type cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180308_101841_remote_actions_action_type cannot be reverted.\n";

        return false;
    }
    */
}
