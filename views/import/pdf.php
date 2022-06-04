<?php

/**
 * @var $model \app\models\ImportPdf
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use richardfan\widget\JSRegister;

$this->title = 'Импорт pdf';
?>

<h1><?= $this->title ?></h1>

<?php $form = ActiveForm::begin([
    'options' => [
        'enctype' => 'multipart/form-data',
    ],
]); ?>

<p>
    Файл должен быть в zip архиве и содержать папку "Разметка" с pdf файлами
</p>

<?= $form->field($model, 'file')->fileInput()->label(false) ?>

<?= Html::submitButton('Загрузить', ['class' => 'btn btn-primary']) ?>

<div class="loader d-none"></div>

<?php ActiveForm::end(); ?>

<?php JSRegister::begin() ?>
<script>
    $('form').on('beforeSubmit', function() {
        var $form = $(this);
        $('button[type="submit"]', $form).addClass('d-none');
        $('.loader', $form).removeClass('d-none');
    });
</script>
<?php JSRegister::end() ?>

