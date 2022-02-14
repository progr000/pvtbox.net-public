<?php
/* @var $aditional_info string */
/* @var \common\models\Preferences $pref */

$text_area_fields = ['seoAdditionalMetaTagsGuest', 'seoAdditionalMetaTagsMember', 'seoAdditionalMetaTagsAll'];

foreach ($preferences as $index => $pref) {
    unset($title);
    $title = Yii::t('app/preferences', $pref->pref_key);
    if ($title == $pref->pref_key) { $title = $pref->pref_title; }
    if (in_array($pref->pref_key, $text_area_fields)) {
        echo $form->field($pref, "[$pref->pref_key]pref_value")->textarea()->label($title);
    } else {
        echo $form->field($pref, "[$pref->pref_key]pref_value")->textInput()->label($title);
    }
}
?>

<?= $aditional_info ?>
