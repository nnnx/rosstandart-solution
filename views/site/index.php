<?php

/**
 * @var yii\web\View $this
 * @var app\models\ResultCategoryItemSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 */

use yii\grid\GridView;
use richardfan\widget\JSRegister;

$this->title = 'Результаты: по категориям';
?>

<h1>Результаты</h1>

<?= $this->render('_tabs') ?>

<br/>

<?= $this->render('_category_search', ['model' => $searchModel]) ?>

<div class="charts <?php if (Yii::$app->request->getQueryParams()) echo 'd-none'?>">
    <h4>Топ 20 категорий по доле импорта</h4>
    <div id="chartTop20" style="height: 400px;"></div>
</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        'rubr0',
        'rubr1',
        'rubr_1_qty',
        [
            'attribute' => 'rubr_1_import_share',
            'contentOptions' => function ($model) {
                return [
                    'class' => 'td-cluster td-cluster-' . $model->getValueCluster('rubr_1_import_share'),
                ];
            },
        ],
        'rubr_0_qty',
        [
            'attribute' => 'rubr_0_import_share',
            'contentOptions' => function ($model) {
                return [
                    'class' => 'td-cluster td-cluster-' . $model->getValueCluster('rubr_0_import_share'),
                ];
            },
        ],
    ],
    'summary' => false,
    'tableOptions' => [
        'class' => 'table table-bordered'
    ],
]); ?>

<?php JSRegister::begin() ?>
<script>
    function chartTop20() {
        var data = <?=json_encode(\app\models\ResultCategoryItemSearch::getChartTop20())?>;
        var root = am5.Root.new("chartTop20");
        root.setThemes([
            am5themes_Animated.new(root)
        ]);
        var chart = root.container.children.push(
            am5percent.PieChart.new(root, {
                endAngle: 270,
                radius: am5.percent(90),
                innerRadius: am5.percent(30),
                layout: root.horizontalLayout
            })
        );
        var series = chart.series.push(
            am5percent.PieSeries.new(root, {
                valueField: "value",
                categoryField: "category",
                endAngle: 270
            })
        );
        series.states.create("hidden", {
            endAngle: -90
        });
        series.data.setAll(data);
        series.labels.template.set("visible", false);
        series.ticks.template.set("visible", false);
        var legend = chart.children.push(am5.Legend.new(root, {
            centerY: am5.percent(50),
            y: am5.percent(50),
            marginTop: 15,
            marginBottom: 15,
            layout: am5.GridLayout.new(root, {
                maxColumns: 2,
                fixedWidthGrid: true
            })
        }));
        legend.data.setAll(series.dataItems);
        series.appear(1000, 100);
    }

    am5.ready(function () {
        chartTop20();
    });
</script>
<?php JSRegister::end() ?>

