<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "medicine_pharmaceutical_form".
 *
 * @property int $id
 * @property int|null $pharmaceuticalFormId
 * @property int|null $medicineId
 *
 * @property Medicine $medicine
 * @property PharmaceuticalForm $pharmaceuticalForm
 */
class MedicinePharmaceuticalForm extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'medicine_pharmaceutical_form';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pharmaceuticalFormId', 'medicineId'], 'integer'],
            [['pharmaceuticalFormId'], 'exist', 'skipOnError' => true, 'targetClass' => PharmaceuticalForm::className(), 'targetAttribute' => ['pharmaceuticalFormId' => 'id']],
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
            'pharmaceuticalFormId' => 'Pharmaceutical Form ID',
            'medicineId' => 'Medicine ID',
        ];
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

    /**
     * Gets query for [[PharmaceuticalForm]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPharmaceuticalForm()
    {
        return $this->hasOne(PharmaceuticalForm::className(), ['id' => 'pharmaceuticalFormId']);
    }
}
