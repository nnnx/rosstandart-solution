<?php

use yii\bootstrap4\Nav;

echo Nav::widget([
    'options' => ['class' => 'nav nav-tabs'],
    'items' => [
        [
            'label' => 'По категориям',
            'url' => ['site/index']
        ],
    ],
]);