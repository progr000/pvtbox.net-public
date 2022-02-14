<?php

use yii\db\Migration;

/**
 * Class m180427_122246_software
 */
class m180427_122246_software extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%software}} ADD software_sort smallint NOT NULL DEFAULT 0;");
        $this->execute("DROP INDEX idx_17419_software_version");
        $this->execute("CREATE UNIQUE INDEX idx_software_url
                        ON {{%software}} USING btree
                        (software_file_name COLLATE pg_catalog.\"default\", software_url COLLATE pg_catalog.\"default\")
                        TABLESPACE pg_default;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180427_122246_software cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180427_122246_software cannot be reverted.\n";

        return false;
    }
    */
}
