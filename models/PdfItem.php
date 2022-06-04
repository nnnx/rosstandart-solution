<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pdf_item".
 *
 * @property int $id
 * @property string|null $filename Файл
 * @property string|null $color Цвет
 * @property int|null $type Тип
 * @property string|null $value Значение
 */
class PdfItem extends \yii\db\ActiveRecord
{
    const TYPE_DESC = 0;

    const TYPE_UNIT = 1;

    const TYPE_ERROR = 2;

    /**
     * Названия типов
     * @return string[]
     */
    public static function getTypeLabels()
    {
        return [
            self::TYPE_DESC => 'Описание типа СИ',
            self::TYPE_UNIT => 'Единица измерения',
            self::TYPE_ERROR => 'Погрешность',
        ];
    }

    /**
     * Цвета для типов
     * @return string[]
     */
    public static function getTypeColors()
    {
        return [
            self::TYPE_DESC => '#FFFF00',
            self::TYPE_UNIT => '#00FF00',
            self::TYPE_ERROR => '#00AEEE',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pdf_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type'], 'default', 'value' => null],
            [['type'], 'integer'],
            [['value'], 'string'],
            [['filename', 'color'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'filename' => 'Файл',
            'color' => 'Цвет',
            'type' => 'Тип',
            'value' => 'Значение',
        ];
    }

    /**
     * @return string
     */
    public function getTypeLabel()
    {
        return self::getTypeLabels()[$this->type] ?? 'Не указан';
    }
}
