<?php

/**
 * @var $model \app\models\PdfAnnots
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use richardfan\widget\JSRegister;

$this->title = 'Импорт аннотаций из pdf';
$countFiles = \app\models\ImportPdf::getCountFiles();
?>

<h1><?= $this->title ?></h1>

<?php $form = ActiveForm::begin([
    'options' => [
        'enctype' => 'multipart/form-data',
    ],
]); ?>

<p>
    Количество загруженных pdf файлов: <strong><?= $countFiles ?></strong>
</p>
<?php if (!$countFiles) { ?>
    <div class="alert alert-danger">
        Сначала нужно загрузить pdf документы
    </div>
<?php } ?>

<?= Html::submitButton('Обработать', ['class' => 'btn btn-primary']) ?>

<div class="loader d-none"></div>

<?php ActiveForm::end(); ?>

<?php JSRegister::begin() ?>
<script>
    $('form').on('beforeSubmit', function () {
        var $form = $(this);
        $('button[type="submit"]', $form).addClass('d-none');
        $('.loader', $form).removeClass('d-none');
    });
</script>
<?php JSRegister::end() ?>

