<?php

use yii\db\Migration;

/**
 * Class m180123_221933_user_uploads
 */
class m180123_221933_user_uploads extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%user_uploads}} ADD upload_name character varying(255) COLLATE pg_catalog.\"default\" NOT NULL DEFAULT ''::character varying;");
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180123_221933_user_uploads cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180123_221933_user_uploads cannot be reverted.\n";

        return false;
    }
    */
}
