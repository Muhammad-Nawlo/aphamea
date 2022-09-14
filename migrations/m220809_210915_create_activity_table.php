<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%activity}}`.
 */
class m220809_210915_create_activity_table extends Migration
{
    const TABLE_NAME = '{{%activity}}';

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
            'type' => $this->integer(),
            'publishedDate' => $this->dateTime(),
            'imgs' => $this->string(),
            'title' => $this->string(),
            'content' => $this->text(),
            'isRead' => $this->boolean()
        ], $options);
        $com = Yii::$app->db->createCommand("Alter table activity ADD FULLTEXT (title,content)");
        $com->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
