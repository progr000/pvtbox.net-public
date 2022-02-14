<?php

use yii\db\Migration;

/**
 * Class m180131_081319_notifications
 */
class m180131_081319_notifications extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%notifications}} RENAME COLUMN notif_text TO notif_data");
        $this->execute("ALTER TABLE {{%notifications}} ADD COLUMN notif_type  character varying(100) DEFAULT ''::character varying NOT NULL;");
        $this->execute("ALTER TABLE {{%notifications}} ALTER COLUMN notif_data SET DATA TYPE text COLLATE pg_catalog.\"default\"");
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180131_081319_notifications cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180131_081319_notifications cannot be reverted.\n";

        return false;
    }
    */
}
