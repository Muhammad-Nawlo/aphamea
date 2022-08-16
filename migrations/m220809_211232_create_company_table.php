<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%company}}`.
 */
class m220809_211232_create_company_table extends Migration
{
    const TABLE_NAME = '{{%company}}';

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
            'name' => $this->string(),
            'description' => $this->text(),
            'img' => $this->string(),
            'logo' => $this->string(),
            'numOfEmployee' => $this->integer()
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
