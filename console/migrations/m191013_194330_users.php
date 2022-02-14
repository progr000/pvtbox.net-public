<?php

use yii\db\Migration;

/**
 * Class m191013_194330_users
 */
class m191013_194330_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%users}} ALTER COLUMN user_ref_id DROP DEFAULT;");
        $this->execute("ALTER TABLE {{%users}} ALTER COLUMN user_ref_id DROP NOT NULL;");
        //$this->execute("ALTER TABLE {{%users}} ALTER COLUMN user_ref_id DEFAULT NULL;");
        $this->execute("UPDATE {{%users}} SET user_ref_id=NULL;");
        $this->execute("
            ALTER TABLE {{%users}}
            ADD CONSTRAINT fk_users_user_ref_id FOREIGN KEY (user_ref_id)
            REFERENCES {{%admins}} (admin_id) MATCH SIMPLE
            ON UPDATE CASCADE
            ON DELETE SET NULL;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191013_194330_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191013_194330_users cannot be reverted.\n";

        return false;
    }
    */
}
