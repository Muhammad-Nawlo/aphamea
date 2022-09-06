<?php

namespace app\models;

use Yii;
use app\models\Medicine;
use app\models\MedicinePharmaceuticalForm;

/**
 * This is the model class for table "pharmaceutical_form".
 *
 * @property int $id
 * @property string|null $name
 *
 * @property MedicinePharmaceuticalForm[] $medicinePharmaceuticalForms
 */
class PharmaceuticalForm extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pharmaceutical_form';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }

    /**
     * Gets query for [[MedicinePharmaceuticalForms]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMedicinePharmaceuticalForms()
    {
        return $this->hasMany(MedicinePharmaceuticalForm::className(), ['pharmaceuticalFormId' => 'id']);
    }


    public function getMedicines()
    {
        return $this->hasMany(Medicine::class, ['id' => 'medicineId'])
            ->viaTable('medicine_pharmaceutical_form', ['pharmaceuticalFormId' => 'id']);
    }
}
