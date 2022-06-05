<?php

use yii\db\Migration;

/**
 * Class m220605_060814_result_pdf
 */
class m220605_060814_result_pdf extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('result_pdf_item', [
            'id' => $this->primaryKey(),
            'cluster_name' => $this->string(255)->comment('Кластер'),
            'import_share' => $this->float(2)->comment('Доля импорта'),
            'qty' => $this->float(2)->comment('Кол-во товаров'),
            'meassure' => $this->string(255)->comment('Измерение'),
            'delta' => $this->string(255)->comment('Диапазон'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220605_060814_result_pdf cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220605_060814_result_pdf cannot be reverted.\n";

        return false;
    }
    */
}
