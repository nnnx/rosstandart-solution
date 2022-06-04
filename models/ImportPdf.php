<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\helpers\FileHelper;

class ImportPdf extends Model
{
    /** @var string путь к папгке загрузки */
    const UPLOAD_PATH = '@app/data/Разметка';

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
            $dir = Yii::getAlias(self::UPLOAD_PATH);
            FileHelper::removeDirectory($dir);
            FileHelper::createDirectory($dir);
            $zip->extractTo($dir . '/..');
            $zip->close();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * кол-во загруженных файлов
     * @return void
     */
    public static function getCountFiles()
    {
        $path = Yii::getAlias(self::UPLOAD_PATH);
        if (file_exists($path)) {
            return count(FileHelper::findFiles($path, [
                'only' => ['*.pdf'],
                'recursive' => false
            ]));
        }
        return 0;
    }
}