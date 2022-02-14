<?php

use yii\db\Migration;

/**
 * Class m190624_145933_software_software_type
 */
class m190624_145933_software_software_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function Up()
    {
        $this->execute("ALTER TYPE {{%software_software_type}} ADD VALUE 'linux64';");
        $this->execute("ALTER TYPE {{%software_software_type}} ADD VALUE 'linux32';");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190624_145933_software_software_type cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190624_145933_software_software_type cannot be reverted.\n";

        return false;
    }
    */
}
