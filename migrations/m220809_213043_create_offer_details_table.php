<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%offer_details}}`.
 */
class m220809_213043_create_offer_details_table extends Migration
{
    const TABLE_NAME = '{{%offer_details}}';

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
            'id'=>$this->primaryKey(),
            'medicineId' => $this->integer(),
            'offerId' => $this->integer(),
            'quantity' => $this->integer(),
            'extraMedicineId' => $this->integer(),
            'extraQuantity' => $this->integer(),
        ], $option);

        $this->addForeignKey('fk_offer_orderDetails_orderId', self::TABLE_NAME, 'offerId', 'offer', 'id');
        $this->addForeignKey('fk_medicine_orderDetails_medicineId', self::TABLE_NAME, 'medicineId', 'medicine', 'id');
        $this->addForeignKey('fk_medicineExtra_orderDetails_medicineId', self::TABLE_NAME, 'extraMedicineId', 'medicine', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_medicineExtra_orderDetails_medicineId', self::TABLE_NAME);
        $this->dropForeignKey('fk_medicine_orderDetails_medicineId', self::TABLE_NAME);
        $this->dropForeignKey('fk_offer_orderDetails_orderId', self::TABLE_NAME);
        $this->dropTable(self::TABLE_NAME);
    }
}
