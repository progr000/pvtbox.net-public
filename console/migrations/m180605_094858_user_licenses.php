<?php

use yii\db\Migration;

/**
 * Class m180605_094858_user_licenses
 */
class m180605_094858_user_licenses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            ALTER TABLE {{%user_licenses}}
            ADD COLUMN lic_colleague_email character varying(50) COLLATE pg_catalog.\"default\" DEFAULT NULL;
        ");
        $this->execute("
            CREATE UNIQUE INDEX idx_owner_id_colleague_email
            ON {{%user_licenses}} USING btree
            (lic_owner_user_id, lic_colleague_email)
            TABLESPACE pg_default;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180605_094858_user_licenses cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180605_094858_user_licenses cannot be reverted.\n";

        return false;
    }
    */
}
