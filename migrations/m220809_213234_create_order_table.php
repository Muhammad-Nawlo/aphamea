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
            'companyId' => $this->integer(),
            'isCanceled' => $this->boolean(),
            'isCompleted' => $this->boolean(),
        ], $option);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
