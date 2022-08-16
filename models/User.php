<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property int|null $regionId
 * @property string|null $img
 * @property int|null $role
 * @property int|null $companyId
 * @property string|null $accessToken
 * @property string|null $email
 * @property string|null $password
 *
 * @property CompanyTeam[] $companyTeams
 * @property Contact[] $contacts
 * @property Region $region
 */
class User extends \yii\db\ActiveRecord
{
    //Doctor
    //Pharmacist
    //Sales Representative
    //Scientific representative
    //Agent
    const ROLE = [1, 2, 3, 4, 5];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['regionId', 'role', 'companyId'], 'integer'],
            [['img', 'accessToken', 'email', 'password'], 'string', 'max' => 255],
            [['regionId'], 'exist', 'skipOnError' => true, 'targetClass' => Region::className(), 'targetAttribute' => ['regionId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'regionId' => 'Region ID',
            'img' => 'Img',
            'role' => 'Role',
            'companyId' => 'Company ID',
            'accessToken' => 'Access Token',
            'email' => 'Email',
            'password' => 'Password',
        ];
    }

    /**
     * Gets query for [[CompanyTeams]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyTeams()
    {
        return $this->hasMany(CompanyTeam::className(), ['userId' => 'id']);
    }

    /**
     * Gets query for [[Contacts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContacts()
    {
        return $this->hasMany(Contact::className(), ['userId' => 'id']);
    }

    /**
     * Gets query for [[Region]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRegion()
    {
        return $this->hasOne(Region::className(), ['id' => 'regionId']);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return User::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return User::findOne(['accessToken' => $token]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return User::findOne(['accessToken' => $username]);

    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

}
