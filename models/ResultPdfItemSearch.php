<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;

class ResultPdfItemSearch extends ResultPdfItem
{
    /** @var string[] */
    public $cluster_names = [];

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['cluster_names'], 'safe'],
        ]);
    }

    public function attributeLabels()
    {
        return [
            'cluster_names' => 'Кластеры',
        ];
    }

    /**
     * Creates data provider instance with search query applied
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = ResultPdfItem::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'cluster_name' => SORT_ASC,
                ]
            ],
        ]);
        $this->load($params);
        $query->andFilterWhere(['cluster_name' => $this->cluster_names]);
        $query->andFilterWhere(['ilike', 'meassure', $this->meassure]);

        return $dataProvider;
    }

    /**
     * @return array
     */
    public function getNamesSelect()
    {
        $items = ResultPdfItem::find()
            ->select('cluster_name')
            ->orderBy(['cluster_name' => SORT_ASC])
            ->asArray()
            ->distinct()
            ->column();
        return array_combine($items, $items);
    }
}
