<?php

use yii\db\Migration;

/**
 * Class m190312_163028_user_licenses
 */
class m190312_163028_user_licenses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%user_licenses}} ALTER COLUMN lic_colleague_email TYPE CITEXT;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190312_163028_user_licenses cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190312_163028_user_licenses cannot be reverted.\n";

        return false;
    }
    */
}
