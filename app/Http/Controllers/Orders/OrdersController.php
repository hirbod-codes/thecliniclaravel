<?php

namespace App\Http\Controllers\Orders;

use App\Auth\CheckAuthentication;
use App\Http\Controllers\Controller;
use App\Models\Auth\User as Authenticatable;
use App\Models\Order\LaserOrder;
use App\Models\Order\Order;
use App\Models\Order\RegularOrder;
use App\Models\Package\Package;
use App\Models\Part\Part;
use App\Models\User;
use Database\Interactions\Orders\Creation\DatabaseCreateDefaultRegularOrder;
use Database\Interactions\Orders\Creation\DatabaseCreateLaserOrder;
use Database\Interactions\Orders\Creation\DatabaseCreateRegularOrder;
use Database\Interactions\Orders\Deletion\DataBaseDeleteLaserOrder;
use Database\Interactions\Orders\Deletion\DataBaseDeleteRegularOrder;
use Database\Interactions\Orders\Retrieval\DatabaseRetrieveLaserOrders;
use Database\Interactions\Orders\Retrieval\DatabaseRetrieveRegularOrders;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use TheClinicDataStructures\DataStructures\Order\DSOrders;
use TheClinicDataStructures\DataStructures\Order\Laser\DSLaserOrder;
use TheClinicDataStructures\DataStructures\User\DSAdmin;
use TheClinicUseCases\Orders\Creation\LaserOrderCreation;
use TheClinicUseCases\Orders\Creation\RegularOrderCreation;
use TheClinicUseCases\Orders\Deletion\LaserOrderDeletion;
use TheClinicUseCases\Orders\Deletion\RegularOrderDeletion;
use TheClinicUseCases\Orders\Interfaces\IDataBaseCreateLaserOrder;
use TheClinicUseCases\Orders\Interfaces\IDataBaseCreateRegularOrder;
use TheClinicUseCases\Orders\Interfaces\IDataBaseCreateDefaultRegularOrder;
use TheClinicUseCases\Orders\Interfaces\IDataBaseDeleteLaserOrder;
use TheClinicUseCases\Orders\Interfaces\IDataBaseDeleteRegularOrder;
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

    private RegularOrderDeletion $regularOrderDeletion;

    private LaserOrderDeletion $laserOrderDeletion;

    private IDataBaseDeleteLaserOrder $iDataBaseDeleteLaserOrder;

    private IDataBaseDeleteRegularOrder $iDataBaseDeleteRegularOrder;

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
        null|IDataBaseCreateDefaultRegularOrder $iDataBaseCreateDefaultRegularOrder = null,
        null|RegularOrderDeletion $regularOrderDeletion = null,
        null|LaserOrderDeletion $laserOrderDeletion = null,
        null|IDataBaseDeleteLaserOrder $iDataBaseDeleteLaserOrder = null,
        null|IDataBaseDeleteRegularOrder $iDataBaseDeleteRegularOrder = null
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

        $this->regularOrderDeletion = $regularOrderDeletion ?: new RegularOrderDeletion;
        $this->laserOrderDeletion = $laserOrderDeletion ?: new LaserOrderDeletion;
        $this->iDataBaseDeleteLaserOrder = $iDataBaseDeleteLaserOrder ?: new DataBaseDeleteLaserOrder;
        $this->iDataBaseDeleteRegularOrder = $iDataBaseDeleteRegularOrder ?: new DataBaseDeleteRegularOrder;
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
        switch (strtolower($businessName)) {
            case 'laser':
                return $this->laserShow($accountId, $childOrderId);
                break;

            case 'regular':
                return $this->regularShow($accountId, $childOrderId);
                break;

            default:
                throw new \LogicException('Failed to find business', 404);
                break;
        }
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

    public function destroy(string $businessName, int $accountId, int $childOrderId): Response|ResponseFactory
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        $found = false;
        switch (strtolower($businessName)) {
            case 'regular':
                /**
                 * @var User $user
                 * @var Order $order
                 * */
                foreach (($user = User::query()->whereKey($accountId)->first())->orders as $order) {
                    /** @var RegularOrder $regularOrder */
                    if (($regularOrder = $order->regularOrder) !== null && $regularOrder->getKey() === $childOrderId) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    throw new \LogicException('Failed to find the requested order.', 404);
                }

                $this->regularOrderDeletion->deleteRegularOrder($regularOrder->getDSRegularOrder(), $user->authenticatableRole()->getDataStructure(), $dsAuthenticated, $this->iDataBaseDeleteRegularOrder);
                break;

            case 'laser':
                /**
                 * @var User $user
                 * @var Order $order
                 * */
                foreach (($user = User::query()->whereKey($accountId)->first())->orders as $order) {
                    /** @var LaserOrder $laserOrder */
                    if (($laserOrder = $order->laserOrder) !== null && $laserOrder->getKey() === $childOrderId) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    throw new \LogicException('Failed to find the requested order.', 404);
                }

                $this->laserOrderDeletion->deleteLaserOrder($laserOrder->getDSLaserOrder(), $user->authenticatableRole()->getDataStructure(), $dsAuthenticated, $this->iDataBaseDeleteLaserOrder);
                break;

            default:
                throw new \LogicException('Failed to find business.', 404);
                break;
        }

        return response();
    }
}
