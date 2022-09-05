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
 * @property int|null $isCanceled
 * @property int|null $isCompleted
 *
 * @property OrderDetails[] $orderDetails
 * @property User $representative
 * @property User $user
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
            [['userId', 'representativeId', 'isCanceled', 'isCompleted'], 'integer'],
            [['orderDate'], 'safe'],
            [['representativeId'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['representativeId' => 'id']],
            [['userId'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['userId' => 'id']],
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

    /**
     * Gets query for [[Representative]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRepresentative()
    {
        return $this->hasOne(User::className(), ['id' => 'representativeId']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'userId']);
    }
}
