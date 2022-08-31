<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%medicine_category}}`.
 */
class m220809_212110_create_medicine_category_table extends Migration
{
    const TABLE_NAME = '{{%medicine_category}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $option = null;
        if ($this->db->driverName == 'mysql') {
            $option = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'categoryId' => $this->integer(),
            'medicineId' => $this->integer(),
        ], $option);
        $this->addForeignKey('fk_category_medicine_1', self::TABLE_NAME, 'categoryId', 'category', 'id');
        $this->addForeignKey('fk_category_medicine_2', self::TABLE_NAME, 'medicineId', 'medicine', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_category_medicine_2', self::TABLE_NAME);
        $this->dropForeignKey('fk_category_medicine_1', self::TABLE_NAME);
        $this->dropTable(self::TABLE_NAME);
    }
}
