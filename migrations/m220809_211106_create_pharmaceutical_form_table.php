<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%pharmaceutical_form}}`.
 */
class m220809_211106_create_pharmaceutical_form_table extends Migration
{
    const TABLE_NAME = '{{%pharmaceutical_form}}';

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
            'name' => $this->string()->unique()
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
