<?php

use yii\db\Migration;
use common\models\Users;
use common\models\SelfHostUsers;

/**
 * Class m200108_120032_self_host_users
 */
class m200108_120032_self_host_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE {{%self_host_users}} ADD COLUMN shu_user_hash CHARACTER VARYING(128) NOT NULL DEFAULT ''::character varying;");
        $SelfHostUsers = SelfHostUsers::find()->all();
        foreach ($SelfHostUsers as $SelfHostUser) {
            /** @var $SelfHostUser common\models\SelfHostUsers */
            $SelfHostUser->shu_user_hash = Users::generateShuUserHash($SelfHostUser->shu_email);
            $SelfHostUser->save();
        }
        $this->execute("CREATE UNIQUE INDEX idx_shu_user_hash ON {{%self_host_users}} USING BTREE (shu_user_hash);");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200108_120032_self_host_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200108_120032_self_host_users cannot be reverted.\n";

        return false;
    }
    */
}
