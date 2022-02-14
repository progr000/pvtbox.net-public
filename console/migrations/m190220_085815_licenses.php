<?php

use yii\db\Migration;

/**
 * Class m190220_085815_licenses
 */
class m190220_085815_licenses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%licenses}} ADD COLUMN license_max_count_children_on_copy INT DEFAULT '0'::INT NOT NULL;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190220_085815_licenses cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190220_085815_licenses cannot be reverted.\n";

        return false;
    }
    */
}
