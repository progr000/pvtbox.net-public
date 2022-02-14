<?php

use yii\db\Migration;

/**
 * Class m180221_182659_servers
 */
class m180221_182659_servers extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%servers}} ALTER COLUMN server_login TYPE varchar(50)");
        $this->execute("ALTER TABLE {{%servers}} ALTER COLUMN server_login SET DEFAULT ''::character varying");
        $this->execute("ALTER TABLE {{%servers}} ALTER COLUMN server_password TYPE varchar(50)");
        $this->execute("ALTER TABLE {{%servers}} ALTER COLUMN server_password SET DEFAULT ''::character varying");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180221_182659_servers cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180221_182659_servers cannot be reverted.\n";

        return false;
    }
    */
}
