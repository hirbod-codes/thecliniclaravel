<?php

namespace App\Http\Controllers\Orders;

use App\Auth\CheckAuthentication;
use App\Http\Controllers\Controller;
use App\Models\Auth\User as Authenticatable;
use App\Models\Order\Order;
use App\Models\Package\Package;
use App\Models\Part\Part;
use App\Models\User;
use Database\Interactions\Orders\Creation\DatabaseCreateDefaultRegularOrder;
use Database\Interactions\Orders\Creation\DatabaseCreateLaserOrder;
use Database\Interactions\Orders\Creation\DatabaseCreateRegularOrder;
use Database\Interactions\Orders\Retrieval\DatabaseRetrieveLaserOrders;
use Database\Interactions\Orders\Retrieval\DatabaseRetrieveRegularOrders;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use TheClinicDataStructures\DataStructures\Order\DSOrders;
use TheClinicDataStructures\DataStructures\Order\Laser\DSLaserOrder;
use TheClinicDataStructures\DataStructures\User\DSAdmin;
use TheClinicUseCases\Orders\Creation\LaserOrderCreation;
use TheClinicUseCases\Orders\Creation\RegularOrderCreation;
use TheClinicUseCases\Orders\Interfaces\IDataBaseCreateLaserOrder;
use TheClinicUseCases\Orders\Interfaces\IDataBaseCreateRegularOrder;
use TheClinicUseCases\Orders\Interfaces\IDataBaseCreateDefaultRegularOrder;
use TheClinicUseCases\Orders\Retrieval\LaserOrderRetrieval;
use TheClinicUseCases\Orders\Retrieval\RegularOrderRetrieval;
use TheClinicUseCases\Orders\Interfaces\IDataBaseRetrieveLaserOrders;
use TheClinicUseCases\Orders\Interfaces\IDataBaseRetrieveRegularOrders;

class OrdersController extends Controller
{
    private CheckAuthentication $checkAuthentication;

    private RegularOrderCreation $regularOrderCreation;

    private LaserOrderCreation $laserOrderCreation;

    private IDataBaseCreateRegularOrder $iDataBaseCreateRegularOrder;

    private IDataBaseCreateLaserOrder $iDataBaseCreateLaserOrder;

    private IDataBaseCreateDefaultRegularOrder $iDataBaseCreateDefaultRegularOrder;

    private RegularOrderRetrieval $regularOrderRetrieval;

    private LaserOrderRetrieval $laserOrderRetrieval;

    private IDataBaseRetrieveLaserOrders $iDataBaseRetrieveLaserOrders;

    private IDataBaseRetrieveRegularOrders $iDataBaseRetrieveRegularOrders;

    public function indexx()
    {
        /** @var User $user */
        $user = User::first();
        dd($user->orders[1]->laserOrder->getDSLaserOrder());

        $laserOrders = Order::query()
            ->where('user_id', '=', 1)
            ->whereHas('laserOrder', function ($query) {
                $query
                    ->where('price', '>', 10000000)
                    //
                ;
            })
            ->with(['laserOrder.parts', 'laserOrder.packages'])
            ->get()
            ->all()
            //
        ;

        $regularOrders = Order::query()
            ->where('user_id', '=', 1)
            ->whereHas('regularOrder', function ($query) {
                $query
                    ->where('price', '>', 10000000)
                    //
                ;
            })
            ->with(['regularOrder'])
            ->get()
            ->all()
            //
        ;


        dd(
            ['$laserOrders' => $laserOrders],
            ['$regularOrders' => $regularOrders],
            ['merged' => array_merge($laserOrders, $regularOrders)],
        );
        return $laserOrders;
    }

    public function __construct(
        null|CheckAuthentication $checkAuthentication = null,
        null|RegularOrderRetrieval $regularOrderRetrieval = null,
        null|LaserOrderRetrieval $laserOrderRetrieval = null,
        null|RegularOrderCreation $regularOrderCreation = null,
        null|LaserOrderCreation $laserOrderCreation = null,
        null|IDataBaseCreateRegularOrder $iDataBaseCreateRegularOrder = null,
        null|IDataBaseCreateLaserOrder $iDataBaseCreateLaserOrder = null,
        null|IDataBaseRetrieveLaserOrders $iDataBaseRetrieveLaserOrders = null,
        null|IDataBaseRetrieveRegularOrders $iDataBaseRetrieveRegularOrders = null,
        null|IDataBaseCreateDefaultRegularOrder $iDataBaseCreateDefaultRegularOrder = null
    ) {
        $this->checkAuthentication = $checkAuthentication ?: new CheckAuthentication;

        $this->regularOrderRetrieval = $regularOrderRetrieval ?: new RegularOrderRetrieval;
        $this->laserOrderRetrieval = $laserOrderRetrieval ?: new LaserOrderRetrieval;
        $this->iDataBaseRetrieveLaserOrders = $iDataBaseRetrieveLaserOrders ?: new DatabaseRetrieveLaserOrders;
        $this->iDataBaseRetrieveRegularOrders = $iDataBaseRetrieveRegularOrders ?: new DatabaseRetrieveRegularOrders;

        $this->regularOrderCreation = $regularOrderCreation ?: new RegularOrderCreation;
        $this->laserOrderCreation = $laserOrderCreation ?: new LaserOrderCreation;
        $this->iDataBaseCreateLaserOrder = $iDataBaseCreateLaserOrder ?: new DataBaseCreateLaserOrder;
        $this->iDataBaseCreateRegularOrder = $iDataBaseCreateRegularOrder ?: new DatabaseCreateRegularOrder;
        $this->iDataBaseCreateDefaultRegularOrder = $iDataBaseCreateDefaultRegularOrder ?: new DatabaseCreateDefaultRegularOrder;
    }

    public function laserIndex(
        ?bool $priceOtherwiseTime = null,
        ?string $username = null,
        ?int $lastOrderId = null,
        ?int $count = null,
        ?string $operator = null,
        ?int $price = null,
        ?int $timeConsumption = null
    ): JsonResponse {
        $args = func_get_args();
        return $this->index('laser', ...$args);
    }

    public function regularIndex(
        ?bool $priceOtherwiseTime = null,
        ?string $username = null,
        ?int $lastOrderId = null,
        ?int $count = null,
        ?string $operator = null,
        ?int $price = null,
        ?int $timeConsumption = null
    ): JsonResponse {
        $args = func_get_args();
        return $this->index('regular', ...$args);
    }

    private function index(
        string $businessName,
        ?bool $priceOtherwiseTime = null,
        ?string $username = null,
        ?int $lastOrderId = null,
        ?int $count = null,
        ?string $operator = null,
        ?int $price = null,
        ?int $timeConsumption = null
    ): JsonResponse {
        $dsUser = $this->checkAuthentication->getAuthenticatedDSUser();

        $args = [];
        $args['user'] = $dsUser;
        $args['db'] = $this->{'iDataBaseRetrieve' . ucfirst($businessName) . 'Orders'};

        if ($username === null) {
            $args['lastOrderId'] = $lastOrderId;
            $args['count'] = $count;
        } else {
            $args['targetUser'] = User::query()->where('username', $username)->first()->authenticatableRole()->getDataStructure();
        }

        if ($priceOtherwiseTime !== null) {
            $args['operator'] = $operator;
            if ($priceOtherwiseTime === true) {
                $args['price'] = $price;
            } else {
                $args['timeConsumption'] = $timeConsumption;
            }
        }

        $method = 'get' . ucfirst($businessName) . 'Orders' .
            (isset($priceOtherwiseTime) && $priceOtherwiseTime === true
                ? 'ByPrice'
                : (isset($priceOtherwiseTime) && $priceOtherwiseTime === false
                    ? 'ByTimeConsumption'
                    : ''
                )
            ) .
            ($username === null ? '' : 'ByUser');

        /** @var DSOrders $dsOrders */
        $dsOrders = $this->{strtolower($businessName) . 'OrderRetrieval'}->{$method}(...$args);

        return response()->json($dsOrders->toArray());
    }

    public function store(Request $request): JsonResponse
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        /** @var Authenticatable $user */
        $user = User::query()
            ->whereKey($request->accountId)
            ->first();
        $dsUser = $user->authenticatableRole()->getDataStructure();
        $gender = $user->gender;

        switch (strtolower($request->businessName)) {
            case 'laser':
                $parts = Part::query();
                foreach ($requestParts = $request->parts as $partName) {
                    $parts = $parts->where('name', '=', $partName, 'or');
                }
                $parts = $parts->get()->all();
                $parts = Part::getDSParts($parts, $gender);

                $packages = Package::query();
                foreach ($requestPackages = $request->packages as $packageName) {
                    $packages = $packages->where('name', '=', $packageName, 'or');
                }
                $packages = $packages->get()->all();
                $packages = Package::getDSPackages($packages, $gender);

                $order = $this->laserOrderCreation->createLaserOrder($dsUser, $dsAuthenticated, $this->iDataBaseCreateLaserOrder, $parts, $packages);
                break;

            case 'regular':
                if ($dsAuthenticated instanceof DSAdmin) {
                    $order = $this->regularOrderCreation->createRegularOrder($request->price, $request->timeConsumption, $dsUser, $dsAuthenticated, $this->iDataBaseCreateRegularOrder);
                } else {
                    $order = $this->regularOrderCreation->createDefaultRegularOrder($dsUser, $dsAuthenticated, $this->iDataBaseCreateDefaultRegularOrder);
                }
                break;

            default:
                throw new \LogicException('There\'s no such business name: ' . strval($request->businessName), 500);
                break;
        }

        return response()->json($order->toArray());
    }

    public function show(string $businessName, int $accountId, int $childOrderId): JsonResponse
    {
        if ($businessName === 'laser') {
            return $this->laserShow($accountId, $childOrderId);
        } elseif ($businessName === 'regular') {
            return $this->regularShow($accountId, $childOrderId);
        }

        throw new \LogicException('Failed to find business', 404);
    }

    private function laserShow(int $accountId, int $laserOrderId): JsonResponse
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        /** @var User $user */
        $user = User::query()->where((new User)->getKeyName(), '=', $accountId)->first();
        $dsUser = $user->authenticatableRole()->getDataStructure();

        $laserOrders = $this->laserOrderRetrieval->getLaserOrdersByUser($dsUser, $dsAuthenticated, $this->iDataBaseRetrieveLaserOrders);

        /** @var DSLaserOrder $laserOrder */
        foreach ($laserOrders as $laserOrder) {
            if ($laserOrder->getId() === $laserOrderId) {
                break;
            }
        }

        return response()->json($laserOrder->toArray());
    }

    private function regularShow(int $accountId, int $regularOrderId): JsonResponse
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        /** @var User $user */
        $user = User::query()->where((new User)->getKeyName(), '=', $accountId)->first();
        $dsUser = $user->authenticatableRole()->getDataStructure();

        $regularOrders = $this->regularOrderRetrieval->getRegularOrdersByUser($dsUser, $dsAuthenticated, $this->iDataBaseRetrieveRegularOrders);

        /** @var DSLaserOrder $regularOrder */
        foreach ($regularOrders as $regularOrder) {
            if ($regularOrder->getId() === $regularOrderId) {
                break;
            }
        }

        return response()->json($regularOrder->toArray());
    }
}
