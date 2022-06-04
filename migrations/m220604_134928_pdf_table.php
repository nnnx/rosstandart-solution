<?php

use yii\db\Migration;

/**
 * Class m220604_134928_pdf_table
 */
class m220604_134928_pdf_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        //таблица для результатов обработки найденных аннотаций pdf
        $this->createTable('pdf_item', [
            'id' => $this->primaryKey(),
            'filename' => $this->string(255)->comment('Файл'),
            'color' => $this->string(255)->comment('Цвет'),
            'type' => $this->integer()->defaultValue(0)->comment('Тип'),
            'value' => $this->text()->comment('Значение'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220604_134928_pdf_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220604_134928_pdf_table cannot be reverted.\n";

        return false;
    }
    */
}
