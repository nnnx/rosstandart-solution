<?php

namespace app\models;

use Yii;
use yii\base\BaseObject;
use yii\base\Model;
use yii\helpers\FileHelper;

class PdfAnnots extends BaseObject
{
    const RESULT_FILE = '@app/python/results/pdf_items.json';

    public function process()
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://python:5000/scan-pdf');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
            curl_exec($ch);
            curl_close($ch);

            Yii::$app->db->createCommand()->truncateTable(PdfItem::tableName())->execute();




            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}