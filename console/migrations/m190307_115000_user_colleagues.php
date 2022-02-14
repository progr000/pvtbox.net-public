<?php

use yii\db\Migration;

/**
 * Class m190307_115000_user_colleagues
 */
class m190307_115000_user_colleagues extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            CREATE UNIQUE INDEX user_colleagues_colleague_email_user_id_collaboration_id
            ON {{%user_colleagues}} USING btree
            (colleague_email COLLATE pg_catalog.\"default\", user_id, collaboration_id)
            TABLESPACE pg_default;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190307_115000_user_colleagues cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190307_115000_user_colleagues cannot be reverted.\n";

        return false;
    }
    */
}
