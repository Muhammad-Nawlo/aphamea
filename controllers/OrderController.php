<?php

namespace app\controllers;

use app\models\Offer;
use app\models\OfferDetails;
use app\models\Order;
use app\models\OrderDetails;
use sizeg\jwt\JwtHttpBearerAuth;
use Yii;
use yii\db\Query;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\Cors;
use yii\helpers\Url;
use yii\web\Controller;

class OrderController extends Controller
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

    function actionAdd()
    {
        try {
            $errors = [];
            $data = (array)json_decode(Yii::$app->request->getRawBody());
            if (!isset($data['userId']) || !isset($data['representativeId']) || !isset($data['orderDetails']))
                return ['status' => 'error', 'details' => 'There are missing params (userId or representativeId or orderDetails)'];

            $orderDetails = $data['orderDetails'];
            if (empty($orderDetails))
                return ['status' => 'error', 'details' => 'Order details should not be empty'];

            if ((int)$data['representativeId'] === (int)$data['userId'])
                return ['status' => 'error', 'details' => 'User id should not be the same as Representative id'];

            $newOrder = new Order();
            $newOrder->userId = (int)$data['userId'];
            $newOrder->representativeId = (int)$data['representativeId'];
            $newOrder->orderDate = date('Y-m-d H:i:s');
            $newOrder->isCanceled = 0;
            $newOrder->isCompleted = 0;
            if ($newOrder->validate()) {
                $newOrder->save();
                foreach ($orderDetails as $o) {
                    $o = (array)$o;
                    $newOrderDetails = new OrderDetails();
                    $newOrderDetails->orderId = $newOrder->id;
                    $newOrderDetails->offerId = (int)$o['offerId'];
                    $newOrderDetails->quantity = (int)$o['quantity'];
                    if ($newOrderDetails->validate()) {
                        $newOrderDetails->save();
                    } else {
                        $errors[] = $newOrderDetails->getErrors();
                    }
                }
                return ['status' => 'ok', 'errors' => $errors];
            } else {
                return ['status' => 'error', 'details' => $newOrder->getErrors()];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'details' => $e->getMessage()];
        }
    }

    function actionCompleted($id)
    {
        try {
            $order = Order::findOne(['id' => (int)$id]);
            if ($order === null)
                return ['status' => 'error', 'details' => 'There is no order that has this id'];

            if ($order->isCompleted === 1)
                return ['status' => 'error', 'details' => 'The order is already completed'];

            $order->isCompleted = 1;
            if ($order->validate() && $order->save()) {
                return ['status' => 'ok'];
            } else {
                return ['status' => 'error', 'details' => $order->getErrors()];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'details' => $e->getMessage()];
        }
    }

    function actionCanceled($id)
    {
        try {
            $order = Order::findOne(['id' => (int)$id]);
            if ($order === null)
                return ['status' => 'error', 'details' => 'There is no order that has this id'];

            if ($order->isCanceled === 1)
                return ['status' => 'error', 'details' => 'The order is already canceled'];

            $order->isCanceled = 1;
            if ($order->validate() && $order->save()) {
                return ['status' => 'ok'];
            } else {
                return ['status' => 'error', 'details' => $order->getErrors()];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'details' => $e->getMessage()];
        }
    }

    function actionGetAll()
    {
        try {
            $orders = Order::find()->with('representative', 'user')->asArray()->all();
            if ($orders) {
                return ['status' => 'ok', 'orders' => $orders];
            } else {
                return ['status' => 'error', 'details' => 'There is no order'];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'details' => $e->getMessage()];
        }
    }

    function actionGet($id)
    {
        try {
            $order = Order::find()->where(['id' => (int)$id])->with('offers', 'orderDetails')->asArray()->one();

            if ($order === null)
                return ['status' => 'error', 'details' => 'There is no order that has this id'];

            $offers = [];

            foreach ($order['offers'] as $key => $offer) {
                $offer  = Offer::find()->where(['id' => $offer['id']])
                    ->with('medicines', 'extraMedicines')
                    ->asArray()
                    ->one();
                $offer['quantity'] = $order['orderDetails'][$key]['quantity'];
                $offers[] = $offer;
            }
            $order['offers'] = $offers;
            unset($order['orderDetails']);

            return ['status' => 'ok', 'order' => $order];
        } catch (\Exception $e) {
            return ['status' => 'error', 'details' => $e->getMessage()];
        }
    }
}
