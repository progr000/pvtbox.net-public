<?php

use yii\db\Migration;

/**
 * Class m180409_082943_drop_not_used_tables
 */
class m180409_082943_drop_not_used_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("DROP TABLE IF EXISTS {{%node_changes}}");
        $this->execute("DROP TABLE IF EXISTS {{%pears}}");
        $this->execute("DROP SEQUENCE IF EXISTS {{%node_changes_ncg_id_seq}}");
        $this->execute("DROP SEQUENCE IF EXISTS {{%pears_pear_id_seq}}");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180409_082943_drop_not_used_tables cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180409_082943_drop_not_used_tables cannot be reverted.\n";

        return false;
    }
    */
}
