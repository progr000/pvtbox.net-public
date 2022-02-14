<?php

use yii\db\Migration;

/**
 * Class m191104_121512_software
 */
class m191104_121512_software extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%software}} ALTER COLUMN software_type TYPE CHARACTER VARYING(50)");
        $this->execute("ALTER TABLE {{%software}} ALTER COLUMN software_type SET DEFAULT 'windows';");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191104_121512_software cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191104_121512_software cannot be reverted.\n";

        return false;
    }
    */
}
