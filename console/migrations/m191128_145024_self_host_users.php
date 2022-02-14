<?php

use yii\db\Migration;

/**
 * Class m191128_145024_self_host_users
 */
class m191128_145024_self_host_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%self_host_users}} ADD COLUMN pay_type CHARACTER VARYING(20) NOT NULL DEFAULT 'not_set'::character varying;");
        $this->execute("ALTER TABLE {{%self_host_users}} ADD COLUMN license_period SMALLINT NOT NULL DEFAULT '0'::smallint;");
        $this->execute("ALTER TABLE {{%self_host_users}} ADD COLUMN license_expire TIMESTAMP WITHOUT TIME ZONE;");
        $this->execute("ALTER TABLE {{%self_host_users}} ADD COLUMN shu_support_requested SMALLINT NOT NULL DEFAULT '0'::smallint;");
        $this->execute("ALTER TABLE {{%self_host_users}} ADD COLUMN shu_brand_requested SMALLINT NOT NULL DEFAULT '0'::smallint;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191128_145024_self_host_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191128_145024_self_host_users cannot be reverted.\n";

        return false;
    }
    */
}
