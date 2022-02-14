<?php

use yii\db\Migration;

/**
 * Class m180124_173952_user_uploads
 */
class m180124_173952_user_uploads extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%user_uploads}} ALTER COLUMN upload_path SET DATA TYPE text COLLATE pg_catalog.\"default\"");
        $this->execute("ALTER TABLE {{%user_uploads}} RENAME COLUMN upload_name TO upload_saved_name");
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180124_173952_user_uploads cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180124_173952_user_uploads cannot be reverted.\n";

        return false;
    }
    */
}
