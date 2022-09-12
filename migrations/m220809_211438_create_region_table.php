<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%region}}`.
 */
class m220809_211438_create_region_table extends Migration
{
    const TABLE_NAME = '{{%region}}';

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
            'cityId' => $this->integer(),
            'regionAr' => $this->string(),
            'regionEn' => $this->string(),
        ], $options);
        $this->addForeignKey('fk_city_region_cityId', 'region', 'cityId', 'city', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_city_region_cityId', self::TABLE_NAME);
        $this->dropTable(self::TABLE_NAME);
    }
}
