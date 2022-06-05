<?php

namespace app\controllers;

use app\models\ResultCategoryItemSearch;
use app\models\ResultPdfItemSearch;
use Yii;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\Response;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new ResultCategoryItemSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionResultPdf()
    {
        $searchModel = new ResultPdfItemSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        return $this->render('result-pdf', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}
