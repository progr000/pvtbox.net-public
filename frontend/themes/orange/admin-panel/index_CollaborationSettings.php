<?php
/* @var $dataProviderColleagues \yii\data\ActiveDataProvider */
/* @var $ColleagueAddForm \frontend\models\forms\ShareElementForm */
/* @var $admin \common\models\Users */
/* @var $license_count_info array */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\widgets\ListView;
use yii\widgets\Pjax;
use common\models\Licenses;

?>

<!-- .inputForm -->
<?php $form = ActiveForm::begin([
    'id'      => 'form-colleague-add',
    'action'  => ['colleague-add'],
    'options' => ['onsubmit' => "return checkNodesOnline(false)"]
]);
?>

<div class="inputForm admin-panel-inputForm">

    <div class="inputForm__title"><span><?= Yii::t('user/admin-panel', 'Invite_colleague') ?></span></div>

    <div class="inputForm__cont">

        <div class="inputForm__box admin-panel-inputForm__box">

            <div class="form-group">
                <?= $form->field($ColleagueAddForm, 'colleague_email')
                    ->textInput([
                        'type'         => "email",
                        'placeholder'  => $ColleagueAddForm->getAttributeLabel('colleague_email'),
                        'autocomplete' => "off",
                        //'value' => Yii::$app->user->identity->user_email
                    ])
                    ->label(false)
                ?>

            </div>

        </div>

        <div class="inputForm__box admin-panel-inputForm__box">

            <?= Html::submitButton(Yii::t('user/admin-panel', 'Invite'), ['class' => 'btn-big', 'name' => 'ColleagueAdd']) ?>

        </div>

        <div class="inputForm__box admin-panel-inputForm__box">

            <div class="inform">
                <p><?= Yii::t('user/admin-panel', 'To_add_colleague_enter') ?></p>
            </div>

        </div>

    </div>

    <div class="inputForm__title inputForm__title--indenting admin-panel-inputForm__title--indenting"><span><?= Yii::t('user/admin-panel', 'Colleagues_list') ?></span></div>

</div>

<?php ActiveForm::end(); ?>
<!-- END .inputForm -->

<div class="table-text-top">
    <span><?= Yii::t('user/admin-panel', 'Count_of_Total_lic_used', [
            'count' => $license_count_info['used'],
            'total' => $license_count_info['total'],
        ]) ?> <?= ""/*Html::a(Yii::t('user/admin-panel', 'Add_more'), ['purchase/add-licenses', 'billed' => Licenses::getBilledByPeriod($admin->license_period)])*/ ?>
         <a href="#" class="masterTooltip" title="<?= Yii::t("user/billing", "Contact_support") ?>"><?= Yii::t('user/admin-panel', 'Add_more') ?></a>
    </span>
</div>

<div class="table table--collaboration">

    <div class="table__head-cont">

        <div class="table__head">
            <div class="table__head-box"></div>
            <div class="table__head-box"><span><?= Yii::t('user/admin-panel', 'CollSet_User') ?></span></div>
            <div class="table__head-box"><span><?= Yii::t('user/admin-panel', 'CollSet_Status') ?></span></div>
            <div class="table__head-box"><span><?= Yii::t('user/admin-panel', 'CollSet_Permission') ?></span></div>
            <div class="table__head-box"><span><?= Yii::t('user/admin-panel', 'CollSet_Action') ?></span></div>
        </div>

    </div>


    <?php Pjax::begin(); ?>
    <?php
    //$minCount = 6;
    $currentPage = intval(Yii::$app->request->get($dataProviderColleagues->pagination->pageParam, 1));
    $count = $dataProviderColleagues->count;
    $lost = $dataProviderColleagues->pagination->pageSize - $count;
    //$lost = $minCount - $count;
    $user_created_ts = strtotime($admin->user_created) + Yii::$app->session->get('UserTimeZoneOffset', 0);
    ?>
    <?=
    ListView::widget([
        'dataProvider' => $dataProviderColleagues,
        'itemOptions' => [
            'tag' => false,
            'class' => '',
        ],
        'layout' => '<div class="scrollbar-box"><div class="table__body-cont">' . "{items}" . '</div></div>' . "\n{pager}",
        'emptyText' => $this->render('index_CollaborationSettings_ListItemNoData', ['admin' => $admin]),
        'emptyTextOptions' => ['tag' => false],
        //'layout' => "{pager}\n{summary}\n{items}\n{pager}",
        //'summary' => 'Показано {count} из {totalCount}',
        'itemView' => function ($model, $key, $index, $widget) use ($lost, $count, $admin) {
            $lost_row = '';
            if ($lost>0 && ($index == $count - 1)) {
                for ($i=1; $i<=$lost; $i++) {
                    $lost_row .= $this->render('index_CollaborationSettings_ListItemEmpty');
                }
            }
            /* @var $model \frontend\models\search\ColleaguesSearch */
            return $this->render('index_CollaborationSettings_ListItem', ['model' => $model, 'admin' => $admin]) . $lost_row;
        },
    ]);
    ?>
    <?php Pjax::end(); ?>

</div>
