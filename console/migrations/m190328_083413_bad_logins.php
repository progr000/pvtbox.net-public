<?php

use yii\db\Migration;

/**
 * Class m190328_083413_bad_logins
 */
class m190328_083413_bad_logins extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("DELETE FROM {{%bad_logins}}");
        $this->execute("ALTER TABLE {{%bad_logins}} ADD COLUMN bl_type character varying(32) COLLATE pg_catalog.\"default\" NOT NULL;");
        $this->execute("DROP INDEX idx_bad_logins_bl_ip;");
        $this->execute("CREATE UNIQUE INDEX idx_bad_logins_bl_ip
                        ON {{%bad_logins}} USING btree
                        (bl_ip COLLATE pg_catalog.\"default\", bl_type COLLATE pg_catalog.\"default\")
                        TABLESPACE pg_default;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190328_083413_bad_logins cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190328_083413_bad_logins cannot be reverted.\n";

        return false;
    }
    */
}
