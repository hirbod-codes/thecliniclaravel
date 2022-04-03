<?php

namespace Tests\Unit\app\Http\Controller;

use App\Auth\CheckAuthentication;
use App\Http\Controllers\Orders\OrdersController;
use App\Models\Order\LaserOrder;
use App\Models\Order\Order;
use App\Models\Order\RegularOrder;
use App\Models\Package\Package;
use App\Models\Part\Part;
use App\Models\roles\PatientRole;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;
use TheClinicDataStructures\DataStructures\Order\DSPackage;
use TheClinicDataStructures\DataStructures\Order\DSPackages;
use TheClinicDataStructures\DataStructures\Order\DSPart;
use TheClinicDataStructures\DataStructures\Order\DSParts;
use TheClinicDataStructures\DataStructures\Order\Laser\DSLaserOrder;
use TheClinicDataStructures\DataStructures\Order\Laser\DSLaserOrders;
use TheClinicDataStructures\DataStructures\Order\Regular\DSRegularOrder;
use TheClinicDataStructures\DataStructures\Order\Regular\DSRegularOrders;
use TheClinicDataStructures\DataStructures\User\DSAdmin;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Orders\Creation\LaserOrderCreation;
use TheClinicUseCases\Orders\Creation\RegularOrderCreation;
use TheClinicUseCases\Orders\Interfaces\IDataBaseCreateDefaultRegularOrder;
use TheClinicUseCases\Orders\Interfaces\IDataBaseCreateLaserOrder;
use TheClinicUseCases\Orders\Interfaces\IDataBaseCreateRegularOrder;
use TheClinicUseCases\Orders\Interfaces\IDataBaseRetrieveLaserOrders;
use TheClinicUseCases\Orders\Interfaces\IDataBaseRetrieveRegularOrders;
use TheClinicUseCases\Orders\Retrieval\LaserOrderRetrieval;
use TheClinicUseCases\Orders\Retrieval\RegularOrderRetrieval;

class OrdersControllerTest extends TestCase
{
    use GetAuthenticatables;

    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    // Laser

    public function testLaserIndex(): void
    {
        $user = $this->getAuthenticatable('admin');
        $dsUser = $user->getDataStructure();

        $targetUser = $this->getAuthenticatable('patient');
        $dsTargetUser = $targetUser->getDataStructure();

        $dsOrders = new DSLaserOrders();
        $dsOrders[] = LaserOrder::first()->getDSLaserOrder();

        /** @var IDataBaseRetrieveLaserOrders|MockInterface $iDataBaseRetrieveLaserOrders */
        $iDataBaseRetrieveLaserOrders = Mockery::mock(IDataBaseRetrieveLaserOrders::class);

        $laserOrderRetrievalArgs = [
            'lastOrderId' => $lastOrderId = 10,
            'count' => $count = 10,
            'user' => $dsUser,
            'db' => $iDataBaseRetrieveLaserOrders,
        ];

        $indexArgs = [
            'lastOrderId' => $lastOrderId,
            'count' => $count,
        ];

        /** @var LaserOrderRetrieval|MockInterface $laserOrderRetrieval */
        $laserOrderRetrieval = Mockery::mock(LaserOrderRetrieval::class);
        $laserOrderRetrieval
            ->shouldReceive('getLaserOrders')
            ->with(...array_values($laserOrderRetrievalArgs))
            ->andReturn($dsOrders);

        /** @var CheckAuthentication|MockInterface $checkAuthentication */
        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication
            ->shouldReceive('getAuthenticatedDSUser')
            ->andReturn($dsUser);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'checkAuthentication' => $checkAuthentication,
            'laserOrderRetrieval' => $laserOrderRetrieval,
            'iDataBaseRetrieveLaserOrders' => $iDataBaseRetrieveLaserOrders,
        ];
        $response = (new OrdersController(...$controllerArgs))->laserIndex(...$indexArgs);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertNotCount(0, $response->original);
    }

    public function testLaserIndexByUser(): void
    {
        $user = $this->getAuthenticatable('admin');
        $dsUser = $user->getDataStructure();

        $targetUser = $this->getAuthenticatable('patient');
        $dsTargetUser = $targetUser->getDataStructure();

        $dsOrders = new DSLaserOrders();
        foreach ($orders = $targetUser->user->orders as $order) {
            if (($laserOrder = $order->laserOrder) !== null) {
                $dsOrders[] = $laserOrder->getDSLaserOrder();
                goto after_loop;
            }
        }
        throw new \RuntimeException('Failed to find a laser order.', 500);
        after_loop:

        /** @var IDataBaseRetrieveLaserOrders|MockInterface $iDataBaseRetrieveLaserOrders */
        $iDataBaseRetrieveLaserOrders = Mockery::mock(IDataBaseRetrieveLaserOrders::class);

        $laserOrderRetrievalArgs = [
            'targetUser' => Mockery::on(function (DSUser $value) use ($dsTargetUser) {
                if ($value->getUsername() !== $dsTargetUser->getUsername()) {
                    return false;
                }
                return true;
            }),
            'user' => Mockery::on(function (DSUser $value) use ($dsUser) {
                if ($value->getUsername() !== $dsUser->getUsername()) {
                    return false;
                }
                return true;
            }),
            'db' => $iDataBaseRetrieveLaserOrders
        ];

        $indexArgs = [
            'username' => $targetUser->user->username
        ];

        /** @var LaserOrderRetrieval|MockInterface $laserOrderRetrieval */
        $laserOrderRetrieval = Mockery::mock(LaserOrderRetrieval::class);
        $laserOrderRetrieval
            ->shouldReceive('getLaserOrdersByUser')
            ->with(...array_values($laserOrderRetrievalArgs))
            ->andReturn($dsOrders);

        /** @var CheckAuthentication|MockInterface $checkAuthentication */
        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication
            ->shouldReceive('getAuthenticatedDSUser')
            ->andReturn($dsUser);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'checkAuthentication' => $checkAuthentication,
            'laserOrderRetrieval' => $laserOrderRetrieval,
            'iDataBaseRetrieveLaserOrders' => $iDataBaseRetrieveLaserOrders,
        ];
        $response = (new OrdersController(...$controllerArgs))->laserIndex(...$indexArgs);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertNotCount(0, $response->original);
    }

    public function testLaserIndexByPriceByUser(): void
    {
        $user = $this->getAuthenticatable('admin');
        $dsUser = $user->getDataStructure();

        $targetUser = $this->getAuthenticatable('patient');
        $dsTargetUser = $targetUser->getDataStructure();

        $dsOrders = new DSLaserOrders();
        foreach ($targetUser->user->orders as $value) {
            if (($laserOrder = $value->laserOrder) !== null) {
                $dsOrders[] = $laserOrder->getDSLaserOrder();
                goto after_loop;
            }
        }
        throw new \RuntimeException('Failed to find a laser order.', 500);
        after_loop:

        /** @var IDataBaseRetrieveLaserOrders|MockInterface $iDataBaseRetrieveLaserOrders */
        $iDataBaseRetrieveLaserOrders = Mockery::mock(IDataBaseRetrieveLaserOrders::class);

        $laserOrderRetrievalArgs = [
            'operator' => $operator = $this->faker->randomElement(['<=', '>=', '=', '<>', '<', '>']),
            'price' => $price = $this->faker->numberBetween(1000000, 10000000),
            'targetUser' => Mockery::on(function (DSUser $value) use ($dsTargetUser) {
                if ($value->getUsername() !== $dsTargetUser->getUsername()) {
                    return false;
                }
                return true;
            }),
            'user' => Mockery::on(function (DSUser $value) use ($dsUser) {
                if ($value->getUsername() !== $dsUser->getUsername()) {
                    return false;
                }
                return true;
            }),
            'db' => $iDataBaseRetrieveLaserOrders
        ];

        $indexArgs = [
            'username' => $targetUser->user->username,
            'priceOtherwiseTime' => true,
            'price' => $price,
            'operator' => $operator
        ];

        /** @var LaserOrderRetrieval|MockInterface $laserOrderRetrieval */
        $laserOrderRetrieval = Mockery::mock(LaserOrderRetrieval::class);
        $laserOrderRetrieval
            ->shouldReceive('getLaserOrdersByPriceByUser')
            ->with(...array_values($laserOrderRetrievalArgs))
            ->andReturn($dsOrders);

        /** @var CheckAuthentication|MockInterface $checkAuthentication */
        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication
            ->shouldReceive('getAuthenticatedDSUser')
            ->andReturn($dsUser);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'checkAuthentication' => $checkAuthentication,
            'laserOrderRetrieval' => $laserOrderRetrieval,
            'iDataBaseRetrieveLaserOrders' => $iDataBaseRetrieveLaserOrders,
        ];
        $response = (new OrdersController(...$controllerArgs))->laserIndex(...$indexArgs);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertNotCount(0, $response->original);
    }

    public function testLaserIndexByPrice(): void
    {
        $user = $this->getAuthenticatable('admin');
        $dsUser = $user->getDataStructure();

        $dsOrders = new DSLaserOrders();
        $dsOrders[] = LaserOrder::first()->getDSLaserOrder();

        /** @var IDataBaseRetrieveLaserOrders|MockInterface $iDataBaseRetrieveLaserOrders */
        $iDataBaseRetrieveLaserOrders = Mockery::mock(IDataBaseRetrieveLaserOrders::class);

        $laserOrderRetrievalArgs = [
            'lastOrderId' => $lastOrderId = 10,
            'count' => $count = 10,
            'operator' => $operator = $this->faker->randomElement(['<=', '>=', '=', '<>', '<', '>']),
            'price' => $price = $this->faker->numberBetween(1000000, 10000000),
            'user' => Mockery::on(function (DSUser $value) use ($dsUser) {
                if ($value->getUsername() !== $dsUser->getUsername()) {
                    return false;
                }
                return true;
            }),
            'db' => $iDataBaseRetrieveLaserOrders,
        ];

        $indexArgs = [
            'priceOtherwiseTime' => true,
            'price' => $price,
            'operator' => $operator,
            'lastOrderId' => $lastOrderId,
            'count' => $count,
        ];

        /** @var LaserOrderRetrieval|MockInterface $laserOrderRetrieval */
        $laserOrderRetrieval = Mockery::mock(LaserOrderRetrieval::class);
        $laserOrderRetrieval
            ->shouldReceive('getLaserOrdersByPrice')
            ->with(...array_values($laserOrderRetrievalArgs))
            ->andReturn($dsOrders);

        /** @var CheckAuthentication|MockInterface $checkAuthentication */
        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication
            ->shouldReceive('getAuthenticatedDSUser')
            ->andReturn($dsUser);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'checkAuthentication' => $checkAuthentication,
            'laserOrderRetrieval' => $laserOrderRetrieval,
            'iDataBaseRetrieveLaserOrders' => $iDataBaseRetrieveLaserOrders,
        ];
        $response = (new OrdersController(...$controllerArgs))->laserIndex(...$indexArgs);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertNotCount(0, $response->original);
    }

    public function testLaserIndexByTimeConsumptionByUser(): void
    {
        $user = $this->getAuthenticatable('admin');
        $dsUser = $user->getDataStructure();

        $targetUser = $this->getAuthenticatable('patient');
        $dsTargetUser = $targetUser->getDataStructure();

        $dsOrders = new DSLaserOrders();
        foreach ($targetUser->user->orders as $value) {
            if (($laserOrder = $value->laserOrder) !== null) {
                $dsOrders[] = $laserOrder->getDSLaserOrder();
                goto after_loop;
            }
        }
        throw new \RuntimeException('Failed to find a laser order.', 500);
        after_loop:

        /** @var IDataBaseRetrieveLaserOrders|MockInterface $iDataBaseRetrieveLaserOrders */
        $iDataBaseRetrieveLaserOrders = Mockery::mock(IDataBaseRetrieveLaserOrders::class);

        $laserOrderRetrievalArgs = [
            'operator' => $operator = $this->faker->randomElement(['<=', '>=', '=', '<>', '<', '>']),
            'timeConsumption' => $timeConsumption = $this->faker->numberBetween(1000000, 10000000),
            'targetUser' => Mockery::on(function (DSUser $value) use ($dsTargetUser) {
                if ($value->getUsername() !== $dsTargetUser->getUsername()) {
                    return false;
                }
                return true;
            }),
            'user' => Mockery::on(function (DSUser $value) use ($dsUser) {
                if ($value->getUsername() !== $dsUser->getUsername()) {
                    return false;
                }
                return true;
            }),
            'db' => $iDataBaseRetrieveLaserOrders
        ];

        $indexArgs = [
            'username' => $targetUser->user->username,
            'priceOtherwiseTime' => false,
            'timeConsumption' => $timeConsumption,
            'operator' => $operator
        ];

        /** @var LaserOrderRetrieval|MockInterface $laserOrderRetrieval */
        $laserOrderRetrieval = Mockery::mock(LaserOrderRetrieval::class);
        $laserOrderRetrieval
            ->shouldReceive('getLaserOrdersByTimeConsumptionByUser')
            ->with(...array_values($laserOrderRetrievalArgs))
            ->andReturn($dsOrders);

        /** @var CheckAuthentication|MockInterface $checkAuthentication */
        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication
            ->shouldReceive('getAuthenticatedDSUser')
            ->andReturn($dsUser);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'checkAuthentication' => $checkAuthentication,
            'laserOrderRetrieval' => $laserOrderRetrieval,
            'iDataBaseRetrieveLaserOrders' => $iDataBaseRetrieveLaserOrders,
        ];

        $response = (new OrdersController(...$controllerArgs))->laserIndex(...$indexArgs);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertNotCount(0, $response->original);
    }

    public function testLaserIndexByTimeConsumption(): void
    {
        $user = $this->getAuthenticatable('admin');
        $dsUser = $user->getDataStructure();

        $dsOrders = new DSLaserOrders();
        $dsOrders[] = LaserOrder::first()->getDSLaserOrder();

        /** @var IDataBaseRetrieveLaserOrders|MockInterface $iDataBaseRetrieveLaserOrders */
        $iDataBaseRetrieveLaserOrders = Mockery::mock(IDataBaseRetrieveLaserOrders::class);

        $laserOrderRetrievalArgs = [
            'lastOrderId' => $lastOrderId = 10,
            'count' => $count = 10,
            'operator' => $operator = $this->faker->randomElement(['<=', '>=', '=', '<>', '<', '>']),
            'timeConsumption' => $timeConsumption = $this->faker->numberBetween(1000000, 10000000),
            'user' => $dsUser,
            'db' => $iDataBaseRetrieveLaserOrders,
        ];

        $indexArgs = [
            'priceOtherwiseTime' => false,
            'timeConsumption' => $timeConsumption,
            'operator' => $operator,
            'lastOrderId' => $lastOrderId,
            'count' => $count,
        ];

        /** @var LaserOrderRetrieval|MockInterface $laserOrderRetrieval */
        $laserOrderRetrieval = Mockery::mock(LaserOrderRetrieval::class);
        $laserOrderRetrieval
            ->shouldReceive('getLaserOrdersByTimeConsumption')
            ->with(...array_values($laserOrderRetrievalArgs))
            ->andReturn($dsOrders);

        /** @var CheckAuthentication|MockInterface $checkAuthentication */
        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication
            ->shouldReceive('getAuthenticatedDSUser')
            ->andReturn($dsUser);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'checkAuthentication' => $checkAuthentication,
            'laserOrderRetrieval' => $laserOrderRetrieval,
            'iDataBaseRetrieveLaserOrders' => $iDataBaseRetrieveLaserOrders,
        ];

        $response = (new OrdersController(...$controllerArgs))->laserIndex(...$indexArgs);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertNotCount(0, $response->original);
    }

    // Regular

    public function testRegularIndex(): void
    {
        $user = $this->getAuthenticatable('admin');
        $dsUser = $user->getDataStructure();

        $targetUser = $this->getAuthenticatable('patient');
        $dsTargetUser = $targetUser->getDataStructure();

        $dsOrders = new DSRegularOrders();
        $dsOrders[] = RegularOrder::first()->getDSRegularOrder();

        /** @var IDataBaseRetrieveRegularOrders|MockInterface $iDataBaseRetrieveRegularOrders */
        $iDataBaseRetrieveRegularOrders = Mockery::mock(IDataBaseRetrieveRegularOrders::class);

        $regularOrderRetrievalArgs = [
            'lastOrderId' => $lastOrderId = 10,
            'count' => $count = 10,
            'user' => $dsUser,
            'db' => $iDataBaseRetrieveRegularOrders,
        ];

        $indexArgs = [
            'lastOrderId' => $lastOrderId,
            'count' => $count,
        ];

        /** @var RegularOrderRetrieval|MockInterface $regularOrderRetrieval */
        $regularOrderRetrieval = Mockery::mock(RegularOrderRetrieval::class);
        $regularOrderRetrieval
            ->shouldReceive('getRegularOrders')
            ->with(...array_values($regularOrderRetrievalArgs))
            ->andReturn($dsOrders);

        /** @var CheckAuthentication|MockInterface $checkAuthentication */
        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication
            ->shouldReceive('getAuthenticatedDSUser')
            ->andReturn($dsUser);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'checkAuthentication' => $checkAuthentication,
            'regularOrderRetrieval' => $regularOrderRetrieval,
            'iDataBaseRetrieveRegularOrders' => $iDataBaseRetrieveRegularOrders,
        ];
        $response = (new OrdersController(...$controllerArgs))->regularIndex(...$indexArgs);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertNotCount(0, $response->original);
    }

    public function testRegularIndexByUser(): void
    {
        $user = $this->getAuthenticatable('admin');
        $dsUser = $user->getDataStructure();

        $targetUser = $this->getAuthenticatable('patient');
        $dsTargetUser = $targetUser->getDataStructure();

        $dsOrders = new DSRegularOrders();
        foreach ($targetUser->user->orders as $value) {
            if (($regularOrder = $value->regularOrder) !== null) {
                $dsOrders[] = $regularOrder->getDSRegularOrder();
                goto after_loop;
            }
        }
        throw new \RuntimeException('Failed to find a regular order.', 500);
        after_loop:

        /** @var IDataBaseRetrieveRegularOrders|MockInterface $iDataBaseRetrieveRegularOrders */
        $iDataBaseRetrieveRegularOrders = Mockery::mock(IDataBaseRetrieveRegularOrders::class);

        $regularOrderRetrievalArgs = [
            'targetUser' => Mockery::on(function (DSUser $value) use ($dsTargetUser) {
                if ($value->getUsername() !== $dsTargetUser->getUsername()) {
                    return false;
                }
                return true;
            }),
            'user' => Mockery::on(function (DSUser $value) use ($dsUser) {
                if ($value->getUsername() !== $dsUser->getUsername()) {
                    return false;
                }
                return true;
            }),
            'db' => $iDataBaseRetrieveRegularOrders
        ];

        $indexArgs = [
            'username' => $targetUser->user->username
        ];

        /** @var RegularOrderRetrieval|MockInterface $regularOrderRetrieval */
        $regularOrderRetrieval = Mockery::mock(RegularOrderRetrieval::class);
        $regularOrderRetrieval
            ->shouldReceive('getRegularOrdersByUser')
            ->with(...array_values($regularOrderRetrievalArgs))
            ->andReturn($dsOrders);

        /** @var CheckAuthentication|MockInterface $checkAuthentication */
        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication
            ->shouldReceive('getAuthenticatedDSUser')
            ->andReturn($dsUser);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'checkAuthentication' => $checkAuthentication,
            'regularOrderRetrieval' => $regularOrderRetrieval,
            'iDataBaseRetrieveRegularOrders' => $iDataBaseRetrieveRegularOrders,
        ];
        $response = (new OrdersController(...$controllerArgs))->regularIndex(...$indexArgs);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertNotCount(0, $response->original);
    }

    public function testRegularIndexByPriceByUser(): void
    {
        $user = $this->getAuthenticatable('admin');
        $dsUser = $user->getDataStructure();

        $targetUser = $this->getAuthenticatable('patient');
        $dsTargetUser = $targetUser->getDataStructure();

        $dsOrders = new DSRegularOrders();
        foreach ($targetUser->user->orders as $value) {
            if (($regularOrder = $value->regularOrder) !== null) {
                $dsOrders[] = $regularOrder->getDSRegularOrder();
                goto after_loop;
            }
        }
        throw new \RuntimeException('Failed to find a regular order.', 500);
        after_loop:

        /** @var IDataBaseRetrieveRegularOrders|MockInterface $iDataBaseRetrieveRegularOrders */
        $iDataBaseRetrieveRegularOrders = Mockery::mock(IDataBaseRetrieveRegularOrders::class);

        $regularOrderRetrievalArgs = [
            'operator' => $operator = $this->faker->randomElement(['<=', '>=', '=', '<>', '<', '>']),
            'price' => $price = $this->faker->numberBetween(1000000, 10000000),
            'targetUser' => Mockery::on(function (DSUser $value) use ($dsTargetUser) {
                if ($value->getUsername() !== $dsTargetUser->getUsername()) {
                    return false;
                }
                return true;
            }),
            'user' => Mockery::on(function (DSUser $value) use ($dsUser) {
                if ($value->getUsername() !== $dsUser->getUsername()) {
                    return false;
                }
                return true;
            }),
            'db' => $iDataBaseRetrieveRegularOrders
        ];

        $indexArgs = [
            'username' => $targetUser->user->username,
            'priceOtherwiseTime' => true,
            'price' => $price,
            'operator' => $operator
        ];

        /** @var RegularOrderRetrieval|MockInterface $regularOrderRetrieval */
        $regularOrderRetrieval = Mockery::mock(RegularOrderRetrieval::class);
        $regularOrderRetrieval
            ->shouldReceive('getRegularOrdersByPriceByUser')
            ->with(...array_values($regularOrderRetrievalArgs))
            ->andReturn($dsOrders);

        /** @var CheckAuthentication|MockInterface $checkAuthentication */
        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication
            ->shouldReceive('getAuthenticatedDSUser')
            ->andReturn($dsUser);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'checkAuthentication' => $checkAuthentication,
            'regularOrderRetrieval' => $regularOrderRetrieval,
            'iDataBaseRetrieveRegularOrders' => $iDataBaseRetrieveRegularOrders,
        ];
        $response = (new OrdersController(...$controllerArgs))->regularIndex(...$indexArgs);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertNotCount(0, $response->original);
    }

    public function testRegularIndexByPrice(): void
    {
        $user = $this->getAuthenticatable('admin');
        $dsUser = $user->getDataStructure();

        $dsOrders = new DSRegularOrders();
        $dsOrders[] = RegularOrder::first()->getDSRegularOrder();

        /** @var IDataBaseRetrieveRegularOrders|MockInterface $iDataBaseRetrieveRegularOrders */
        $iDataBaseRetrieveRegularOrders = Mockery::mock(IDataBaseRetrieveRegularOrders::class);

        $regularOrderRetrievalArgs = [
            'lastOrderId' => $lastOrderId = 10,
            'count' => $count = 10,
            'operator' => $operator = $this->faker->randomElement(['<=', '>=', '=', '<>', '<', '>']),
            'price' => $price = $this->faker->numberBetween(1000000, 10000000),
            'user' => $dsUser,
            'db' => $iDataBaseRetrieveRegularOrders,
        ];

        $indexArgs = [
            'priceOtherwiseTime' => true,
            'price' => $price,
            'operator' => $operator,
            'lastOrderId' => $lastOrderId,
            'count' => $count,
        ];

        /** @var RegularOrderRetrieval|MockInterface $regularOrderRetrieval */
        $regularOrderRetrieval = Mockery::mock(RegularOrderRetrieval::class);
        $regularOrderRetrieval
            ->shouldReceive('getRegularOrdersByPrice')
            ->with(...array_values($regularOrderRetrievalArgs))
            ->andReturn($dsOrders);

        /** @var CheckAuthentication|MockInterface $checkAuthentication */
        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication
            ->shouldReceive('getAuthenticatedDSUser')
            ->andReturn($dsUser);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'checkAuthentication' => $checkAuthentication,
            'regularOrderRetrieval' => $regularOrderRetrieval,
            'iDataBaseRetrieveRegularOrders' => $iDataBaseRetrieveRegularOrders,
        ];
        $response = (new OrdersController(...$controllerArgs))->regularIndex(...$indexArgs);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertNotCount(0, $response->original);
    }

    public function testRegularIndexByTimeConsumptionByUser(): void
    {
        $user = $this->getAuthenticatable('admin');
        $dsUser = $user->getDataStructure();

        $targetUser = $this->getAuthenticatable('patient');
        $dsTargetUser = $targetUser->getDataStructure();

        $dsOrders = new DSRegularOrders();
        foreach ($targetUser->user->orders as $value) {
            if (($regularOrder = $value->regularOrder) !== null) {
                $dsOrders[] = $regularOrder->getDSRegularOrder();
                goto after_loop;
            }
        }
        throw new \RuntimeException('Failed to find a regular order.', 500);
        after_loop:

        /** @var IDataBaseRetrieveRegularOrders|MockInterface $iDataBaseRetrieveRegularOrders */
        $iDataBaseRetrieveRegularOrders = Mockery::mock(IDataBaseRetrieveRegularOrders::class);

        $regularOrderRetrievalArgs = [
            'operator' => $operator = $this->faker->randomElement(['<=', '>=', '=', '<>', '<', '>']),
            'timeConsumption' => $timeConsumption = $this->faker->numberBetween(1000000, 10000000),
            'targetUser' => Mockery::on(function (DSUser $value) use ($dsTargetUser) {
                if ($value->getUsername() !== $dsTargetUser->getUsername()) {
                    return false;
                }
                return true;
            }),
            'user' => Mockery::on(function (DSUser $value) use ($dsUser) {
                if ($value->getUsername() !== $dsUser->getUsername()) {
                    return false;
                }
                return true;
            }),
            'db' => $iDataBaseRetrieveRegularOrders
        ];

        $indexArgs = [
            'username' => $targetUser->user->username,
            'priceOtherwiseTime' => false,
            'timeConsumption' => $timeConsumption,
            'operator' => $operator
        ];

        /** @var RegularOrderRetrieval|MockInterface $regularOrderRetrieval */
        $regularOrderRetrieval = Mockery::mock(RegularOrderRetrieval::class);
        $regularOrderRetrieval
            ->shouldReceive('getRegularOrdersByTimeConsumptionByUser')
            ->with(...array_values($regularOrderRetrievalArgs))
            ->andReturn($dsOrders);

        /** @var CheckAuthentication|MockInterface $checkAuthentication */
        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication
            ->shouldReceive('getAuthenticatedDSUser')
            ->andReturn($dsUser);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'checkAuthentication' => $checkAuthentication,
            'regularOrderRetrieval' => $regularOrderRetrieval,
            'iDataBaseRetrieveRegularOrders' => $iDataBaseRetrieveRegularOrders,
        ];
        $response = (new OrdersController(...$controllerArgs))->regularIndex(...$indexArgs);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertNotCount(0, $response->original);
    }

    public function testRegularIndexByTimeConsumption(): void
    {
        $user = $this->getAuthenticatable('admin');
        $dsUser = $user->getDataStructure();

        $dsOrders = new DSRegularOrders();
        $dsOrders[] = RegularOrder::first()->getDSRegularOrder();

        /** @var IDataBaseRetrieveRegularOrders|MockInterface $iDataBaseRetrieveRegularOrders */
        $iDataBaseRetrieveRegularOrders = Mockery::mock(IDataBaseRetrieveRegularOrders::class);

        $regularOrderRetrievalArgs = [
            'lastOrderId' => $lastOrderId = 10,
            'count' => $count = 10,
            'operator' => $operator = $this->faker->randomElement(['<=', '>=', '=', '<>', '<', '>']),
            'timeConsumption' => $timeConsumption = $this->faker->numberBetween(1000000, 10000000),
            'user' => $dsUser,
            'db' => $iDataBaseRetrieveRegularOrders,
        ];

        $indexArgs = [
            'priceOtherwiseTime' => false,
            'timeConsumption' => $timeConsumption,
            'operator' => $operator,
            'lastOrderId' => $lastOrderId,
            'count' => $count,
        ];

        /** @var RegularOrderRetrieval|MockInterface $regularOrderRetrieval */
        $regularOrderRetrieval = Mockery::mock(RegularOrderRetrieval::class);
        $regularOrderRetrieval
            ->shouldReceive('getRegularOrdersByTimeConsumption')
            ->with(...array_values($regularOrderRetrievalArgs))
            ->andReturn($dsOrders);

        /** @var CheckAuthentication|MockInterface $checkAuthentication */
        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication
            ->shouldReceive('getAuthenticatedDSUser')
            ->andReturn($dsUser);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'checkAuthentication' => $checkAuthentication,
            'regularOrderRetrieval' => $regularOrderRetrieval,
            'iDataBaseRetrieveRegularOrders' => $iDataBaseRetrieveRegularOrders,
        ];
        $response = (new OrdersController(...$controllerArgs))->regularIndex(...$indexArgs);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertNotCount(0, $response->original);
    }

    public function testLaserShow(): void
    {
        $businessName = 'laser';

        $user = $this->getAuthenticatable('admin');
        $dsUser = $user->getDataStructure();

        /** @var PatientRole $otherUser */
        $otherUser = $this->getAuthenticatable('patient');
        $dsOtherUser = $otherUser->getDataStructure();

        $accountId = $otherUser->{$otherUser->getKeyName()};

        /** @var Order $order */
        foreach ($orders = $otherUser->user->orders as $order) {
            /** @var LaserOrder $otherUserLaserOrder */
            if (($otherUserLaserOrder = $order->laserOrder) !== null) {
                $found = true;
                break;
            }
        }
        if (!isset($found)) {
            throw new \RuntimeException('Failure!!!', 500);
        }

        $laserOrderId = $otherUserLaserOrder->{$otherUserLaserOrder->getKeyName()};

        /** @var CheckAuthentication|MockInterface $checkAuthentication */
        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication
            ->shouldReceive('getAuthenticatedDSUser')
            ->andReturn($dsUser);

        /** @var IDataBaseRetrieveLaserOrders|MockInterface $iDataBaseRetrieveLaserOrders */
        $iDataBaseRetrieveLaserOrders = Mockery::mock(IDataBaseRetrieveLaserOrders::class);

        $dsOrders = new DSLaserOrders($dsOtherUser);
        $dsOrders[] = $otherUserLaserOrder->getDSLaserOrder();

        /** @var LaserOrderRetrieval|MockInterface $laserOrderRetrieval */
        $laserOrderRetrieval = Mockery::mock(LaserOrderRetrieval::class);
        $laserOrderRetrieval
            ->shouldReceive('getLaserOrdersByUser')
            ->with(
                Mockery::on(function (DSUser $value) use ($dsOtherUser) {
                    if ($value->getUsername() !== $dsOtherUser->getUsername()) {
                        return false;
                    }
                    return true;
                }),
                Mockery::on(function (DSUser $value) use ($dsUser) {
                    if ($value->getUsername() !== $dsUser->getUsername()) {
                        return false;
                    }
                    return true;
                }),
                $iDataBaseRetrieveLaserOrders
            )
            ->andReturn($dsOrders);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'checkAuthentication' => $checkAuthentication,
            'iDataBaseRetrieveLaserOrders' => $iDataBaseRetrieveLaserOrders,
            'laserOrderRetrieval' => $laserOrderRetrieval,
        ];

        $response = (new OrdersController(...$controllerArgs))->show($businessName, $accountId, $laserOrderId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);

        $this->assertEquals($laserOrderId, $response->original[$otherUserLaserOrder->getKeyName()]);
        $this->assertEquals(
            $accountId,
            LaserOrder::query()
                ->whereKey($response->original[$otherUserLaserOrder->getKeyName()])
                ->first()
                ->order()
                ->first()
                ->user()
                ->first()
                ->{$user->getKeyName()}
        );
    }

    public function testRegularShow(): void
    {
        $businessName = 'regular';

        $user = $this->getAuthenticatable('admin');
        $dsUser = $user->getDataStructure();

        /** @var PatientRole $otherUser */
        $otherUser = $this->getAuthenticatable('patient');
        $dsOtherUser = $otherUser->getDataStructure();

        $accountId = $otherUser->{$otherUser->getKeyName()};

        /** @var Order $order */
        foreach ($orders = $otherUser->user->orders as $order) {
            /** @var RegularOrder $otherUserRegularOrder */
            if (($otherUserRegularOrder = $order->regularOrder) !== null) {
                $found = true;
                break;
            }
        }
        if (!isset($found)) {
            throw new \RuntimeException('Failure!!!', 500);
        }

        $regularOrderId = $otherUserRegularOrder->{$otherUserRegularOrder->getKeyName()};

        /** @var CheckAuthentication|MockInterface $checkAuthentication */
        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication
            ->shouldReceive('getAuthenticatedDSUser')
            ->andReturn($dsUser);

        /** @var IDataBaseRetrieveRegularOrders|MockInterface $iDataBaseRetrieveRegularOrders */
        $iDataBaseRetrieveRegularOrders = Mockery::mock(IDataBaseRetrieveRegularOrders::class);

        $dsOrders = new DSRegularOrders($dsOtherUser);
        $dsOrders[] = $otherUserRegularOrder->getDSRegularOrder();

        /** @var RegularOrderRetrieval|MockInterface $regularOrderRetrieval */
        $regularOrderRetrieval = Mockery::mock(RegularOrderRetrieval::class);
        $regularOrderRetrieval
            ->shouldReceive('getRegularOrdersByUser')
            ->with(
                Mockery::on(function (DSUser $value) use ($dsOtherUser) {
                    if ($value->getUsername() !== $dsOtherUser->getUsername()) {
                        return false;
                    }
                    return true;
                }),
                Mockery::on(function (DSUser $value) use ($dsUser) {
                    if ($value->getUsername() !== $dsUser->getUsername()) {
                        return false;
                    }
                    return true;
                }),
                $iDataBaseRetrieveRegularOrders
            )
            ->andReturn($dsOrders);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'checkAuthentication' => $checkAuthentication,
            'iDataBaseRetrieveRegularOrders' => $iDataBaseRetrieveRegularOrders,
            'regularOrderRetrieval' => $regularOrderRetrieval,
        ];

        $response = (new OrdersController(...$controllerArgs))->show($businessName, $accountId, $regularOrderId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);

        $this->assertEquals($regularOrderId, $response->original[$otherUserRegularOrder->getKeyName()]);
        $this->assertEquals(
            $accountId,
            RegularOrder::query()
                ->whereKey($response->original[$otherUserRegularOrder->getKeyName()])
                ->first()
                ->order()
                ->first()
                ->user()
                ->first()
                ->{$user->getKeyName()}
        );
    }

    public function testLaserStore()
    {
        $user = $this->getAuthenticatable('admin');
        $dsUser = $user->getDataStructure();

        $otherUser = $this->getAuthenticatable('patient');
        $dsOtherUser = $otherUser->getDataStructure();

        $gender = $otherUser->user->gender;

        $partsName = $this->faker->randomElements(
            Arr::flatten(Part::query()->where('gender', '=', $gender)->get(['name'])->toArray()),
            $this->faker->numberBetween(1, 5)
        );
        $parts = Part::query()->whereIn('name', $partsName)->get()->all();

        $packagesName = $this->faker->randomElements(
            Arr::flatten(Package::query()->where('gender', '=', $gender)->get(['name'])->toArray()),
            $this->faker->numberBetween(1, 3)
        );
        $packages = Package::query()->whereIn('name', $packagesName)->get()->all();

        /** @var CheckAuthentication|MockInterface $checkAuthentication */
        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication
            ->shouldReceive('getAuthenticatedDSUser')
            ->andReturn($dsUser);

        /** @var IDataBaseCreateLaserOrder|MockInterface $iDataBaseCreateLaserOrder */
        $iDataBaseCreateLaserOrder = Mockery::mock(IDataBaseCreateLaserOrder::class);

        /** @var Request|MockInterface $request */
        $request = Mockery::mock(Request::class);
        $request->businessName = 'laser';
        $request->accountId = $otherUser->{$otherUser->getKeyName()};
        $request->parts = $partsName;
        $request->packages = $packagesName;
        $request->price = $this->faker->numberBetween(10000000, 30000000);
        $request->timeConsumption = $this->faker->numberBetween(60, 5400);

        /** @var DSLaserOrder|MockInterface $dsOrder */
        $dsOrder = Mockery::mock(DSLaserOrder::class);
        $dsOrder
            ->shouldReceive('toArray')
            ->andReturn([]);

        /** @var LaserOrderCreation|MockInterface $laserOrderCreation */
        $laserOrderCreation = Mockery::mock(LaserOrderCreation::class);
        $laserOrderCreation
            ->shouldReceive('createLaserOrder')
            ->with(
                Mockery::on(function (DSUser $value) use ($dsOtherUser) {
                    if ($value->getUsername() !== $dsOtherUser->getUsername()) {
                        return false;
                    }
                    return true;
                }),
                Mockery::on(function (DSUser $value) use ($dsUser) {
                    if ($value->getUsername() !== $dsUser->getUsername()) {
                        return false;
                    }
                    return true;
                }),
                Mockery::on(function (IDataBaseCreateLaserOrder $iDataBaseCreateLaserOrder) {
                    $this->assertInstanceOf(IDataBaseCreateLaserOrder::class, $iDataBaseCreateLaserOrder);
                    return true;
                }),
                Mockery::on(function (DSParts $parts) use ($partsName) {
                    try {
                        $this->assertCount(Count($partsName), $parts);
                        /** @var DSPart $part */
                        foreach ($parts as $part) {
                            $this->assertInstanceOf(DSPart::class, $part);
                            $this->assertNotFalse(array_search($part->getName(), $partsName));
                        }
                        return true;
                    } catch (\Throwable $th) {
                        return false;
                    }
                }),
                Mockery::on(function (DSPackages $packages) use ($packagesName) {
                    try {
                        $this->assertCount(Count($packagesName), $packages);
                        /** @var DSPackage $package */
                        foreach ($packages as $package) {
                            $this->assertInstanceOf(DSPackage::class, $package);
                            $this->assertNotFalse(array_search($package->getName(), $packagesName));
                        }
                        return true;
                    } catch (\Throwable $th) {
                        return false;
                    }
                })
            )
            ->andReturn($dsOrder);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'checkAuthentication' => $checkAuthentication,
            'laserOrderCreation' => $laserOrderCreation,
            'iDataBaseCreateLaserOrder' => $iDataBaseCreateLaserOrder
        ];

        $response = (new OrdersController(...$controllerArgs))->store($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertCount(0, $response->original);
    }

    public function testRegularStore()
    {
        $user = $this->getAuthenticatable('admin');
        $dsUser = $user->getDataStructure();

        $otherUser = $this->getAuthenticatable('patient');
        $dsOtherUser = $otherUser->getDataStructure();

        $partsName = $this->faker->randomElements(
            Arr::flatten(Part::query()->get(['name'])->toArray()),
            $this->faker->numberBetween(1, 5)
        );
        $parts = Part::query()->whereIn('name', $partsName)->get()->all();

        $packagesName = $this->faker->randomElements(
            Arr::flatten(Package::query()->get(['name'])->toArray()),
            $this->faker->numberBetween(1, 3)
        );
        $packages = Package::query()->whereIn('name', $packagesName)->get()->all();

        /** @var CheckAuthentication|MockInterface $checkAuthentication */
        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication
            ->shouldReceive('getAuthenticatedDSUser')
            ->andReturn($dsUser);

        /** @var IDataBaseCreateRegularOrder|MockInterface $iDataBaseCreateRegularOrder */
        $iDataBaseCreateRegularOrder = Mockery::mock(IDataBaseCreateRegularOrder::class);

        /** @var Request|MockInterface $request */
        $request = Mockery::mock(Request::class);
        $request->businessName = 'regular';
        $request->accountId = $otherUser->{$otherUser->getKeyName()};
        $request->parts = $partsName;
        $request->packages = $packagesName;
        $request->price = $price = $this->faker->numberBetween(10000000, 30000000);
        $request->timeConsumption = $timeConsumption = $this->faker->numberBetween(60, 5400);

        /** @var DSRegularOrder|MockInterface $dsOrder */
        $dsOrder = Mockery::mock(DSRegularOrder::class);
        $dsOrder
            ->shouldReceive('toArray')
            ->andReturn([]);

        /** @var RegularOrderCreation|MockInterface $regularOrderCreation */
        $regularOrderCreation = Mockery::mock(RegularOrderCreation::class);
        $regularOrderCreation
            ->shouldReceive('createRegularOrder')
            ->with(
                $price,
                $timeConsumption,
                Mockery::on(function (DSUser $value) use ($dsOtherUser) {
                    if ($value->getUsername() !== $dsOtherUser->getUsername()) {
                        return false;
                    }
                    return true;
                }),
                Mockery::on(function (DSAdmin $value) use ($dsUser) {
                    if ($value->getUsername() !== $dsUser->getUsername()) {
                        return false;
                    }
                    return true;
                }),
                Mockery::on(function (IDataBaseCreateRegularOrder $iDataBaseCreateRegularOrder) {
                    $this->assertInstanceOf(IDataBaseCreateRegularOrder::class, $iDataBaseCreateRegularOrder);
                    return true;
                })
            )
            ->andReturn($dsOrder);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'checkAuthentication' => $checkAuthentication,
            'regularOrderCreation' => $regularOrderCreation,
            'iDataBaseCreateRegularOrder' => $iDataBaseCreateRegularOrder
        ];

        $response = (new OrdersController(...$controllerArgs))->store($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertCount(0, $response->original);
    }

    public function testDefaultRegularStore()
    {
        $user = $this->getAuthenticatable('patient');
        $dsUser = $user->getDataStructure();

        $otherUser = $this->getAuthenticatable('patient');
        $dsOtherUser = $otherUser->getDataStructure();

        $partsName = $this->faker->randomElements(
            Arr::flatten(Part::query()->get(['name'])->toArray()),
            $this->faker->numberBetween(1, 5)
        );
        $parts = Part::query()->whereIn('name', $partsName)->get()->all();

        $packagesName = $this->faker->randomElements(
            Arr::flatten(Package::query()->get(['name'])->toArray()),
            $this->faker->numberBetween(1, 3)
        );
        $packages = Package::query()->whereIn('name', $packagesName)->get()->all();

        /** @var CheckAuthentication|MockInterface $checkAuthentication */
        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication
            ->shouldReceive('getAuthenticatedDSUser')
            ->andReturn($dsUser);

        /** @var IDataBaseCreateDefaultRegularOrder|MockInterface $iDataBaseCreateDefaultRegularOrder */
        $iDataBaseCreateDefaultRegularOrder = Mockery::mock(IDataBaseCreateDefaultRegularOrder::class);

        /** @var Request|MockInterface $request */
        $request = Mockery::mock(Request::class);
        $request->businessName = 'regular';
        $request->accountId = $otherUser->{$otherUser->getKeyName()};
        $request->parts = $partsName;
        $request->packages = $packagesName;
        $request->price = $price = $this->faker->numberBetween(10000000, 30000000);
        $request->timeConsumption = $timeConsumption = $this->faker->numberBetween(60, 5400);

        /** @var DSRegularOrder|MockInterface $dsOrder */
        $dsOrder = Mockery::mock(DSRegularOrder::class);
        $dsOrder
            ->shouldReceive('toArray')
            ->andReturn([]);

        /** @var RegularOrderCreation|MockInterface $regularOrderCreation */
        $regularOrderCreation = Mockery::mock(RegularOrderCreation::class);
        $regularOrderCreation
            ->shouldReceive('createDefaultRegularOrder')
            ->with(
                Mockery::on(function (DSUser $value) use ($dsOtherUser) {
                    if ($value->getUsername() !== $dsOtherUser->getUsername()) {
                        return false;
                    }
                    return true;
                }),
                Mockery::on(function (DSUser $value) use ($dsUser) {
                    if ($value->getUsername() !== $dsUser->getUsername()) {
                        return false;
                    }
                    return true;
                }),
                Mockery::on(function (IDataBaseCreateDefaultRegularOrder $iDataBaseCreateDefaultRegularOrder) {
                    $this->assertInstanceOf(IDataBaseCreateDefaultRegularOrder::class, $iDataBaseCreateDefaultRegularOrder);
                    return true;
                })
            )
            ->andReturn($dsOrder);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'checkAuthentication' => $checkAuthentication,
            'regularOrderCreation' => $regularOrderCreation,
            'iDataBaseCreateDefaultRegularOrder' => $iDataBaseCreateDefaultRegularOrder
        ];

        $response = (new OrdersController(...$controllerArgs))->store($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertCount(0, $response->original);
    }
}
