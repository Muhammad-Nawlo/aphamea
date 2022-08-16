<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%contact}}`.
 */
class m220809_211851_create_contact_table extends Migration
{
    const TABLE_NAME = '{{%contact}}';

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
            'companyId' => $this->integer(),
            'userId' => $this->integer(),
            'type' => $this->integer(),
            'content' => $this->string()
        ], $options);
        $this->addForeignKey('fk_company_contact_companyId', 'contact', 'companyId', 'company', 'id');
        $this->addForeignKey('fk_user_contact_userId', 'contact', 'userId', 'user', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_user_contact_userId', self::TABLE_NAME);
        $this->dropForeignKey('fk_company_contact_companyId', self::TABLE_NAME);
        $this->dropTable(self::TABLE_NAME);
    }
}
