<?php

use yii\db\Migration;

/**
 * Class m190128_124051_mailq
 */
class m190128_124051_mailq extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%mailq}} ADD COLUMN mailer_description citext COLLATE pg_catalog.\"default\";");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190128_124051_mailq cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190128_124051_mailq cannot be reverted.\n";

        return false;
    }
    */
}
