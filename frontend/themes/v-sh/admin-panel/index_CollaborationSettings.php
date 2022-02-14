<?php
/* @var $dataProviderColleagues \yii\data\ActiveDataProvider */
/* @var $ColleagueAddForm \frontend\models\forms\ShareElementForm */
/* @var $admin \common\models\Users */
/* @var $license_count_info array */

use yii\bootstrap\ActiveForm;
use yii\widgets\ListView;
use yii\widgets\Pjax;

?>
<!-- begin form-colleague-add -->
<?php $form = ActiveForm::begin([
    'id'      => 'form-colleague-add',
    'action'  => ['colleague-add'],
    'options' => [
        'onsubmit' => "return checkNodesOnline(false)",
        'class'    => "colleague-frm",
    ],
]);
?>
    <div class="form-title"><?= Yii::t('user/admin-panel', 'Invite_colleague') ?></div>
    <div class="form-row">
        <?= $form->field($ColleagueAddForm, 'colleague_email')
            ->textInput([
                'type'         => "email",
                'placeholder'  => $ColleagueAddForm->getAttributeLabel('colleague_email'),
                'autocomplete' => "off",
                'aria-label'   => $ColleagueAddForm->getAttributeLabel('colleague_email'),
                //aria-required="true" aria-invalid="true"
            ])
            ->label(false)
        ?>
        <button class="btn primary-btn md-btn"
               type="submit"
               name="ColleagueAdd"
               value="<?= Yii::t('user/admin-panel', 'Invite') ?>"><?= Yii::t('user/admin-panel', 'Invite') ?></button>
        <div class="img-progress" data-add-class="img-progress-inline" title="loading..."></div>
    </div>
    <div class="inform">
        <p><?= Yii::t('user/admin-panel', 'To_add_colleague_enter') ?></p>
    </div>
<?php ActiveForm::end(); ?>
<!-- end form-colleague-add -->
<div class="form-title form-title--row"><span><?= Yii::t('user/admin-panel', 'Colleagues_list') ?></span>
    <div class="licences-control"><span><?= Yii::t('user/admin-panel', 'Count_of_Total_lic_used', [
                'count' => $license_count_info['used'],
                'total' => $license_count_info['total'],
            ]) ?></span><a class="void-0 masterTooltip" href="#" title="<?= Yii::t("user/billing", "Contact_support") ?>"><?= Yii::t('user/admin-panel', 'Add_more') ?></a></div>
</div>
<div class="table-wrap">
    <div class="table-wrap__inner">

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
            'layout' => '
                <table class="colleague-tbl">
                    <thead>
                        <tr>
                            <th></th>
                            <th>' . Yii::t('user/admin-panel', 'CollSet_User') . '</th>
                            <th>' . Yii::t('user/admin-panel', 'CollSet_Status') . '</th>
                            <th>' . Yii::t('user/admin-panel', 'CollSet_Permission') . '</th>
                            <th>' . Yii::t('user/admin-panel', 'CollSet_Action') . '</th>
                        </tr>
                    </thead>
                    <tbody>
                        {items}
                    </tbody>
                </table>
                {pager}',
            'emptyText' => $this->render('index_CollaborationSettings_ListItemNoData', ['admin' => $admin]),
            'emptyTextOptions' => ['tag' => false],
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
</div>
