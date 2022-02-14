<?php

use yii\db\Migration;

/**
 * Class m190116_224511_user_collaborations
 */
class m190116_224511_user_collaborations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%user_collaborations}} ADD COLUMN collaboration_created timestamp without time zone;");
        $this->execute("
        UPDATE {{%user_collaborations}}
        SET collaboration_created=rec.colleague_invite_date
        FROM {{%user_colleagues}} as rec
        WHERE {{%user_collaborations}}.collaboration_id = rec.collaboration_id
        AND rec.colleague_permission = 'owner';
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190116_224511_user_collaborations cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190116_224511_user_collaborations cannot be reverted.\n";

        return false;
    }
    */
}
