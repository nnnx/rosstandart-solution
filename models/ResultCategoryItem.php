<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "result_category_item".
 *
 * @property int $id
 * @property string|null $rubr0 Категория
 * @property string|null $rubr1 Подкатегория
 * @property float|null $rubr_1_qty Значение кат.
 * @property float|null $rubr_1_import_share Доля импорта кат.
 * @property float|null $rubr_0_qty Значение подкат.
 * @property float|null $rubr_0_import_share Доля импорта подкат.
 */
class ResultCategoryItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'result_category_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['rubr_1_qty', 'rubr_1_import_share', 'rubr_0_qty', 'rubr_0_import_share'], 'number'],
            [['rubr0', 'rubr1'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rubr0' => 'Категория',
            'rubr1' => 'Подкатегория',
            'rubr_1_qty' => 'Значение кат.',
            'rubr_1_import_share' => 'Доля импорта кат.',
            'rubr_0_qty' => 'Значение подкат.',
            'rubr_0_import_share' => 'Доля импорта подкат.',
        ];
    }

    /**
     * Получить уровень кластера значения, для цветов
     * @return int
     */
    public function getValueCluster($attr)
    {
        $value = (float)$this->$attr;
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
