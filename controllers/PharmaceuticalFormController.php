<?php

namespace app\controllers;

use app\models\Medicine;
use app\models\MedicinePharmaceuticalForm;
use app\models\PharmaceuticalForm;
use PhpOffice\PhpSpreadsheet\IOFactory;
use sizeg\jwt\JwtHttpBearerAuth;
use Yii;
use yii\db\Query;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\Cors;
use yii\helpers\Url;

class PharmaceuticalFormController extends \yii\web\Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        $behaviors = parent::behaviors(); // TODO: Change the autogenerated stub
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => Yii::$app->params['allowedDomains'],
                'Access-Control-Request-Method' => ['*'],
                'Access-Control-Allow-Methods' => ['POST', 'PUT', 'OPTIONS', 'GET'],
                'Access-Control-Allow-Headers' => ['*'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
            ]
        ];
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                HttpBearerAuth::class,
                QueryParamAuth::class,
                JwtHttpBearerAuth::class
            ]
        ];
        return $behaviors;
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'api-docs' => [
                'class' => 'genxoft\swagger\ViewAction',
                'apiJsonUrl' => \yii\helpers\Url::to(['/site/api-json'], true),
            ],
            'api-json' => [
                'class' => 'genxoft\swagger\JsonAction',
                'dirs' => [
                    Yii::getAlias('@app/controllers'),
                    Yii::getAlias('@app/models'),
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return ['msg' => 'ok', 'status' => 'working'];
    }

    public function actionReadPharmaceuticalForms()
    {
        $spreadSheet = IOFactory::load(Url::to('@app/web/medicines.xlsx'));
        $spreadSheetArray = $spreadSheet->getActiveSheet()->toArray();
        array_splice($spreadSheetArray, 0, 1);
        foreach ($spreadSheetArray as $p) {
            $newPharmaceuticalForm = new PharmaceuticalForm();
            $newPharmaceuticalForm->name = trim($p[4]);
            if ($newPharmaceuticalForm->validate()) {
                $newPharmaceuticalForm->save();
            }
        }
    }

    public function actionAdd()
    {
        try {
            $errors = [
                'notExist' => [],
                'pharmaceuticalFormErrors' => [],
                'used' => [],
            ];


            $data = json_decode(Yii::$app->request->getRawBody(), true);
            if (!isset($data['pharmaceuticalForms'])) {
                return ["status" => "error", "details" => "There are missing params (pharmaceuticalForms)"];
            }

            if (!isset($data['deletedPharmaceuticalForms'])) {
                return ["status" => "error", "details" => "There is missing params (deletedPharmaceuticalForms)"];
            }

            if (!empty($data['deletedPharmaceuticalForms'])) {
                foreach ($data['deletedPharmaceuticalForms'] as $id) {
                    $pharmaceuticalForm = PharmaceuticalForm::findOne(['id' => (int)$id]);
                    if ($pharmaceuticalForm !== null && MedicinePharmaceuticalForm::findOne(['pharmaceuticalFormId' => (int)$id]) === null) {
                        MedicinePharmaceuticalForm::deleteAll(['pharmaceuticalFormId' => (int)$id]);
                        $pharmaceuticalForm->delete();
                    } else {
                        if ($pharmaceuticalForm) {
                            $errors['used'][] = ["Pharmaceutical Form that has this name $pharmaceuticalForm->name is used"];
                        } else {
                            $errors['notExist'][] = ["Pharmaceutical Form that has this id $id is not exist"];
                        }
                    }
                }
            }

            foreach ($data['pharmaceuticalForms'] as $p) {
                $p = (array)$p;
                if ($p['id'] === '') {
                    $newPharmaceuticalForm = new PharmaceuticalForm();
                } else {
                    $newPharmaceuticalForm = PharmaceuticalForm::findOne(['id' => (int)$p['id']]);
                    if ($newPharmaceuticalForm === null) {
                        $errors['notExist'][] = ["Pharmaceutical Form that has this id " . $p['id'] . " is not exist"];
                        continue;
                    }
                }
                $newPharmaceuticalForm->name = trim($p['name']);
                if ($newPharmaceuticalForm->validate()) {
                    $newPharmaceuticalForm->save();
                } else {
                    $errors['pharmaceuticalFormErrors'][] = $newPharmaceuticalForm->getErrors();
                }
            }
            return ['status' => 'ok', 'errors' => $errors];
        } catch (\Exception $e) {
            return ['msg' => 'error', 'details' => $e->getMessage()];
        }
    }

    public function actionGetAll()
    {
        $pharmaceuticalForm = PharmaceuticalForm::find()->where([])->asArray()->all();
        return ['status' => 'status', 'pharmaceuticalForm' => $pharmaceuticalForm];
    }

    function actionGet($id)
    {
        $pharmaceuticalForm = PharmaceuticalForm::find()->where(['id' => (int)$id])->one();
        if ($pharmaceuticalForm === null)
            return ["status" => "error", "details" => "There is no Pharmaceutical Form"];

        return ['status' => 'ok', 'pharmaceuticalForm' => $pharmaceuticalForm];
    }


    function actionGetMedicines($id)
    {
        try {
            $medicines = (new Query())
                ->select([
                    "productName",
                    "indications",
                    "barcode",
                    "composition",
                    "packing",
                    "expiredDate",
                    "price",
                    "netPrice",
                    "name"
                ])
                ->from('medicine_pharmaceutical_form')
                ->innerJoin('medicine', 'medicine.id=medicine_pharmaceutical_form.medicineId')
                ->innerJoin('pharmaceutical_form', 'pharmaceutical_form.id=medicine_pharmaceutical_form.pharmaceuticalFormId')
                ->where(['pharmaceutical_form.id' => $id])
                ->all();

            if (empty($medicines))
                return ['status' => 'error', 'details' => "There are no medicine that has this Pharmaceutical Form id ($id)"];

            return ['status' => 'ok', 'medicines' => $medicines];
        } catch (\Exception $e) {
            return ['msg' => 'error', 'details' => $e->getMessage()];
        }
    }
}
