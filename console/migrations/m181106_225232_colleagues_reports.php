<?php

use yii\db\Migration;

/**
 * Class m181106_225232_colleagues_reports
 */
class m181106_225232_colleagues_reports extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%colleagues_reports}} ADD COLUMN event_id bigint DEFAULT NULL;");
        $this->execute("CREATE INDEX colleagues_reports_event_id_idx
                        ON {{%colleagues_reports}} USING btree
                        (event_id)
                        TABLESPACE pg_default;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181106_225232_colleagues_reports cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181106_225232_colleagues_reports cannot be reverted.\n";

        return false;
    }
    */
}
