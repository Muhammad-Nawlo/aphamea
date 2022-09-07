<?php

namespace app\controllers;

use Yii;
use yii\helpers\Url;
use yii\filters\Cors;
use yii\web\Response;
use yii\db\Expression;
use app\models\Category;
use app\models\Medicine;
use yii\web\UploadedFile;
use app\helpers\HelperFunction;
use app\models\MedicineCategory;
use sizeg\jwt\JwtHttpBearerAuth;
use app\models\PharmaceuticalForm;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use app\models\MedicinePharmaceuticalForm;

class MedicineController extends \yii\web\Controller
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
        return ['status' => 'ok', 'status' => 'working'];
    }
    public function actionGenerateExcelFileTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Barcode');
        $sheet->setCellValue('B1', 'Product Name');
        $sheet->setCellValue('C1', 'Indications');
        $sheet->setCellValue('D1', 'Packing');
        $sheet->setCellValue('E1', 'Composition');
        $sheet->setCellValue('F1', 'Expired Date');
        $sheet->setCellValue('G1', 'Price');
        $sheet->setCellValue('H1', 'Net Price');
        $sheet->setCellValue('I1', 'Category');
        $sheet->setCellValue('J1', 'Pharmaceutical Form');

        $fileName = yii::$app->getSecurity()->generateRandomString(10);
        $writer = new Xlsx($spreadsheet);
        HelperFunction::createFolderIfNotExist(Url::to('@app/web/excelFiles/medicines'));


        $writer->save("excelFiles/medicines" . $fileName . ".xlsx");
        $this->response->sendFile("../web/excelFiles/medicines" . $fileName . ".xlsx", "$fileName.xlsx");
    }
    public function actionImportExcelFile()
    {
        try {
            if (isset($_FILES['sheet'])) {
                $file = $_FILES['sheet'];
                $tmpName = yii::$app->security->generateRandomString();
                $inputFile = 'excelFiles/' . $tmpName . '.xlsx';
                move_uploaded_file($file['tmp_name'], $inputFile);
                $spreadSheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFile);
                $medicineArray = $spreadSheet->getActiveSheet()->toArray();
                //To remove the first row in file
                $tmpExcelFields = array_splice($medicineArray, 0, 1);
                //This condition to check the template
                if (
                    $tmpExcelFields[0][0] != 'Barcode' ||
                    $tmpExcelFields[0][1] != 'Product Name' ||
                    $tmpExcelFields[0][2] != 'Indications' ||
                    $tmpExcelFields[0][3] != 'Packing' ||
                    $tmpExcelFields[0][4] != 'Composition' ||
                    $tmpExcelFields[0][5] != 'Expired Date' ||
                    $tmpExcelFields[0][6] != 'Price' ||
                    $tmpExcelFields[0][7] != 'Net Price' ||
                    $tmpExcelFields[0][8] != 'Category' ||
                    $tmpExcelFields[0][9] != 'Pharmaceutical Form'
                ) {
                    return ['status' => 'error', 'details' => 'This excel file is not a validate file'];
                }
                $i = 0;
                $errorArr = [];
                foreach ($medicineArray as $m) {
                    if (
                        empty($m[0]) ||
                        empty($m[1]) ||
                        empty($m[2]) ||
                        empty($m[3]) ||
                        empty($m[4]) ||
                        empty($m[5]) ||
                        empty($m[6]) ||
                        empty($m[7]) ||
                        empty($m[8])||
                        empty($m[9])
                    ) {
                        array_push($errorArr, ['error' => "There are missing data in this row ($i)"]);
                    }
                    $i++;
                    $isExsist = Medicine::findOne(['productName' => trim($m[1])]);
                    if ($isExsist !== null) {
                        array_push($errorArr, ['error' => "<b>$m[1]</b> Medicine already exist"]);
                        continue;
                    }
                    $newMedicine = new Medicine();
                    if (trim($m[1]) === '') {
                        array_push($errorArr, ['error' => "<b>$m[1]</b> Medicine name should not be empty"]);
                        continue;
                    }
                    $category = Category::findOne(['name' => trim($m[8])]);
                    $pharmaceuticalForm = PharmaceuticalForm::findOne(['name' => trim($m[9])]);

                    if ($category === null) {
                        $category = new Category();
                        if (trim($m[8]) === '') {
                            array_push($errorArr, ['error' => "<b>$m[1]</b> does not have a category"]);
                            continue;
                        }
                        $category->name =  trim($m[8]);
                        $category->save();
                    }

                    if ($pharmaceuticalForm === null) {
                        if (trim($m[9]) === '') {
                            array_push($errorArr, ['error' => "<b>$m[1]</b> does not have a pharmaceutical Form"]);
                            continue;
                        }
                        $pharmaceuticalForm = new PharmaceuticalForm();
                        $pharmaceuticalForm->name =  trim($m[9]);
                        $pharmaceuticalForm->save();
                    }

                    $newMedicine->barcode = trim($m[0]);
                    $newMedicine->productName = trim($m[1]);
                    $newMedicine->indications = trim($m[2]);
                    $newMedicine->packing = trim($m[3]);
                    $newMedicine->composition = trim($m[4]);
                    $newMedicine->expiredDate = trim($m[5]);
                    $newMedicine->price = (float)$m[6];
                    $newMedicine->netPrice = (float) $m[7];
                    if ($newMedicine->validate()) {
                        $newMedicine->save();

                        $pm = new MedicinePharmaceuticalForm();
                        $pm->medicineId = $newMedicine->id;
                        $pm->pharmaceuticalFormId = $pharmaceuticalForm->id;

                        $pm->save();

                        $c = new MedicineCategory();
                        $c->medicineId = $newMedicine->id;
                        $c->categoryId = $category->id;
                        $c->save();
                    } else {
                        array_push($errorArr, ['error' => "<b>$m[1]</b> ", 'details' => $newMedicine->getErrors()]);
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

    // public function actionReadMedicines()
    // {
    //     $spreadSheet = IOFactory::load(Url::to('@app/web/aphamea.xlsx'));
    //     $spreadSheetArray = $spreadSheet->getActiveSheet()->toArray();
    //     array_splice($spreadSheetArray, 0, 1);
    //     foreach ($spreadSheetArray as $m) {
    //         $newMedicine = new Medicine();
    //         $newMedicine->productName = $m[1];
    //         $newMedicine->indications = $m[5];
    //         $newMedicine->packing = $m[6];
    //         $newMedicine->composition = $m[4];
    //         if ($newMedicine->validate()) {
    //             $newMedicine->save();

    //             $pharmaceuticalForm = PharmaceuticalForm::findOne(['name' => $m[2]]);
    //             if ($pharmaceuticalForm === null) continue;

    //             $pm = new MedicinePharmaceuticalForm();
    //             $pm->medicineId = $newMedicine->id;
    //             $pm->pharmaceuticalFormId = $pharmaceuticalForm->id;
    //             $pm->save();


    //             $category = Category::findOne(['name' => $m[3]]);
    //             if ($category === null) continue;
    //             $cm = new MedicineCategory();
    //             $cm->medicineId = $newMedicine->id;
    //             $cm->categoryId = $pharmaceuticalForm->id;
    //             $cm->save();
    //         }
    //     }
    // }

    public function actionAdd()
    {
        $data = (array)(Yii::$app->request->post());
        if (
            !isset($data['productName']) ||
            !isset($data['indications']) ||
            !isset($data['packing']) ||
            !isset($data['composition']) ||
            !isset($data['price']) ||
            !isset($data['netPrice']) ||
            !isset($data['pharmaceuticalFormId']) ||
            !isset($data['categoryId'])
        ) {
            return ['status' => 'error', 'details' => 'There are missing params'];
        }
        try {
            $pharmaceuticalForm = PharmaceuticalForm::findOne(['id' => (int)$data['pharmaceuticalFormId']]);
            $category = Category::findOne(['id' => $data['categoryId']]);
            if ($pharmaceuticalForm === null) {

                return ['status' => 'error', 'details' => 'Pharmaceutical Form Id is not valid'];
            }
            if ($category === null) {
                return ['status' => 'error', 'details' => 'Category Id is not valid'];
            }

            $newMedicine = new Medicine();
            $newMedicine->load($data, '');
            $medicineImages = UploadedFile::getInstancesByName('medicineImages');
            if (!empty($medicineImages)) {
                HelperFunction::createFolderIfNotExist('@app/web/medicines/images');
                $imagesName = [];
                foreach ($medicineImages as $img) {
                    $name = Yii::$app->security->generateRandomString(5) . '.' . $img->extension;
                    $img->saveAs(Url::to('@app/web/medicines/images') . '/' . $name);
                    $imagesName[] = $name;
                }
                $newMedicine->imgs = implode(',', $imagesName);
            }
            if ($newMedicine->validate()) {
                $newMedicine->save();

                $pm = new MedicinePharmaceuticalForm();
                $pm->medicineId = $newMedicine->id;
                $pm->pharmaceuticalFormId = $pharmaceuticalForm->id;
                if ($pm->validate()) {
                    $pm->save();
                } else {
                    return ['status' => 'error', 'details' => $pm->getErrors()];
                }
                $c = new MedicineCategory();
                $c->medicineId = $newMedicine->id;
                $c->categoryId = $category->id;
                if ($c->validate()) {
                    $c->save();
                } else {
                    return ['status' => 'error', 'details' => $c->getErrors()];
                }
                return ['status' => 'ok'];
            } else {
                return ['status' => 'error', 'details' => $newMedicine->getErrors()];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'details' => $e->getMessage()];
        }
    }

    public function actionUpdate()
    {
        $data = (array)(Yii::$app->request->post());
        if (
            !isset($data['productName']) ||
            !isset($data['indications']) ||
            !isset($data['packing']) ||
            !isset($data['composition']) ||
            !isset($data['price']) ||
            !isset($data['netPrice']) ||
            !isset($data['pharmaceuticalFormId']) ||
            !isset($data['categoryId']) ||
            !isset($data['id'])
        ) {
            return ['status' => 'error', 'details' => 'There are missing params'];
        }

        try {
            $pharmaceuticalForm = PharmaceuticalForm::findOne(['id' => (int)$data['pharmaceuticalFormId']]);
            $category = Category::findOne(['id' => $data['categoryId']]);
            if ($pharmaceuticalForm === null) {
                return ['status' => 'error', 'details' => 'Pharmaceutical Form Id is not valid'];
            }
            if ($category === null) {
                return ['status' => 'error', 'details' => 'Category Id is not valid'];
            }

            $medicine = Medicine::findOne(['id' => (int)$data['id']]);
            if ($medicine === null)
                return ['status' => 'error', 'details' => "There is no medicine that has this id"];

            $medicine->load($data, '');
            $medicineImages = UploadedFile::getInstancesByName('medicineImages');
            if (!empty($medicineImages)) {
                HelperFunction::createFolderIfNotExist('@app/web/medicines/images');
                HelperFunction::deletePhotos($medicine->imgs, 'medicines');
                $imagesName = [];
                foreach ($medicineImages as $img) {
                    $name = Yii::$app->security->generateRandomString(5) . '.' . $img->extension;
                    $img->saveAs(Url::to('@app/web/medicines/images') . '/' . $name);
                    $imagesName[] = $name;
                }
                $medicine->imgs = implode(',', $imagesName);
            }
            if ($medicine->validate()) {
                $medicine->save();
                MedicinePharmaceuticalForm::findOne(['medicineId' => $medicine->id])->delete();
                MedicineCategory::findOne(['medicineId' => $medicine->id])->delete();

                $pm = new MedicinePharmaceuticalForm();
                $pm->medicineId = $medicine->id;
                $pm->pharmaceuticalFormId = $pharmaceuticalForm->id;
                if ($pm->validate()) {
                    $pm->save();
                } else {
                    return ['status' => 'error', 'details' => $pm->getErrors()];
                }
                $c = new MedicineCategory();
                $c->medicineId = $medicine->id;
                $c->categoryId = $category->id;
                if ($c->validate()) {
                    $c->save();
                } else {
                    return ['status' => 'error', 'details' => $c->getErrors()];
                }
                return ['status' => 'ok'];
            } else {
                return ['status' => 'error', 'details' => $medicine->getErrors()];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'details' => $e->getMessage()];
        }
    }

    public function actionGet($id = null, $barcode = null)
    {
        try {
            if (($id === null && $barcode === null) || ($id !== null && $barcode !== null)) {
                return ['status' => 'error', 'details' => 'You should send either id or barcode params'];
            }
            if ($id != null) {
                $medicine = Medicine::find()
                    ->where(['id' => (int)$id])
                    ->with('categories', 'pharmaceuticalForms')
                    ->asArray()
                    ->one();
            } elseif ($barcode != null) {
                $medicine = Medicine::find()
                    ->where(['barcode' => (int)$barcode])
                    ->with('categories', 'pharmaceuticalForms')
                    ->asArray()
                    ->one();
            }
            if ($medicine === null)
                return ['status' => 'error', 'details' => "There is no medicine that has this id ($id) or this barcode ($barcode)"];


            $imgs = explode(',', $medicine['imgs']);
            $images = [];
            if ($imgs !== false) {
                foreach ($imgs as $i) {
                    if ($i)
                        $images[] = Url::to('@web/medicines/images/' . $i, true);
                }
            }
            $medicine['imgs'] = $images;

            return ['status' => 'ok', 'medicine' => $medicine];
        } catch (\Exception $e) {
            return ['status' => 'error', 'details' => $e->getMessage()];
        }
    }

    public function actionGetAll($searchText = null)
    {
        if ($searchText != null) {
            $medicines = Medicine::find()
                ->where(['like', 'productName', trim($searchText) . '%', false])
                ->with('categories', 'pharmaceuticalForms')
                ->asArray()->all();
        } else {
            $medicines = Medicine::find()
                ->with('categories', 'pharmaceuticalForms')
                ->asArray()->all();
        }
        if ($medicines) {
            $medicines = array_map(function ($m) {
                $imgs = explode(',', $m['imgs']);
                $images = [];
                if (!empty($imgs)) {
                    foreach ($imgs as $i) {
                        if ($i)
                            $images[] = Url::to('@web/medicines/images/' . $i, true);
                    }
                }
                $m['imgs'] = $images;
                return $m;
            }, $medicines);
            return ['status' => 'ok', 'medicines' => $medicines];
        } else {
            return ['status' => 'error', 'details' => 'There is no medicine yet'];
        }
    }
}
