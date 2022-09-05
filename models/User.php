<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string|null $firstName
 * @property string|null $lastName
 * @property int|null $regionId
 * @property string|null $img
 * @property int|null $role
 * @property string|null $accessToken
 * @property string|null $email
 * @property string|null $password
 * @property string|null $specialMarks
 *
 * @property CompanyTeam[] $companyTeams
 * @property Contact[] $contacts
 * @property Order[] $orders
 * @property Order[] $orders0
 * @property Region $region
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{
        //Doctor
    //Pharmacist
    //Sales Representative
    //Scientific representative
    //Agent
    const ROLE = [0, 1, 2, 3, 4, 5];
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
            [['regionId', 'role'], 'integer'],
            [['firstName', 'lastName', 'img', 'accessToken', 'email', 'password', 'specialMarks'], 'string', 'max' => 255],
            [['email'], 'unique'],
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
            'firstName' => 'First Name',
            'lastName' => 'Last Name',
            'regionId' => 'Region ID',
            'img' => 'Img',
            'role' => 'Role',
            'accessToken' => 'Access Token',
            'email' => 'Email',
            'password' => 'Password',
            'specialMarks' => 'Special Marks',
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
     * Gets query for [[Orders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['representativeId' => 'id']);
    }

    /**
     * Gets query for [[Orders0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders0()
    {
        return $this->hasMany(Order::className(), ['userId' => 'id']);
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
        return User::findOne(['username' => $username]);
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
        //        return $this->authKey;
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
