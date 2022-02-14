<?php

use yii\db\Migration;

/**
 * Class m181107_141217_users
 */
class m181107_141217_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%users}} ALTER COLUMN static_timezone TYPE integer");
        $this->execute("ALTER TABLE {{%users}} ALTER COLUMN dynamic_timezone TYPE integer");
        $this->execute("ALTER TABLE {{%users}} ALTER COLUMN static_timezone SET DEFAULT '0'::integer;");
        $this->execute("ALTER TABLE {{%users}} ALTER COLUMN dynamic_timezone SET DEFAULT '0'::integer;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181107_141217_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181107_141217_users cannot be reverted.\n";

        return false;
    }
    */
}
