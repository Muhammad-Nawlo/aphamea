<?php

namespace app\controllers;

use Yii;
use yii\db\Query;
use yii\helpers\Url;
use yii\filters\Cors;
use yii\web\Response;
use app\models\Medicine;
use app\helpers\HelperFunction;
use sizeg\jwt\JwtHttpBearerAuth;
use app\models\PharmaceuticalForm;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use app\models\MedicinePharmaceuticalForm;

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
        // $behaviors['authenticator'] = [
        //     'class' => CompositeAuth::class,
        //     'authMethods' => [
        //         HttpBearerAuth::class,
        //         QueryParamAuth::class,
        //         JwtHttpBearerAuth::class
        //     ]
        // ];
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
        return ['status' => 'ok', 'status' => 'working'];
    }

    public function actionGenerateExcelFileTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Name');

        $fileName = yii::$app->getSecurity()->generateRandomString(10);
        $writer = new Xlsx($spreadsheet);
        HelperFunction::createFolderIfNotExist(Url::to('@app/web/excelFiles/pharmaceuticalForms'));

        $writer->save("excelFiles/pharmaceuticalForms" . $fileName . ".xlsx");
        $this->response->sendFile("../web/excelFiles/pharmaceuticalForms" . $fileName . ".xlsx", "$fileName.xlsx");
    }

    public function actionImportExcelFile()
    {
        try {
            if (isset($_FILES['sheet'])) {
                $file = $_FILES['sheet'];
                $tmpName = yii::$app->security->generateRandomString();
                $inputFile = 'excelFiles/pharmaceuticalForms' . $tmpName . '.xlsx';
                move_uploaded_file($file['tmp_name'], $inputFile);
                $spreadSheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFile);
                $pharmaceuticalFormArray = $spreadSheet->getActiveSheet()->toArray();
                //To remove the first row in file
                $tmpExcelFields = array_splice($pharmaceuticalFormArray, 0, 1);
                //This condition to check the template
                if (
                    $tmpExcelFields[0][0] != 'Name'
                ) {
                    return ['status' => 'error', 'details' => 'This excel file is not a validate file'];
                }
                $i = 0;
                $errorArr = [];
                foreach ($pharmaceuticalFormArray as $p) {
                    $i++;
                    $isExist = PharmaceuticalForm::findOne(['name' => trim($p[0])]);
                    if ($isExist === null) {
                        $newPharmaceuticalForm = new PharmaceuticalForm();
                        if (trim($p[0]) === '') {
                            array_push($errorArr, ['error' => "<b>$p[0]</b> Pharmaceutical Form should not be empty"]);
                            continue;
                        }
                        $newPharmaceuticalForm->name = trim($p[0]);
                        if ($newPharmaceuticalForm->validate()) {
                            $newPharmaceuticalForm->save();
                        } else {
                            array_push($errorArr, ['error' => "<b>$p[0]</b> ", 'details' => $newPharmaceuticalForm->getErrors()]);
                        }
                    } else {
                        array_push($errorArr, ['error' => "<b>$p[0]</b> Pharmaceutical Form already exist"]);
                    }
                }
                return ['status' => 'ok', 'errorDetails' => $errorArr];
            } else {
                return ['status' => 'error', 'details' => 'There is no file uploaded'];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'details' => $e->getMessage()];
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
            return ['status' => 'error', 'details' => $e->getMessage()];
        }
    }

    public function actionGetAll()
    {
        $pharmaceuticalForms = PharmaceuticalForm::find()->where([])->with('medicines')->asArray()->all();
        if (!$pharmaceuticalForms)
            return ["status" => "error", "details" => "There is no Pharmaceutical Form"];

        $pharmaceuticalForms = array_map(function ($p) {
            $p['medicines'] = array_map(function ($m) {
                $imgs = explode(',', $m['imgs']);
                $images = [];
                if ($imgs !== false) {
                    foreach ($imgs as $i) {
                        if ($i)
                            $images[] = Url::to('@web/medicines/images/' . $i, true);
                    }
                }
                $m['imgs'] = $images;
                $m['barcode']= Url::to('@web/medicines/barcodes/' . $m['barcode'], true);

                return $m;
            }, $p['medicines']);
            return $p;
        }, $pharmaceuticalForms);

        return ['status' => 'ok', 'pharmaceuticalForm' => $pharmaceuticalForms];
    }

    function actionGet($id)
    {
        $pharmaceuticalForm = PharmaceuticalForm::find()->with('medicines')->where(['id' => (int)$id])->asArray()->one();

        if ($pharmaceuticalForm === null)
            return ["status" => "error", "details" => "There is no Pharmaceutical Form"];

        $pharmaceuticalForm['medicines'] = array_map(function ($m) {
            $imgs = explode(',', $m['imgs']);
            $images = [];
            if ($imgs !== false) {
                foreach ($imgs as $i) {
                    if ($i)
                        $images[] = Url::to('@web/medicines/images/' . $i, true);
                }
            }
            $m['imgs'] = $images;
            $m['barcode']= Url::to('@web/medicines/barcodes/' . $m['barcode'], true);

            return $m;
        }, $pharmaceuticalForm['medicines']);


        return ['status' => 'ok', 'pharmaceuticalForm' => $pharmaceuticalForm];
    }


    function actionGetMedicines($id)
    {
        try {
            $pharmaceuticalForm = PharmaceuticalForm::find()->with('medicines')->where(['id' => (int)$id])->asArray()->one();

            if ($pharmaceuticalForm === null)
                return ["status" => "error", "details" => "There is no Pharmaceutical Form"];

            if (empty($pharmaceuticalForm['medicines']))
                return ['status' => 'error', 'details' => "There are no medicine that has this Pharmaceutical Form id ($id)"];

            $pharmaceuticalForm['medicines'] = array_map(function ($m) {
                $imgs = explode(',', $m['imgs']);
                $images = [];
                if ($imgs !== false) {
                    foreach ($imgs as $i) {
                        if ($i)
                            $images[] = Url::to('@web/medicines/images/' . $i, true);
                    }
                }
                $m['imgs'] = $images;
                $m['barcode']= Url::to('@web/medicines/barcodes/' . $m['barcode'], true);

                return $m;
            }, $pharmaceuticalForm['medicines']);

            return ['status' => 'ok', 'medicines' => $pharmaceuticalForm['medicines']];
        } catch (\Exception $e) {
            return ['status' => 'error', 'details' => $e->getMessage()];
        }
    }

    public  function actionDelete()
    {
        try {
            $data = (array)json_decode(Yii::$app->request->getRawBody(), true);
            if (!isset($data['id']))
                return ["status" => "error", "details" => "There are missing param"];

            $pharmaceuticalForm = PharmaceuticalForm::findOne(['id' => (int)$data['id']]);
            if ($pharmaceuticalForm === null)
                return ["status" => "error", "details" => "There is no Pharmaceutical Form that has this id "];

            if (MedicinePharmaceuticalForm::findOne(['pharmaceuticalFormId' => (int)$data['id']]) !== null)
                return ["status" => "error", "details" => "This Pharmaceutical Form has medicines belongs to it"];
           
                if (!$pharmaceuticalForm->delete())
                return ["status" => "error", "details" => $pharmaceuticalForm->getErrors()];

            return ['status' => 'ok'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'details' => $e->getMessage()];
        }
    }
}
