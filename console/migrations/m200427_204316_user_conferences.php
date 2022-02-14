<?php

use yii\db\Migration;

/**
 * Class m200427_204316_user_conferences
 */
class m200427_204316_user_conferences extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%user_conferences}} ADD COLUMN conference_guest_hash CHARACTER VARYING(32) DEFAULT NULL;");
        $this->execute("CREATE UNIQUE INDEX conference_guest_hash_idx ON {{%user_conferences}} USING BTREE (conference_guest_hash);");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200427_204316_user_conferences cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200427_204316_user_conferences cannot be reverted.\n";

        return false;
    }
    */
}
