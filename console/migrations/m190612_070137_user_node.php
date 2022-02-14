<?php

use yii\db\Migration;

/**
 * Class m190612_070137_user_node
 */
class m190612_070137_user_node extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%user_node}} ADD COLUMN node_prev_status SMALLINT DEFAULT NULL;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190612_070137_user_node cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190612_070137_user_node cannot be reverted.\n";

        return false;
    }
    */
}
