<?php

use yii\db\Migration;

/**
 * Class m190717_082631_users
 */
class m190717_082631_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%users}} RENAME COLUMN limit_nodes TO upl_limit_nodes;");
        $this->execute("ALTER TABLE {{%users}} ADD COLUMN upl_shares_count_in24 SMALLINT DEFAULT NULL;");
        $this->execute("ALTER TABLE {{%users}} ADD COLUMN upl_max_shares_size BIGINT DEFAULT NULL;");
        $this->execute("ALTER TABLE {{%users}} ADD COLUMN upl_max_count_children_on_copy INTEGER DEFAULT NULL;");
        $this->execute("ALTER TABLE {{%users}} ADD COLUMN upl_block_server_nodes_above_bought SMALLINT DEFAULT NULL;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190717_082631_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190717_082631_users cannot be reverted.\n";

        return false;
    }
    */
}
