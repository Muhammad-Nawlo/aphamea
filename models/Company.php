<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "company".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $description
 * @property string|null $img
 * @property string|null $logo
 * @property int|null $numOfEmployee
 *
 * @property CompanyTeam[] $companyTeams
 * @property Contact[] $contacts
 */
class Company extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['numOfEmployee'], 'integer'],
            [['name', 'img', 'logo'], 'string', 'max' => 255],
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
            'description' => 'Description',
            'img' => 'Img',
            'logo' => 'Logo',
            'numOfEmployee' => 'Num Of Employee',
        ];
    }

    /**
     * Gets query for [[CompanyTeams]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyTeams()
    {
        return $this->hasMany(User::class, ['id' => 'userId'])
            ->viaTable('company_team', ['companyId' => 'id']);
    }

    /**
     * Gets query for [[Contacts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContacts()
    {
        return $this->hasMany(Contact::className(), ['companyId' => 'id']);
    }



    public function contacts()
    {
        return $this->hasManyThrough(Company::class, Contact::class);
    }
    // public function functionGetContactCompany()
    // {
    //     $rows = (new Qu)
    // }
}
