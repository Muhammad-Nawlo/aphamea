<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "activity".
 *
 * @property int $id
 * @property int|null $type
 * @property string|null $publishedDate
 * @property string|null $imgs
 * @property string|null $content
 * @property int|null $isRead
 */
class Activity extends \yii\db\ActiveRecord
{
    const ACTIVITY_TYPE = [0, 1, 2, 3];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'activity';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'isRead'], 'integer'],
            [['publishedDate'], 'safe'],
            [['content'], 'string'],
            [['imgs'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'publishedDate' => 'Published Date',
            'imgs' => 'Imgs',
            'content' => 'Content',
            'isRead' => 'Is Read',
        ];
    }
}
