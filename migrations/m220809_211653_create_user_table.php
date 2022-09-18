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
            'firstName' => $this->string(),
            'lastName' => $this->string(),
            'regionId' => $this->integer(),
            'cityId' => $this->integer(),
            'countryId' => $this->integer(),
            'img' => $this->string(),
            'role' => $this->integer(),
            'accessToken' => $this->string(),
            'email' => $this->string()->unique(),
            'password' => $this->string(),
            'specialMark' => $this->string(),
            'createdAt' => $this->date()
        ], $options);
        $this->addForeignKey('fk_user_region_regionId', self::TABLE_NAME, 'regionId', 'region', 'id');
        $this->addForeignKey('fk_user_region_cityId', self::TABLE_NAME, 'cityId', 'city', 'id');
        $this->addForeignKey('fk_user_region_countryId', self::TABLE_NAME, 'countryId', 'country', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_user_region_countryId', self::TABLE_NAME);
        $this->dropForeignKey('fk_user_region_cityId', self::TABLE_NAME);
        $this->dropForeignKey('fk_user_region_regionId', self::TABLE_NAME);
        $this->dropTable(self::TABLE_NAME);
    }
}
