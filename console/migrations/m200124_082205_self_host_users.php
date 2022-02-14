<?php

use yii\db\Migration;

/**
 * Class m200124_082205_self_host_users
 */
class m200124_082205_self_host_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%self_host_users}} ADD COLUMN shu_promo_code CHARACTER VARYING(30) DEFAULT NULL;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200124_082205_self_host_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200124_082205_self_host_users cannot be reverted.\n";

        return false;
    }
    */
}
