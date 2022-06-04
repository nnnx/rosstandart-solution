<?php

/**
 * @var $model \app\models\ResultCategoryItemSearch
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;

?>

<?php $form = ActiveForm::begin([
    'method' => 'get',
    'action' => '/' . Yii::$app->request->pathInfo,
]) ?>

<div class="row">
    <div class="col-md-6">
        <?= $form->field($model, 'rubr0_selected')->widget(Select2::class, [
            'data' => $model->getRubr0Select(),
            'theme' => Select2::THEME_BOOTSTRAP,
            'showToggleAll' => false,
            'options' => [
                'placeholder' => 'Все',
                'multiple' => true,
            ],
        ]); ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'rubr1_selected')->widget(Select2::class, [
            'data' => $model->getRubr1Select(),
            'theme' => Select2::THEME_BOOTSTRAP,
            'showToggleAll' => false,
            'options' => [
                'placeholder' => 'Все',
                'multiple' => true,
            ],
        ]); ?>
    </div>
</div>

<?= Html::submitButton('Применить', ['class' => 'btn btn-primary']) ?>
<?= Html::a('Сбросить', $form->action, ['class' => 'btn btn-light ml-2']) ?>

<?php ActiveForm::end(); ?>

<br/>