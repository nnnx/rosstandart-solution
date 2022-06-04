<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;

class ResultCategoryItemSearch extends ResultCategoryItem
{
    /** @var string[] */
    public $rubr0_selected = [];

    /** @var string[] */
    public $rubr1_selected = [];

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['rubr0_selected', 'rubr1_selected'], 'safe'],
        ]);
    }

    public function attributeLabels()
    {
        return [
            'rubr0_selected' => 'Категории',
            'rubr1_selected' => 'Подкатегории',
        ];
    }

    /**
     * Creates data provider instance with search query applied
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = ResultCategoryItem::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'rubr0' => SORT_ASC,
                    'rubr1' => SORT_ASC,
                ]
            ],
        ]);
        $this->load($params);
        $query->andFilterWhere([
            'rubr0' => $this->rubr0_selected,
            'rubr1' => $this->rubr1_selected,
        ]);

        return $dataProvider;
    }

    /**
     * @return array
     */
    public function getRubr0Select()
    {
        $items = ResultCategoryItem::find()
            ->select('rubr0')
            ->orderBy(['rubr0' => SORT_ASC])
            ->asArray()
            ->distinct()
            ->column();
        return array_combine($items, $items);
    }

    /**
     * @return array
     */
    public function getRubr1Select()
    {
        $items = ResultCategoryItem::find()
            ->select('rubr1')
            ->orderBy(['rubr1' => SORT_ASC])
            ->asArray()
            ->distinct()
            ->column();
        return array_combine($items, $items);
    }

    /**
     * Данные для графика топ20 категорий
     * @return array
     */
    public static function getChartTop20()
    {
        $items = ResultCategoryItem::find()
            ->select([
                'rubr0 as category',
                'MAX(rubr_0_import_share) as value',
            ])
            ->orderBy(['value' => SORT_DESC])
            ->groupBy(['rubr0'])
            ->limit(20)
            ->asArray()
            ->all();

        return $items;
    }
}
