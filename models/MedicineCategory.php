<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "medicine_category".
 *
 * @property int $id
 * @property int|null $categoryId
 * @property int|null $medicineId
 *
 * @property Category $category
 * @property Medicine $medicine
 */
class MedicineCategory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'medicine_category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['categoryId', 'medicineId'], 'integer'],
            [['categoryId'], 'exist', 'skipOnError' => true, 'targetClass' => Category::className(), 'targetAttribute' => ['categoryId' => 'id']],
            [['medicineId'], 'exist', 'skipOnError' => true, 'targetClass' => Medicine::className(), 'targetAttribute' => ['medicineId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'categoryId' => 'Category ID',
            'medicineId' => 'Medicine ID',
        ];
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'categoryId']);
    }

    /**
     * Gets query for [[Medicine]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMedicine()
    {
        return $this->hasOne(Medicine::className(), ['id' => 'medicineId']);
    }
}
