<?php

namespace app\models;

use TonchikTm\PdfToHtml\Pdf;
use Yii;
use yii\base\BaseObject;
use yii\base\Model;
use yii\helpers\FileHelper;

/**
 * Обработка полученных аннотаций из pdf
 */
class PdfAnnots extends BaseObject
{
    const RESULT_FILE = '@app/python/results/pdf_items.json';

    public function process()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://python:5000/scan-pdf');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_exec($ch);
        curl_close($ch);
        Yii::$app->db->createCommand()->truncateTable(PdfItem::tableName())->execute();
        if (!file_exists(Yii::getAlias(self::RESULT_FILE))) {
            throw new \Exception('Отсутвует файл с результатами');
        }
        $data = json_decode(file_get_contents(Yii::getAlias(self::RESULT_FILE)), true);
        $typeColors = array_map(function ($value) {
            return $this->hexToRgb($value);
        }, PdfItem::getTypeColors());
        $columns = ['filename', 'color', 'type', 'value'];
        $dataForInsert = [];
        foreach ($data as $fileResult) {
            foreach ($fileResult['items'] as $item) {
                if ($item['colors']) {
                    $colors = explode(',', $item['colors']);
                    $rgb = array_map(function ($value) {
                        return floor(255 * $value);
                    }, $colors);
                    $type = $this->getTypeClosestColor($rgb, $typeColors);
                    $value = $this->formatValue($type, $item['text']);
                    $dataForInsert[] = [
                        $fileResult['filename'],
                        implode(',', $rgb),
                        $type,
                        $value
                    ];
                    if (count($dataForInsert) == 50) {
                        $this->insert($columns, $dataForInsert);
                        $dataForInsert = [];
                    }
                }
            }
        }
        if (count($dataForInsert)) {
            $this->insert($columns, $dataForInsert);
        }

        return true;
    }

    protected function insert($columns, $data)
    {
        Yii::$app->db->createCommand()
            ->batchInsert(PdfItem::tableName(), $columns, $data)
            ->execute();
    }

    /**
     * @param $hex
     * @return array|string[]
     */
    protected function hexToRgb($hex)
    {
        return array_map(function ($value) {
            return base_convert($value, 16, 10);
        }, str_split(ltrim($hex, '#'), 2));
    }

    /**
     * Возвращает id наиболее подходящего цвета типа
     * @param $color
     * @param array $pallet
     * @return int
     */
    protected function getTypeClosestColor($color, array $pallet)
    {
        $distances = array_map(function ($colorFromPallet) use ($color) {
            return $this->getDistanceFromColor($color, $colorFromPallet);
        }, $pallet);
        asort($distances);
        $keys = array_keys($distances);
        return (int)$keys[0];
    }

    /**
     * Расстояние между двумя цветами
     * @param $a
     * @param $b
     * @return float
     */
    protected function getDistanceFromColor($a, $b)
    {
        list($r1, $g1, $b1) = $a;
        list($r2, $g2, $b2) = $b;
        return sqrt(pow($r2 - $r1, 2) + pow($g2 - $g1, 2) + pow($b2 - $b1, 2));
    }

    /**
     * Обработать текстовое значение по типу
     * @param $type
     * @param $text
     * @return string
     */
    protected function formatValue($type, $value)
    {
        if ($type == PdfItem::TYPE_UNIT) {
            preg_match('/\,?(?=[^,]*$)(.+)/u', $value, $matches);
            if (isset($matches[1])) {
                $value = $matches[1];
            }
        }
        if ($type == PdfItem::TYPE_ERROR) {
            preg_match('/±?(?=[^±]*$)(.+)$/u', $value, $matches);
            if (isset($matches[0])) {
                $value = $matches[0];
            }
        }

        return trim($value);
    }
}