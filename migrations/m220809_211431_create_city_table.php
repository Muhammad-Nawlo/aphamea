<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%city}}`.
 */
class m220809_211431_create_city_table extends Migration
{
    const TABLE_NAME = '{{%city}}';

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
            'nameAr' => $this->string(),
            'nameEn' => $this->string(),
            'countryId' => $this->integer()
        ], $options);
        $this->addForeignKey('fk_city_country_countryId', self::TABLE_NAME, 'countryId', 'country', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_city_country_countryId', self::TABLE_NAME);
        $this->dropTable(self::TABLE_NAME);
    }
}
