<?php

namespace app\controllers;

use app\models\ImportPdf;
use app\models\ImportResultCategory;
use app\models\ImportResultPdf;
use app\models\PdfAnnots;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;

class ImportController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionPdf()
    {
        $model = new ImportPdf();
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $model->file = UploadedFile::getInstance($model, 'file');
            $errors = ActiveForm::validate($model);
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return $errors;
            }
            if ($model->process()) {
                Yii::$app->session->setFlash('success', 'Успешно сохранено');
            } else {
                Yii::$app->session->setFlash('error', 'Произошла ошибка');
            }
        }
        return $this->render('pdf', [
            'model' => $model,
        ]);
    }

    public function actionResultCategory()
    {
        $model = new ImportResultCategory();
        $model->process();
    }

    public function actionPdfAnnots()
    {
        $model = new PdfAnnots();
        if (Yii::$app->request->isPost) {
            if ($model->process()) {
                Yii::$app->session->setFlash('success', 'Успешно сохранено');
            } else {
                Yii::$app->session->setFlash('error', 'Произошла ошибка');
            }
        }
        return $this->render('pdf_annots', [
            'model' => $model,
        ]);
    }

    public function actionResultPdf()
    {
        $model = new ImportResultPdf();
        $model->process();
    }
}