<?php

use yii\db\Migration;

/**
 * Class m190701_082117_users
 */
class m190701_082117_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%users}} ADD COLUMN enable_admin_panel SMALLINT NOT NULL DEFAULT 1::SMALLINT;");
        $this->execute("ALTER TABLE {{%users}} ADD COLUMN limit_nodes SMALLINT DEFAULT NULL;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190701_082117_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190701_082117_users cannot be reverted.\n";

        return false;
    }
    */
}
