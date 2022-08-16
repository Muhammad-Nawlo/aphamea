<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user}}`.
 */
class m220809_211653_create_user_table extends Migration
{
    const TABLE_NAME = '{{%user}}';

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
            'first_name' => $this->string(),
            'last_name' => $this->string(),
            'regionId' => $this->integer(),
            'img' => $this->string(),
            'role' => $this->integer(),
            'companyId' => $this->integer(),
            'accessToken' => $this->string(),
            'email' => $this->string(),
            'password' => $this->string()
        ], $options);
        $this->addForeignKey('fk_user_region_regionId', self::TABLE_NAME, 'regionId', 'region', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_user_region_regionId', self::TABLE_NAME);
        $this->dropTable(self::TABLE_NAME);
    }
}
