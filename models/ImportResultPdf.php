<?php

namespace app\models;

use Yii;
use yii\base\BaseObject;
use yii\helpers\FileHelper;

/**
 * Загрузить данные обработки по доле импорта
 */
class ImportResultPdf extends BaseObject
{
    const FILE_PATH = '@app/python/results/pdf_data_final.csv';

    public function process()
    {
        try {
            Yii::$app->db->createCommand()->truncateTable(ResultPdfItem::tableName())->execute();
            $handle = fopen(Yii::getAlias(self::FILE_PATH), "r");
            $header = fgets($handle);
            $separator = ';';
            $keys = explode($separator, $header);
            $keys = array_map(function ($value) {
                return trim(str_replace('"', '', $value));
            }, $keys);
            $data = [];
            while (($row = fgetcsv($handle, 0, $separator)) !== false) {
                $item = $row;
                $data[] = $item;
                if (count($data) == 50) {
                    $this->insert($keys, $data);
                    $data = [];
                }
            }
            fclose($handle);
            if (count($data)) {
                $this->insert($keys, $data);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function insert($columns, $data)
    {
        Yii::$app->db->createCommand()
            ->batchInsert(ResultPdfItem::tableName(), $columns, $data)
            ->execute();
    }
}