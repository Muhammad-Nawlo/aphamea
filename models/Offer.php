<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "offer".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $creationDate
 * @property int|null $offerStatus
 * @property int|null $orderCount
 *
 * @property OfferDetails[] $offerDetails
 * @property OrderDetails[] $orderDetails
 */
class Offer extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'offer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['creationDate'], 'safe'],
            [['offerStatus', 'orderCount'], 'integer'],
            [['name'], 'string', 'max' => 255],
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
            'creationDate' => 'Creation Date',
            'offerStatus' => 'Offer Status',
            'orderCount' => 'Order Count',
        ];
    }

    /**
     * Gets query for [[OfferDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOfferDetails()
    {
        return $this->hasMany(OfferDetails::className(), ['offerId' => 'id']);
    }

    /**
     * Gets query for [[OrderDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderDetails()
    {
        return $this->hasMany(OrderDetails::className(), ['offerId' => 'id']);
    }
}
