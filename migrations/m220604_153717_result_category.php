<?php

use yii\db\Migration;

/**
 * Class m220604_153717_result_category
 */
class m220604_153717_result_category extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        //результаты по категориями
        $this->createTable('result_category_item', [
            'id' => $this->primaryKey(),
            'rubr0' => $this->string(255)->comment('Категория'),
            'rubr1' => $this->string(255)->comment('Подкатегория'),
            'rubr_1_qty' => $this->float(2)->comment('Значение кат.'),
            'rubr_1_import_share' => $this->float(2)->comment('Доля импорта кат.'),
            'rubr_0_qty' => $this->float(2)->comment('Значение подкат.'),
            'rubr_0_import_share' => $this->float(2)->comment('Доля импорта подкат.'),
        ]);

        $this->createIndex('ix_result_category_item__rubr0', 'result_category_item', 'rubr0');
        $this->createIndex('ix_result_category_item__rubr1', 'result_category_item', 'rubr1');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220604_153717_result_category cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220604_153717_result_category cannot be reverted.\n";

        return false;
    }
    */
}
