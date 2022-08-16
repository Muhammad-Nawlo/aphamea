<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%medicine_pharmaceutical_form}}`.
 */
class m220809_212202_create_medicine_pharmaceutical_form_table extends Migration
{
    const TABLE_NAME = '{{%medicine_pharmaceutical_form}}';

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
            'pharmaceuticalFormId' => $this->integer(),
            'medicineId' => $this->integer(),
        ], $option);

        $this->addForeignKey('fk_type_medicine_1', self::TABLE_NAME, 'pharmaceuticalFormId', 'pharmaceutical_form', 'id');
        $this->addForeignKey('fk_type_medicine_2', self::TABLE_NAME, 'medicineId', 'medicine', 'id');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_type_medicine_2', self::TABLE_NAME);
        $this->dropForeignKey('fk_type_medicine_1', self::TABLE_NAME);
        $this->dropTable(self::TABLE_NAME);
    }
}
