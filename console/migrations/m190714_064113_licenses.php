<?php

use yii\db\Migration;

/**
 * Class m190714_064113_licenses
 */
class m190714_064113_licenses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%licenses}} ADD COLUMN license_block_server_nodes_above_bought SMALLINT NOT NULL DEFAULT 1");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190714_064113_licenses cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190714_064113_licenses cannot be reverted.\n";

        return false;
    }
    */
}
