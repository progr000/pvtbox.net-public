<?php

use yii\db\Migration;

/**
 * Class m190308_094824_user_licenses
 */
class m190308_094824_user_licenses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            ALTER TABLE {{%user_licenses}}
            ADD CONSTRAINT fk_lic_colleague_user_id FOREIGN KEY (lic_colleague_user_id)
            REFERENCES {{%users}} (user_id) MATCH SIMPLE
            ON UPDATE CASCADE
            ON DELETE SET NULL;
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190308_094824_user_licenses cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190308_094824_user_licenses cannot be reverted.\n";

        return false;
    }
    */
}
