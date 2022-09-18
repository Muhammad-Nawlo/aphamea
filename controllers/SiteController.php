<?php

namespace app\controllers;

use Yii;
use app\models\User;
use yii\helpers\Url;
use app\models\Order;
use yii\filters\Cors;
use yii\web\Response;
use app\models\Contact;
use yii\web\Controller;
use yii\web\UploadedFile;
use app\models\CompanyTeam;
use yii\helpers\ArrayHelper;
use app\helpers\HelperFunction;
use sizeg\jwt\JwtHttpBearerAuth;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SiteController extends Controller
{
    public $enableCsrfValidation = false;

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
            'except' => ['login', 'signup', 'index', 'save-user-info'],
            'authMethods' => [
                HttpBearerAuth::class,
                QueryParamAuth::class,
                JwtHttpBearerAuth::class
            ]
        ];
        return $behaviors;
    }

    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return ['status' => 'ok', 'status' => 'It\'s working'];
    }

    public function actionLogin()
    {
        $data = (array)json_decode(Yii::$app->request->getRawBody(), true);
        if (!isset($data['email']) || !isset($data['password'])) {
            return ['status' => 'error', 'details' => 'There are missing params'];
        }
        $isValidRequest = HelperFunction::checkEmptyData([$data['email'], $data['password']]);
        if ($isValidRequest) return $isValidRequest;

        $user = User::find()
            ->where(['email' => $data['email']])
            ->with('contacts', 'region', 'city', 'country')
            ->asArray()
            ->one();

        if ($user === null) return ['status' => 'error', 'details' => 'Email is not exist or wrong'];

        if (!Yii::$app->security->validatePassword($data['password'], $user['password']))
            return ['status' => 'error', 'details' => 'Password is not exist'];

        $accessToken = Yii::$app->jwt->getBuilder()
            ->setIssuer('http://localhost')
            ->setAudience('http://localhost')
            ->setId((string)$user['id'], true)
            ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
            ->setNotBefore(time() + 60)
            ->setExpiration(time() + 99999999) // Configures the expiration time of the token (exp claim)
            ->set('uid', (string)$user['id']) // Configures a new claim, called "uid"
            ->getToken(); // Retrieves the generated token
        User::updateAll(['accessToken' => (string)$accessToken], ['id' => $user['id']]);

        $user['accessToken'] = (string)$accessToken;
        if ($user['img'])
            $user['img'] = Url::to('@web/users/images/' . $user['img'], true);

        return [
            'status' => 'ok',
            'userInfo' => $user
        ];
    }

    public function actionSignup()
    {
        try {
            $data = (array)json_decode(Yii::$app->request->getRawBody());
            $email = ArrayHelper::getValue($data, 'email', '');
            $password = ArrayHelper::getValue($data, 'password', '');
            $firstName = ArrayHelper::getValue($data, 'firstName', '');
            $lastName = ArrayHelper::getValue($data, 'lastName', '');
            $isDataValid = HelperFunction::checkEmptyData([$email, $password, $firstName, $lastName]);
            if ($isDataValid) {
                return $isDataValid;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['status' => 'error', 'details' => 'Your email is not valid'];
            }
            if ((strlen($firstName) < 2 && strlen($firstName) > 20) ||
                (strlen($lastName) < 2 && strlen($lastName) > 20)
            ) {
                return ['status' => 'error', 'details' => 'First name and last name should be more than 2 and less than 20 charachter'];
            }
            if (strlen($password) < 8 || strlen($password) > 20) {
                return ['status' => 'error', 'details' => 'Your password should be between 8 and 20 charachter'];
            }

            $newUser = new User();
            $newUser->email = trim($email);
            $newUser->firstName = trim($firstName);
            $newUser->lastName = trim($lastName);
            $newUser->password = Yii::$app->security->generatePasswordHash(trim($password));
            $newUser->createdAt = date('Y-m-d');


            if ($newUser->validate()) {
                $newUser->save();
                return ['status' => 'ok'];
            } else {
                return ['status' => 'error', 'details' => $newUser->getErrors()];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'details' => $e->getMessage()];
        }
    }

    public function actionSaveUserInfo()
    {
        $errors = [];
        try {
            $data = (array)Yii::$app->request->post();
            if (!isset($data['email']))
                return ["status" => "error", "details" => "There is missing param"];

            $user = User::findOne(['email' => $data['email']]);
            if ($user === null) return ["status" => "error", "details" => "There is no user that has this email"];

            $user->load($data, '');
            $userContacts = isset($data['userContacts']) ? (array)$data['userContacts'] : [];
            $userImage = UploadedFile::getInstanceByName('userImage');
            if ($userImage !== null) {
                HelperFunction::createFolderIfNotExist(Url::to('@app/web/users/images'));
                HelperFunction::deletePhotos($user->img, 'users/images');
                $name = Yii::$app->security->generateRandomString(5) . '.' . $userImage->extension;
                $userImage->saveAs(Url::to('@app/web/users/images') . '/' . $name);
                $user->img = $name;
            }
            if (!in_array((int)$data['role'], User::ROLE)) {
                return ["status" => "error", "details" => "The role is not valid"];
            }

            if ($userContacts) {
                Contact::deleteAll(['userId' => $user->id]);
                foreach ($userContacts as $c) {
                    if (
                        (!isset($c['type']) && !in_array((int)$c['type'], Contact::CONTACT_TYPES)) ||
                        !isset($c['content'])
                    ) {
                        $errors[] = 'The content of contact is invalid';
                    }
                    $newContact = new Contact();
                    $newContact->userId = $user->id;
                    $newContact->type = (int)$c['type'];
                    $newContact->content = $c['content'];
                    if ($newContact->validate()) {
                        $newContact->save();
                    } else {
                        $errors[] = $newContact->getErrors();
                    }
                }
            }

            if ($user->validate()) {
                $user->save();
                $user->toArray();
                if ($user['img'])
                    $user['img'] = Url::to('@web/users/images/' . $user['img'], true);

                return ['status' => 'ok', 'user' => $user, 'errors' => $errors];
            } else {
                return ['status' => 'error', 'details' => $user->getErrors()];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'details' => $e->getMessage()];
        }
    }

    public function actionUpdateUserInfo()
    {
        $errors = [];
        try {
            $data = (array)Yii::$app->request->post();
            if (!isset($data['id']))
                return ["status" => "error", "details" => "There is missing param"];

            $user = User::findOne(['id' => $data['id']]);
            if ($user === null) return ["status" => "error", "details" => "There is no user that has this email"];

            $user->load($data, '');
            $userContacts = isset($data['userContacts']) ? (array)$data['userContacts'] : [];
            $userImage = UploadedFile::getInstanceByName('userImage');
            if ($userImage !== null) {
                HelperFunction::createFolderIfNotExist(Url::to('@app/web/users/images'));
                HelperFunction::deletePhotos($user->img, 'users/images');
                $name = Yii::$app->security->generateRandomString(5) . '.' . $userImage->extension;
                $userImage->saveAs(Url::to('@app/web/users/images') . '/' . $name);
                $user->img = $name;
            }
            if (!in_array((int)$data['role'], User::ROLE)) {
                return ["status" => "error", "details" => "The role is not valid"];
            }

            if ($userContacts) {
                Contact::deleteAll(['userId' => $user->id]);
                foreach ($userContacts as $c) {
                    if (
                        (!isset($c['type']) && !in_array((int)$c['type'], Contact::CONTACT_TYPES)) ||
                        !isset($c['content'])
                    ) {
                        $errors[] = 'The content of contact is invalid';
                    }
                    $newContact = new Contact();
                    $newContact->userId = $user->id;
                    $newContact->type = (int)$c['type'];
                    $newContact->content = $c['content'];
                    if ($newContact->validate()) {
                        $newContact->save();
                    } else {
                        $errors[] = $newContact->getErrors();
                    }
                }
            }

            if ($user->validate()) {
                $user->save();
                $user->toArray();
                if ($user['img'])
                    $user['img'] = Url::to('@web/users/images/' . $user['img'], true);
                return ['status' => 'ok', 'user' => $user, 'errors' => $errors];
            } else {
                return ['status' => 'error', 'details' => $user->getErrors()];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'details' => $e->getMessage()];
        }
    }

    public function actionGetUserInfo()
    {
        $user = User::find()
            ->where(['id' => Yii::$app->user->identity->id])
            ->with('contacts', 'region', 'city', 'country')
            ->asArray()
            ->one();
        if ($user === null)
            return ['status' => 'error', 'details' => 'There is no user that has this id'];

        if ($user['img'])
            $user['img'] = Url::to('@web/users/images/' . $user['img'], true);

        return [
            'status' => 'ok',
            'userInfo' => $user
        ];
    }

    public function actionGetAllUsers($year = null, $month = null)
    {
        $users = User::find();
        if ($year != null && $month != null) {
            $month = str_pad($month, 2, '0', STR_PAD_LEFT);
            $endDayMonth = date('t', strtotime("$year-$month-01"));
            $users->andFilterWhere(['between', 'createdAt', date('Y-m-d', strtotime("$year-$month-01")), date('Y-m-d', strtotime("$year-$month-$endDayMonth"))]);
        }
        $users = $users
            ->with('contacts', 'region', 'city', 'country')
            ->asArray()->all();

        if ($users) {
            $users = array_map(function ($u) {
                if ($u['img'])
                    $u['img'] = Url::to('@web/users/images/' . $u['img'], true);
                return $u;
            }, $users);
            return ['status' => 'ok', 'users' => $users];
        } else {
            return ['status' => 'error', 'details' => 'There is no user'];
        }
    }

    public function actionExportToExcelFile()
    {
        $headStyle = [
            'font' => [
                'color' => [
                    'rgb' => 'ffffff'
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '538ED5']
            ]
        ];
        $data = User::find()->where([])->all();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Berlin Sans FB')->setSize(18);
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'First Name');
        $sheet->setCellValue('B1', 'Last name');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('D1', 'Role');

        $sheet->getStyle('A1:D1')->applyFromArray($headStyle);

        $sheet
            ->getStyle('A1:D1')
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getColumnDimension('A')->setWidth(16);
        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(16);

        $i = 2;
        foreach ($data as $userInfo) {
            $sheet->setCellValue("A$i", $userInfo->firstName);
            $sheet->setCellValue("B$i", $userInfo->lastName);
            $sheet->setCellValue("C$i", $userInfo->email);
            $sheet->setCellValue("D$i", $userInfo->role);
            $i++;
        }

        $fileName = date('Y-m-d');
        $writer = new Xlsx($spreadsheet);
        HelperFunction::createFolderIfNotExist(Url::to('@app/web/excelFiles/users'));
        $writer->save("excelFiles/users/" . "$fileName.xlsx");
        $this->response->sendFile(Url::to("@app/web/excelFiles/users/$fileName.xlsx"), "$fileName.xlsx");
    }

    public function actionGenerateExcelFileTemplate()
    {
        $headStyle = [
            'font' => [
                'color' => [
                    'rgb' => 'ffffff'
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '538ED5']
            ]
        ];
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Berlin Sans FB')->setSize(18);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'First Name');
        $sheet->setCellValue('B1', 'Last name');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('D1', 'Password');
        $sheet->setCellValue('E1', 'Role');
        $sheet->getStyle('A1:E1')->applyFromArray($headStyle);

        $sheet
            ->getStyle('A1:E1')
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getColumnDimension('A')->setWidth(16);
        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(16);

        $fileName =         $fileName = date('Y-m-d');
        $writer = new Xlsx($spreadsheet);
        HelperFunction::createFolderIfNotExist(Url::to('@app/web/excelFiles/users'));


        $writer->save("excelFiles/users" . $fileName . ".xlsx");
        $this->response->sendFile(Url::to("@app/web/excelFiles/users" . $fileName . ".xlsx"), "$fileName.xlsx");
    }

    public function actionImportExcelFile()
    {
        try {
            $ids = [];
            if (isset($_FILES['sheet'])) {
                $file = $_FILES['sheet'];
                $tmpName = yii::$app->security->generateRandomString();
                $inputFile = 'excelFiles/users/' . $tmpName . '.xlsx';
                move_uploaded_file($file['tmp_name'], $inputFile);
                $spreadSheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFile);
                $usersArray = $spreadSheet->getActiveSheet()->toArray();
                //To remove the first row in file
                $tmpExcelFields = array_splice($usersArray, 0, 1);
                //This condition to check the template
                if (
                    $tmpExcelFields[0][0] != 'First Name' ||
                    $tmpExcelFields[0][1] != 'Last name' ||
                    $tmpExcelFields[0][2] != 'Email' ||
                    $tmpExcelFields[0][3] != 'Password' ||
                    $tmpExcelFields[0][4] != 'Role'
                ) {
                    return ['status' => 'error', 'details' => 'This excel file is not a validate file'];
                }
                $i = 0;
                $errorArr = [];
                foreach ($usersArray as $user) {
                    $i++;
                    if (filter_var($user[2], FILTER_VALIDATE_EMAIL)) {
                        $isExist = User::findOne(['email' => $user[2]]);
                        if ($isExist === null) {
                            $newUser = new User();
                            $newUser->firstName = htmlspecialchars(stripslashes(trim($user[0])));
                            $newUser->lastName = htmlspecialchars(stripslashes(trim($user[1])));
                            $newUser->email = filter_var($user[2], FILTER_VALIDATE_EMAIL);
                            if (strlen(trim($user[3]) < 8 || strlen(trim($user[3])) > 20)) {
                                array_push($errorArr, ['error' => "<b>$user[0]</b>, Password should be between 8 and 20 charachter"]);
                                continue;
                            }
                            $newUser->password = Yii::$app->security->generatePasswordHash($user[3]);

                            if (!in_array((int)$user[4], User::ROLE)) {
                                array_push($errorArr, ['error' => "<b>$user[2]</b> has invalid role"]);
                                continue;
                            }
                            $newUser->role = $user[4];
                            $newUser->createdAt = date('Y-m-d');
                            if ($newUser->validate()) {
                                $newUser->save();
                                $ids[] = $newUser->id;
                            } else {
                                array_push($errorArr, ['error' => "<b>$user[2]</b> ", 'details' => $newUser->getErrors()]);
                            }
                        } else {
                            array_push($errorArr, ['error' => "<b>$user[2]</b> User already exist"]);
                        }
                    } else {
                        array_push($errorArr, ['error' => "<b>$user[2]</b> Please enter valid email address"]);
                    }
                }

                $newAddedUser =  User::find()->where(['in', 'id', $ids])->all();
                return ['status' => 'ok', 'newAddedUser' => $newAddedUser, 'errorDetails' => $errorArr];
            } else {
                return ['status' => 'error', 'details' => 'There is no file uploaded'];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'details' => $e->getMessage()];
        }
    }

    public  function actionDelete()
    {
        try {
            $errors = [];
            $data = (array)json_decode(Yii::$app->request->getRawBody(), true);
            if (!isset($data['ids'])) {
                return ["status" => "error", "details" => "There are missing param"];
            }
            $ids = (array)$data['ids'];
            if (empty($ids))
                return ["status" => "error", "details" => "The array of ids is empty"];

            foreach ($ids as $id) {
                $id = (int)$id;
                $user = User::findOne(['id' => $id]);
                if ($user === null) {
                    $errors[] = "There is no user that has this id " . $id;
                    continue;
                }
                if (CompanyTeam::findOne(['userId' => $id]) !== null) {
                    $errors[] = "This user that has this {$user->email} belongs to a company";
                    continue;
                }

                if (Order::findOne(['userId' => $id]) !== null) {
                    $errors[] = "This user that has this {$user->email} has an offer";
                }

                if (Order::findOne(['representativeId' => $id]) !== null) {
                    $errors[] = "This user that has this {$user->email} has an offer that is assigned to him";
                }


                Contact::deleteAll(['userId' => $id]);
                if (!$user->delete()) {
                    return ["status" => "error", "details" => $user->getErrors()];
                }
            }

            return ['status' => 'ok', 'errors' => $errors];
        } catch (\Exception $e) {
            return ['status' => 'error', 'details' => $e->getMessage()];
        }
    }
}
