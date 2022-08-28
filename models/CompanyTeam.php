<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "company_team".
 *
 * @property int $id
 * @property int|null $companyId
 * @property int|null $userId
 * @property string|null $position
 * @property string|null $description
 *
 * @property Company $company
 * @property User $user
 */
class CompanyTeam extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company_team';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['companyId', 'userId'], 'integer'],
            [['description'], 'string'],
            [['position'], 'string', 'max' => 255],
            [['companyId'], 'exist', 'skipOnError' => true, 'targetClass' => Company::className(), 'targetAttribute' => ['companyId' => 'id']],
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
            'companyId' => 'Company ID',
            'userId' => 'User ID',
            'position' => 'Position',
            'description' => 'Description',
        ];
    }

    /**
     * Gets query for [[Company]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'companyId']);
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

    public function beforeValidate()
    {
        if(!parent::beforeValidate()){
            return false;
        }
        $this->companyId = (int)$this->companyId;
        $this->userId = (int)$this->userId;
        return true;
    }
    // public function beforeSave($insert)
    // {
    //     if (!parent::beforeSave($insert)) {
    //         return false;
    //     }
    //     $this->companyId = (int)$this->companyId;
    //     $this->userId = (int)$this->userId;
    //     return true;
    // }
}
