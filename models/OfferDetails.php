<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "offer_details".
 *
 * @property int|null $medicineId
 * @property int|null $offerId
 * @property int|null $quantity
 * @property int|null $extraMedicineId
 * @property int|null $extraQuantity
 *
 * @property Medicine $extraMedicine
 * @property Medicine $medicine
 * @property Offer $offer
 */
class OfferDetails extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'offer_details';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['medicineId', 'offerId', 'quantity', 'extraMedicineId', 'extraQuantity'], 'integer'],
            [['extraMedicineId'], 'exist', 'skipOnError' => true, 'targetClass' => Medicine::className(), 'targetAttribute' => ['extraMedicineId' => 'id']],
            [['medicineId'], 'exist', 'skipOnError' => true, 'targetClass' => Medicine::className(), 'targetAttribute' => ['medicineId' => 'id']],
            [['offerId'], 'exist', 'skipOnError' => true, 'targetClass' => Offer::className(), 'targetAttribute' => ['offerId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'medicineId' => 'Medicine ID',
            'offerId' => 'Offer ID',
            'quantity' => 'Quantity',
            'extraMedicineId' => 'Extra Medicine ID',
            'extraQuantity' => 'Extra Quantity',
        ];
    }

    /**
     * Gets query for [[ExtraMedicine]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExtraMedicine()
    {
        return $this->hasOne(Medicine::className(), ['id' => 'extraMedicineId']);
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
     * Gets query for [[Offer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOffer()
    {
        return $this->hasOne(Offer::className(), ['id' => 'offerId']);
    }
}
