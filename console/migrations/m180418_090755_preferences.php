<?php

use yii\db\Migration;
use common\models\Preferences;

/**
 * Class m180418_090755_preferences
 */
class m180418_090755_preferences extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $pref_init = [
            'adminEmail'                    => ['category' => Preferences::CATEGORY_BASE,      'title' => 'E-Mail администратора', 'value' => ''],
            'user.passwordResetTokenExpire' => ['category' => Preferences::CATEGORY_BASE,      'title' => 'Время жизни токена для пароля (в секундах)', 'value' => 86400],
            'RestorePatchTTL'               => ['category' => Preferences::CATEGORY_BASE,      'title' => 'Время хранения патчей для откатов изменений (в секундах)', 'value' => '2592000'],

            'paypalSellerEmail'             => ['category' => Preferences::CATEGORY_OTHER,     'title' => 'E-Mail PayPal - аккаунта (seller)', 'value' => ''],

            'reCaptchaPublicKey'            => ['category' => Preferences::CATEGORY_RECAPTCHA, 'title' => 'Публичный ключ ReCaptcha', 'value' => ''],
            'reCaptchaSecretKey'            => ['category' => Preferences::CATEGORY_RECAPTCHA, 'title' => 'Секретный ключ ReCaptcha', 'value' => ''],
            'reCaptchaGoogleAcc'            => ['category' => Preferences::CATEGORY_RECAPTCHA, 'title' => 'Информация о акаунте на Google (например логин и пароль)', 'value' => ''],
            'RegisterCountNoCaptcha'        => ['category' => Preferences::CATEGORY_RECAPTCHA, 'title' => 'Количество разрешенных регистраций с одгого ИП до появления Рекапчи', 'value' => '2'],
            'LoginCountNoCaptcha'           => ['category' => Preferences::CATEGORY_RECAPTCHA, 'title' => 'Количество разрешенных неверных логинов с одного ИП до появления Рекапчи', 'value' => '3'],
            'ResetPasswordCountNoCaptcha'   => ['category' => Preferences::CATEGORY_RECAPTCHA, 'title' => 'Количество восстановлений пароля с одного ИП до появления Рекапчи', 'value' => '2'],
            'ContactCountNoCaptcha'         => ['category' => Preferences::CATEGORY_RECAPTCHA, 'title' => 'Количество запросов в саппорт одного ИП до появления Рекапчи', 'value' => '2'],

            'SignalAccessKey'               => ['category' => Preferences::CATEGORY_NODEAPI,   'title' => 'Ключ доступа к АПИ для сигнального сервера', 'value' => ''],

            'PricePerMonthForLicenseProfessional' => ['category' => Preferences::CATEGORY_PRICES,    'title' => 'Цена лицензии Professional за месяц', 'value' => '3.99'],
            'PricePerMonthUserForLicenseBusiness' => ['category' => Preferences::CATEGORY_PRICES,    'title' => 'Цена лицензии Business за пользователь/месяц', 'value' => '4.99'],
        ];

        foreach ($pref_init as $k=>$v) {
            $pref = new Preferences();
            $pref->pref_key = $k;
            $pref->pref_category = $v['category'];
            $pref->pref_title = mb_substr($v['title'], 0, 255);
            $pref->pref_value = $v['value'];
            if ($pref->save()) {
                echo "\nCreated new record in {{%preferences}} with key <{$k}> ; \n\n";
            } else {
                echo "\n\n";
                var_dump($pref->getErrors());
                echo "\n\n";
                //return false;
            }
            unset($pref);
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180418_090755_preferences cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180418_090755_preferences cannot be reverted.\n";

        return false;
    }
    */
}
