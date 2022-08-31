<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "medicine".
 *
 * @property int $id
 * @property string|null $barcode
 * @property string|null $productName
 * @property string|null $indications
 * @property int|null $packing
 * @property string|null $composition
 * @property string|null $expiredDate
 * @property string|null $imgs
 * @property float|null $price
 * @property float|null $netPrice
 *
 * @property MedicineCategory[] $medicineCategories
 * @property MedicinePharmaceuticalForm[] $medicinePharmaceuticalForms
 * @property OfferDetails[] $offerDetails
 * @property OfferDetails[] $offerDetails0
 */
class Medicine extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'medicine';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['packing'], 'integer'],
            [['composition'], 'string'],
            [['expiredDate'], 'safe'],
            [['price', 'netPrice'], 'number'],
            [['barcode', 'productName', 'indications', 'imgs'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'barcode' => 'Barcode',
            'productName' => 'Product Name',
            'indications' => 'Indications',
            'packing' => 'Packing',
            'composition' => 'Composition',
            'expiredDate' => 'Expired Date',
            'imgs' => 'Imgs',
            'price' => 'Price',
            'netPrice' => 'Net Price',
        ];
    }

    /**
     * Gets query for [[MedicineCategories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMedicineCategories()
    {
        return $this->hasMany(Category::class, ['id' => 'categoryId'])
            ->viaTable('medicine_category', ['medicineId' => 'id']);
    }

    /**
     * Gets query for [[MedicinePharmaceuticalForms]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMedicinePharmaceuticalForms()
    {
        return $this->hasMany(PharmaceuticalForm::class, ['id' => 'pharmaceuticalFormId'])
        ->viaTable('medicine_pharmaceutical_form', ['medicineId', 'id']);
    }

    /**
     * Gets query for [[OfferDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOfferDetails()
    {
        return $this->hasMany(OfferDetails::className(), ['extraMedicineId' => 'id']);
    }

    /**
     * Gets query for [[OfferDetails0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOfferDetails0()
    {
        return $this->hasMany(OfferDetails::className(), ['medicineId' => 'id']);
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        $this->packing = (int)$this->packing;
        $this->price = (float)$this->price;
        $this->netPrice = (float)$this->netPrice;

        return true;
    }
}
