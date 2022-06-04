<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\helpers\FileHelper;

class ImportPdf extends Model
{
    /** @var \yii\web\UploadedFile|null */
    public $file;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['file'], 'file', 'skipOnEmpty' => false, 'extensions' => ['zip']],
        ];
    }

    /**
     * @return bool
     */
    public function process()
    {
        try {
            $zip = new \ZipArchive();
            $zip->open($this->file->tempName);
            $dir = Yii::getAlias('@app/data/');
            FileHelper::removeDirectory($dir . 'Разметка');
            FileHelper::createDirectory($dir);
            $zip->extractTo($dir);
            $zip->close();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}