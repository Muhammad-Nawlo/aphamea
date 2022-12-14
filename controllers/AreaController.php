<?php

namespace app\controllers;

use app\models\City;
use app\models\Country;
use app\models\Region;
use sizeg\jwt\JwtHttpBearerAuth;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\Cors;
use yii\web\Controller;
use PhpOffice\PhpSpreadsheet\IOFactory;
use yii\helpers\Url;

//read the cities of a country
//read the countries
//read the regions of a city

class AreaController extends Controller
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
            'except' => ['get-countries', 'get-cities', 'get-regions','read-countries','read-cities','read-regions'],
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

       public function actionReadCountries()
       {
           $jsonFile = file_get_contents(Url::to('@app/web/countries.json'));
           $jsonFile = json_decode($jsonFile, true);
           foreach ($jsonFile as $c) {
               $newCountry = new Country();
               $newCountry->nameAr = $c['nameAr'];
               $newCountry->nameEn = $c['nameEn'];
               $newCountry->save();
           }
           return ['status' => 'ok'];
       }

    public function actionReadCities()
    {
        try {
            $spreadSheet = IOFactory::load(Url::to('@app/web/cities.xlsx'));
            $spreadSheetArray = $spreadSheet->getActiveSheet()->toArray();
            array_splice($spreadSheetArray, 0, 1);
            foreach ($spreadSheetArray as $city) {
                $newCity = new City();
                $newCity->nameEn = $city[0];
                $newCity->nameAr = $city[1];
                $newCity->countryId = 1;
                $newCity->save();
            }
            return ['status' => 'ok'];
        } catch (\Exception $e) {
            return ['status' => 'error', $e->getMessage()];
        }
    }

       public function actionReadRegions()
       {
           $spreadSheet = IOFactory::load(Url::to('@app/web/regions.xlsx'));
           $spreadSheetArray = $spreadSheet->getActiveSheet()->toArray();
           foreach ($spreadSheetArray as $region) {
               $newRegions = new Region();
               $newRegions->regionEn = $region[0];
               $newRegions->regionAr = $region[1];
               $newRegions->cityId = 2;
               $newRegions->save();
           }
           return ['status' => 'ok'];
       }


    public function actionGetCountries()
    {
        // Syria only
        $countries = Country::find()->where(['id' => 1])->asArray()->one();
        return [
            'status' => 'ok',
            'countries' => $countries
        ];
    }

    public function actionGetCities()
    {
        // cities of syria
        $cities = City::find()->where(['countryId' => 1])->all();
        return [
            'status' => 'ok',
            'cities' => $cities
        ];
    }

    public function actionGetRegions($cityId)
    {
        $regions = Region::find()->where(['cityId' => (int)$cityId])->asArray()->all();
        return [
            'status' => 'ok',
            'regions' => $regions
        ];
    }
}
