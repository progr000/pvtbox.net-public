<?php

use yii\db\Migration;

/**
 * Class m180704_112728_user_licenses
 */
class m180704_112728_user_licenses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            ALTER TABLE {{%user_licenses}}
            ADD COLUMN lic_lastpay_timestamp bigint NOT NULL DEFAULT 0::bigint;
        ");
        $this->execute("
            ALTER TABLE {{%user_licenses}}
            ADD COLUMN lic_group_id bigint NOT NULL DEFAULT 0::bigint;
        ");
        $this->execute("
            CREATE INDEX idx_lic_lastpay_timestamp
            ON {{%user_licenses}} USING btree
            (lic_lastpay_timestamp)
            TABLESPACE pg_default;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180704_112728_user_licenses cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180704_112728_user_licenses cannot be reverted.\n";

        return false;
    }
    */
}
