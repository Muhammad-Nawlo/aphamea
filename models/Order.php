<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "order".
 *
 * @property int $id
 * @property int|null $userId
 * @property int|null $representativeId
 * @property string|null $orderDate
 * @property int|null $companyId
 * @property int|null $isCanceled
 * @property int|null $isCompleted
 *
 * @property OrderDetails[] $orderDetails
 */
class Order extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['userId', 'representativeId', 'companyId', 'isCanceled', 'isCompleted'], 'integer'],
            [['orderDate'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'userId' => 'User ID',
            'representativeId' => 'Representative ID',
            'orderDate' => 'Order Date',
            'companyId' => 'Company ID',
            'isCanceled' => 'Is Canceled',
            'isCompleted' => 'Is Completed',
        ];
    }

    /**
     * Gets query for [[OrderDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderDetails()
    {
        return $this->hasMany(OrderDetails::className(), ['orderId' => 'id']);
    }
}
