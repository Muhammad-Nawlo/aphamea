<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%medicine}}`.
 */
class m220809_211343_create_medicine_table extends Migration
{
    const TABLE_NAME = '{{%medicine}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $options = null;
        if ($this->db->driverName == 'mysql') {
            $options = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'barcode' => $this->string()->unique(),
            'productName' => $this->string(),
            'indications' => $this->string(),
            'packing' => $this->integer(),
            'composition' => $this->text(),
            'expiredDate' => $this->dateTime(),
            'imgs' => $this->string(),
            'price' => $this->float(),
            'netPrice' => $this->float()
        ], $options);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
