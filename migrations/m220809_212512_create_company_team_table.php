<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%company_team}}`.
 */
class m220809_212512_create_company_team_table extends Migration
{
    const TABLE_NAME = '{{%company_team}}';

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
            'position' => $this->string(),
            'description' => $this->text()
        ], $options);

        $this->addForeignKey('fk_company_companyTeam_companyId', 'company_team', 'companyId', 'company', 'id');
        $this->addForeignKey('fk_user_companyTeam_companyId', 'company_team', 'userId', 'user', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_user_companyTeam_companyId', self::TABLE_NAME);
        $this->dropForeignKey('fk_company_companyTeam_companyId', self::TABLE_NAME);
        $this->dropTable(self::TABLE_NAME);
    }
}
