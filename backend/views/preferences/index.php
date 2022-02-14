<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\tabs\TabsX;
use common\models\Preferences;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\PreferencesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $preferences array */

$this->title = 'Settings Management';
//$this->params['breadcrumbs'][] = $this->title;

?>
<div class="setting-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'formConfig' => ['labelSpan' => 5]
    ]); ?>

        <?php
        $tab = intval(Yii::$app->request->get('tab', 1));
        $aditional_info[Preferences::CATEGORY_RECAPTCHA] = '
            <div class="well">
                <p>
                    We draw your attention! <br /> The site uses Google\'s recaptcha. <br />
                    To obtain the Site key and Secret key, you need to register with the <a target="_blank" href="http://www.google.com/recaptcha/intro/index.html"> link </a> <br /> and the parameters obtained make a higher
                </p>
                <p>Learn more about captcha itself at the <a target="_blank" href="https://developers.google.com/recaptcha"> official page </a>.</p>
            </div>';

        $items = [];
        if ($preferences) {
            foreach ($preferences as $pref_category => $data) {
                $items[] = [
                    'label'   => Preferences::categoryLabel($pref_category),
                    'content' => $this->render('_tab_pref', [
                        'preferences' => $data,
                        'form'=>$form,
                        'aditional_info' => isset($aditional_info[$pref_category]) ? $aditional_info[$pref_category] : '',
                    ]),
                    'active'  => ($tab == $pref_category),
                ];
            }
        }
        ?>

        <?= TabsX::widget([
            'items'=>$items,
            'position'=>TabsX::POS_ABOVE,
            'encodeLabels'=>false
        ]) ?>

        <div class="form-group" style="margin-left: 26%; margin-top: 10px;">
            <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
        </div>

    <?php ActiveForm::end(); ?>

</div>
