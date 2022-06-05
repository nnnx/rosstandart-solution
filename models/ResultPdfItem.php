<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "result_pdf_item".
 *
 * @property int $id
 * @property string|null $cluster_name Кластер
 * @property float|null $import_share Доля импорта
 * @property float|null $qty Кол-во товаров
 * @property string|null $meassure Измерение
 * @property string|null $delta Диапазон
 */
class ResultPdfItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'result_pdf_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['import_share', 'qty'], 'number'],
            [['cluster_name', 'meassure', 'delta'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cluster_name' => 'Кластер',
            'import_share' => 'Доля импорта',
            'qty' => 'Кол-во товаров',
            'meassure' => 'Измерение',
            'delta' => 'Диапазон',
        ];
    }

    /**
     * Получить уровень кластера значения, для цветов
     * @return int
     */
    public function getClassByShare()
    {
        $value = (float)$this->import_share;
        if ($value > 0.8) {
            return 1;
        } else if ($value > 0.6) {
            return 2;
        } else if ($value > 0.4) {
            return 3;
        }
        return 4;
    }
}
