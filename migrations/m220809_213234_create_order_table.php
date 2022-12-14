<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order}}`.
 */
class m220809_213234_create_order_table extends Migration
{
    const TABLE_NAME = '{{%order}}';

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
            'userId' => $this->integer(),
            'representativeId' => $this->integer(),
            'orderDate' => $this->dateTime(),
            'isCanceled' => $this->boolean(),
            'isCompleted' => $this->boolean(),
        ], $option);
        $this->addForeignKey('fk_user_order_userId', self::TABLE_NAME, 'userId', 'user', 'id');
        $this->addForeignKey('fk_user_order_representativeId', self::TABLE_NAME, 'representativeId', 'user', 'id');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
