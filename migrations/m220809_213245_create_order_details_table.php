<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order_details}}`.
 */
class m220809_213245_create_order_details_table extends Migration
{
    const TABLE_NAME = '{{%order_details}}';

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
            'offerId' => $this->integer(),
            'orderId' => $this->integer(),
            'quantity' => $this->integer()
        ], $option);

        $this->addForeignKey('fk_offer_orderDetails_offerId', self::TABLE_NAME, 'offerId', 'offer', 'id');
        $this->addForeignKey('fk_order_orderDetails_orderId', self::TABLE_NAME, 'orderId', 'order', 'id');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_order_orderDetails_orderId', self::TABLE_NAME);
        $this->dropForeignKey('fk_offer_orderDetails_offerId', self::TABLE_NAME);
        $this->dropTable(self::TABLE_NAME);
    }
}
