<?php

namespace Tests\Unit\app\Http\Controller;

use App\Auth\CheckAuthentication;
use App\DataStructures\Order\DSPackages;
use App\DataStructures\Order\DSParts;
use App\Http\Controllers\Orders\OrdersController;
use App\Http\Requests\Orders\IndexRequest;
use App\Http\Requests\Orders\StoreRequest;
use App\Models\Order\LaserOrder;
use App\Models\Order\RegularOrder;
use App\Models\User;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use App\Http\Requests\Orders\CalculatePartsAndPackagesRquest;
use App\Http\Requests\Orders\DestroyRequest;
use App\PoliciesLogic\Order\ICalculateLaserOrder;
use App\PoliciesLogic\Order\Laser\ILaserPriceCalculator;
use App\PoliciesLogic\Order\Laser\ILaserTimeConsumptionCalculator;
use Database\Interactions\Accounts\Interfaces\IDataBaseRetrieveAccounts;
use \Database\Interactions\Orders\Interfaces\IDataBaseCreateDefaultRegularOrder;
use \Database\Interactions\Orders\Interfaces\IDataBaseCreateLaserOrder;
use \Database\Interactions\Orders\Interfaces\IDataBaseDeleteLaserOrder;
use \Database\Interactions\Orders\Interfaces\IDataBaseDeleteRegularOrder;
use \Database\Interactions\Orders\Interfaces\IDataBaseRetrieveLaserOrders;
use \Database\Interactions\Orders\Interfaces\IDataBaseRetrieveRegularOrders;

/**
 * @covers \App\Http\Controllers\Orders\OrdersController
 */
class OrdersControllerTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    // Laser

    public function testLaserIndex(): void
    {
        /** @var IndexRequest|MockInterface $request */
        $request = Mockery::mock(IndexRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn($args = ['roleName' => 'patient', 'count' => 10, 'lastOrderId' => 20]);

        /** @var IDataBaseRetrieveLaserOrders|MockInterface $iDataBaseRetrieveLaserOrders */
        $iDataBaseRetrieveLaserOrders = Mockery::mock(IDataBaseRetrieveLaserOrders::class);
        $iDataBaseRetrieveLaserOrders->shouldReceive('getLaserOrders')->once()->with($args['roleName'], $args['count'], $args['lastOrderId'])->andReturn(['orders']);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'iDataBaseRetrieveLaserOrders' => $iDataBaseRetrieveLaserOrders,
        ];

        $response = (new OrdersController(...$controllerArgs))->laserIndex($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('orders', $response->original[0]);
    }

    public function testLaserIndexByUser(): void
    {
        /** @var IndexRequest|MockInterface $request */
        $request = Mockery::mock(IndexRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn(['username' => 'username']);

        /** @var User|MockInterface $user */
        $user = Mockery::mock(User::class);

        /** @var IDataBaseRetrieveAccounts|MockInterface $iDataBaseRetrieveAccounts */
        $iDataBaseRetrieveAccounts = Mockery::mock(IDataBaseRetrieveAccounts::class);
        $iDataBaseRetrieveAccounts->shouldReceive('getAccount')->once()->with('username')->andReturn($user);

        /** @var IDataBaseRetrieveLaserOrders|MockInterface $iDataBaseRetrieveLaserOrders */
        $iDataBaseRetrieveLaserOrders = Mockery::mock(IDataBaseRetrieveLaserOrders::class);
        $iDataBaseRetrieveLaserOrders->shouldReceive('getLaserOrdersByUser')->once()->with($user)->andReturn(['orders']);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'iDataBaseRetrieveAccounts' => $iDataBaseRetrieveAccounts,
            'iDataBaseRetrieveLaserOrders' => $iDataBaseRetrieveLaserOrders,
        ];

        $response = (new OrdersController(...$controllerArgs))->laserIndex($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('orders', $response->original[0]);
    }

    public function testLaserIndexByPriceByUser(): void
    {
        /** @var IndexRequest|MockInterface $request */
        $request = Mockery::mock(IndexRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn($args = ['username' => 'username', 'priceOtherwiseTime' => true, 'operator' => 'desc', 'price' => 10]);

        /** @var User|MockInterface $user */
        $user = Mockery::mock(User::class);

        /** @var IDataBaseRetrieveAccounts|MockInterface $iDataBaseRetrieveAccounts */
        $iDataBaseRetrieveAccounts = Mockery::mock(IDataBaseRetrieveAccounts::class);
        $iDataBaseRetrieveAccounts->shouldReceive('getAccount')->once()->with('username')->andReturn($user);

        /** @var IDataBaseRetrieveLaserOrders|MockInterface $iDataBaseRetrieveLaserOrders */
        $iDataBaseRetrieveLaserOrders = Mockery::mock(IDataBaseRetrieveLaserOrders::class);
        $iDataBaseRetrieveLaserOrders->shouldReceive('getLaserOrdersByPriceByUser')->once()->with($args['operator'], $args['price'], $user)->andReturn(['orders']);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'iDataBaseRetrieveAccounts' => $iDataBaseRetrieveAccounts,
            'iDataBaseRetrieveLaserOrders' => $iDataBaseRetrieveLaserOrders,
        ];

        $response = (new OrdersController(...$controllerArgs))->laserIndex($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('orders', $response->original[0]);
    }

    public function testLaserIndexByPrice(): void
    {
        /** @var IndexRequest|MockInterface $request */
        $request = Mockery::mock(IndexRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn($args = ['roleName' => 'patient', 'count' => 10, 'lastOrderId' => 20, 'priceOtherwiseTime' => true, 'operator' => '<', 'price' => 10000]);

        /** @var IDataBaseRetrieveLaserOrders|MockInterface $iDataBaseRetrieveLaserOrders */
        $iDataBaseRetrieveLaserOrders = Mockery::mock(IDataBaseRetrieveLaserOrders::class);
        $iDataBaseRetrieveLaserOrders->shouldReceive('getLaserOrdersByPrice')->once()->with($args['roleName'], $args['lastOrderId'], $args['count'], $args['operator'], $args['price'])->andReturn(['orders']);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'iDataBaseRetrieveLaserOrders' => $iDataBaseRetrieveLaserOrders,
        ];

        $response = (new OrdersController(...$controllerArgs))->laserIndex($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('orders', $response->original[0]);
    }

    public function testLaserIndexByTimeConsumptionByUser(): void
    {
        /** @var IndexRequest|MockInterface $request */
        $request = Mockery::mock(IndexRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn($args = ['username' => 'username', 'priceOtherwiseTime' => false, 'operator' => '>=', 'timeConsumption' => 3600]);

        /** @var User|MockInterface $user */
        $user = Mockery::mock(User::class);

        /** @var IDataBaseRetrieveAccounts|MockInterface $iDataBaseRetrieveAccounts */
        $iDataBaseRetrieveAccounts = Mockery::mock(IDataBaseRetrieveAccounts::class);
        $iDataBaseRetrieveAccounts->shouldReceive('getAccount')->once()->with('username')->andReturn($user);

        /** @var IDataBaseRetrieveLaserOrders|MockInterface $iDataBaseRetrieveLaserOrders */
        $iDataBaseRetrieveLaserOrders = Mockery::mock(IDataBaseRetrieveLaserOrders::class);
        $iDataBaseRetrieveLaserOrders->shouldReceive('getLaserOrdersByTimeConsumptionByUser')->once()->with($args['operator'], $args['timeConsumption'], $user)->andReturn(['orders']);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'iDataBaseRetrieveAccounts' => $iDataBaseRetrieveAccounts,
            'iDataBaseRetrieveLaserOrders' => $iDataBaseRetrieveLaserOrders,
        ];

        $response = (new OrdersController(...$controllerArgs))->laserIndex($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('orders', $response->original[0]);
    }

    public function testLaserIndexByTimeConsumption(): void
    {
        /** @var IndexRequest|MockInterface $request */
        $request = Mockery::mock(IndexRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn($args = ['roleName' => 'patient', 'count' => 10, 'lastOrderId' => 20, 'priceOtherwiseTime' => false, 'operator' => '>=', 'timeConsumption' => 3600]);

        /** @var IDataBaseRetrieveLaserOrders|MockInterface $iDataBaseRetrieveLaserOrders */
        $iDataBaseRetrieveLaserOrders = Mockery::mock(IDataBaseRetrieveLaserOrders::class);
        $iDataBaseRetrieveLaserOrders->shouldReceive('getLaserOrdersByTimeConsumption')->once()->with($args['roleName'], $args['count'], $args['operator'], $args['timeConsumption'], $args['lastOrderId'])->andReturn(['orders']);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'iDataBaseRetrieveLaserOrders' => $iDataBaseRetrieveLaserOrders,
        ];

        $response = (new OrdersController(...$controllerArgs))->laserIndex($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('orders', $response->original[0]);
    }

    // Regular

    public function testRegularIndex(): void
    {
        /** @var IndexRequest|MockInterface $request */
        $request = Mockery::mock(IndexRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn($args = ['roleName' => 'patient', 'count' => 10, 'lastOrderId' => 20]);

        /** @var IDataBaseRetrieveRegularOrders|MockInterface $iDataBaseRetrieveRegularOrders */
        $iDataBaseRetrieveRegularOrders = Mockery::mock(IDataBaseRetrieveRegularOrders::class);
        $iDataBaseRetrieveRegularOrders->shouldReceive('getRegularOrders')->once()->with($args['roleName'], $args['count'], $args['lastOrderId'])->andReturn(['orders']);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'iDataBaseRetrieveRegularOrders' => $iDataBaseRetrieveRegularOrders,
        ];

        $response = (new OrdersController(...$controllerArgs))->regularIndex($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('orders', $response->original[0]);
    }

    public function testRegularIndexByUser(): void
    {
        /** @var IndexRequest|MockInterface $request */
        $request = Mockery::mock(IndexRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn(['username' => 'username']);

        /** @var User|MockInterface $user */
        $user = Mockery::mock(User::class);

        /** @var IDataBaseRetrieveAccounts|MockInterface $iDataBaseRetrieveAccounts */
        $iDataBaseRetrieveAccounts = Mockery::mock(IDataBaseRetrieveAccounts::class);
        $iDataBaseRetrieveAccounts->shouldReceive('getAccount')->once()->with('username')->andReturn($user);

        /** @var IDataBaseRetrieveRegularOrders|MockInterface $iDataBaseRetrieveRegularOrders */
        $iDataBaseRetrieveRegularOrders = Mockery::mock(IDataBaseRetrieveRegularOrders::class);
        $iDataBaseRetrieveRegularOrders->shouldReceive('getRegularOrdersByUser')->once()->with($user)->andReturn(['orders']);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'iDataBaseRetrieveAccounts' => $iDataBaseRetrieveAccounts,
            'iDataBaseRetrieveRegularOrders' => $iDataBaseRetrieveRegularOrders,
        ];

        $response = (new OrdersController(...$controllerArgs))->regularIndex($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('orders', $response->original[0]);
    }

    public function testRegularIndexByPriceByUser(): void
    {
        /** @var IndexRequest|MockInterface $request */
        $request = Mockery::mock(IndexRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn($args = ['username' => 'username', 'priceOtherwiseTime' => true, 'operator' => 'desc', 'price' => 10]);

        /** @var User|MockInterface $user */
        $user = Mockery::mock(User::class);

        /** @var IDataBaseRetrieveAccounts|MockInterface $iDataBaseRetrieveAccounts */
        $iDataBaseRetrieveAccounts = Mockery::mock(IDataBaseRetrieveAccounts::class);
        $iDataBaseRetrieveAccounts->shouldReceive('getAccount')->once()->with('username')->andReturn($user);

        /** @var IDataBaseRetrieveRegularOrders|MockInterface $iDataBaseRetrieveRegularOrders */
        $iDataBaseRetrieveRegularOrders = Mockery::mock(IDataBaseRetrieveRegularOrders::class);
        $iDataBaseRetrieveRegularOrders->shouldReceive('getRegularOrdersByPriceByUser')->once()->with($args['operator'], $args['price'], $user)->andReturn(['orders']);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'iDataBaseRetrieveAccounts' => $iDataBaseRetrieveAccounts,
            'iDataBaseRetrieveRegularOrders' => $iDataBaseRetrieveRegularOrders,
        ];

        $response = (new OrdersController(...$controllerArgs))->regularIndex($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('orders', $response->original[0]);
    }

    public function testRegularIndexByPrice(): void
    {
        /** @var IndexRequest|MockInterface $request */
        $request = Mockery::mock(IndexRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn($args = ['roleName' => 'patient', 'count' => 10, 'lastOrderId' => 20, 'priceOtherwiseTime' => true, 'operator' => '<', 'price' => 10000]);

        /** @var IDataBaseRetrieveRegularOrders|MockInterface $iDataBaseRetrieveRegularOrders */
        $iDataBaseRetrieveRegularOrders = Mockery::mock(IDataBaseRetrieveRegularOrders::class);
        $iDataBaseRetrieveRegularOrders->shouldReceive('getRegularOrdersByPrice')->once()->with($args['roleName'], $args['lastOrderId'], $args['count'], $args['operator'], $args['price'])->andReturn(['orders']);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'iDataBaseRetrieveRegularOrders' => $iDataBaseRetrieveRegularOrders,
        ];

        $response = (new OrdersController(...$controllerArgs))->regularIndex($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('orders', $response->original[0]);
    }

    public function testRegularIndexByTimeConsumptionByUser(): void
    {
        /** @var IndexRequest|MockInterface $request */
        $request = Mockery::mock(IndexRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn($args = ['username' => 'username', 'priceOtherwiseTime' => false, 'operator' => '>=', 'timeConsumption' => 3600]);

        /** @var User|MockInterface $user */
        $user = Mockery::mock(User::class);

        /** @var IDataBaseRetrieveAccounts|MockInterface $iDataBaseRetrieveAccounts */
        $iDataBaseRetrieveAccounts = Mockery::mock(IDataBaseRetrieveAccounts::class);
        $iDataBaseRetrieveAccounts->shouldReceive('getAccount')->once()->with('username')->andReturn($user);

        /** @var IDataBaseRetrieveRegularOrders|MockInterface $iDataBaseRetrieveRegularOrders */
        $iDataBaseRetrieveRegularOrders = Mockery::mock(IDataBaseRetrieveRegularOrders::class);
        $iDataBaseRetrieveRegularOrders->shouldReceive('getRegularOrdersByTimeConsumptionByUser')->once()->with($args['operator'], $args['timeConsumption'], $user)->andReturn(['orders']);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'iDataBaseRetrieveAccounts' => $iDataBaseRetrieveAccounts,
            'iDataBaseRetrieveRegularOrders' => $iDataBaseRetrieveRegularOrders,
        ];

        $response = (new OrdersController(...$controllerArgs))->regularIndex($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('orders', $response->original[0]);
    }

    public function testRegularIndexByTimeConsumption(): void
    {
        /** @var IndexRequest|MockInterface $request */
        $request = Mockery::mock(IndexRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn($args = ['roleName' => 'patient', 'count' => 10, 'lastOrderId' => 20, 'priceOtherwiseTime' => false, 'operator' => '>=', 'timeConsumption' => 3600]);

        /** @var IDataBaseRetrieveRegularOrders|MockInterface $iDataBaseRetrieveRegularOrders */
        $iDataBaseRetrieveRegularOrders = Mockery::mock(IDataBaseRetrieveRegularOrders::class);
        $iDataBaseRetrieveRegularOrders->shouldReceive('getRegularOrdersByTimeConsumption')->once()->with($args['roleName'], $args['count'], $args['operator'], $args['timeConsumption'], $args['lastOrderId'])->andReturn(['orders']);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'iDataBaseRetrieveRegularOrders' => $iDataBaseRetrieveRegularOrders,
        ];

        $response = (new OrdersController(...$controllerArgs))->regularIndex($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('orders', $response->original[0]);
    }

    // --------------------------------------------------------------------------------------------------------------------------------------------

    public function testOrdersCount(): void
    {
        $this->markTestIncomplete();
    }

    public function testLaserStore(): void
    {
        $gender = $this->faker->randomElement(['Male', 'Female']);

        /** @var User|MockInterface $user */
        $user = Mockery::mock(User::class);
        $user->shouldReceive('__get')->andReturn($user);
        $user->shouldReceive('__set');
        $user->shouldReceive('setAttribute');
        $user->shouldReceive('getAttribute')->with('authenticatableRole')->andReturn($user);
        $user->shouldReceive('getAttribute')->with('role')->andReturn($user);
        $user->shouldReceive('getAttribute')->with('roleName')->andReturn($user);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('');
        $user->shouldReceive('getAttribute')->with('privilegesSubjects')->andReturn([]);
        $user->shouldReceive('getAttribute')->with('gender')->andReturn($gender);
        $user->shouldReceive('getAttribute');
        $user->shouldReceive('getKey')->andReturn($this->faker->numberBetween(1, 100));

        $partNames = $this->randomArray($this->faker->numberBetween(1, 5));
        $packageNames = $this->randomArray($this->faker->numberBetween(1, 3));

        /** @var CheckAuthentication|MockInterface $checkAuthentication */
        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication->shouldReceive('getAuthenticated')->andReturn($user);

        /** @var DSParts|MockInterface $dsParts */
        $dsParts = Mockery::mock(DSParts::class);
        $dsParts->shouldReceive('getGender')->andReturn($gender);
        /** @var DSPackages|MockInterface $dsPackages */
        $dsPackages = Mockery::mock(DSPackages::class);
        $dsPackages->shouldReceive('getGender')->andReturn($gender);

        /** @var IDataBaseRetrieveLaserOrders|MockInterface $iDataBaseRetrieveLaserOrders */
        $iDataBaseRetrieveLaserOrders = Mockery::mock(IDataBaseRetrieveLaserOrders::class);
        $iDataBaseRetrieveLaserOrders->shouldReceive('collectDSPartsFromNames')->with($partNames, $gender)->andReturn($dsParts);
        $iDataBaseRetrieveLaserOrders->shouldReceive('collectDSPacakgesFromNames')->with($packageNames, $gender)->andReturn($dsPackages);

        /** @var ILaserPriceCalculator|MockInterface $iLaserPriceCalculator */
        $iLaserPriceCalculator = Mockery::mock(ILaserPriceCalculator::class);
        /** @var ILaserTimeConsumptionCalculator|MockInterface $iLaserTimeConsumptionCalculator */
        $iLaserTimeConsumptionCalculator = Mockery::mock(ILaserTimeConsumptionCalculator::class);
        /** @var ICalculateLaserOrder|MockInterface $iCalculateLaserOrder */
        $iCalculateLaserOrder = Mockery::mock(ICalculateLaserOrder::class);
        $iCalculateLaserOrder->shouldReceive('calculatePrice')->with($dsParts, $dsPackages, $iLaserPriceCalculator)->andReturn($price = $this->faker->numberBetween(10, 5000));
        $iCalculateLaserOrder->shouldReceive('calculateTimeConsumption')->with($dsParts, $dsPackages, $iLaserTimeConsumptionCalculator)->andReturn($timeConsumption = $this->faker->numberBetween(10, 5000));
        $iCalculateLaserOrder->shouldReceive('calculatePriceWithoutDiscount')->with($dsParts, $dsPackages, $iLaserPriceCalculator)->andReturn($priceWithoutDiscount = $this->faker->numberBetween(10, 5000));

        /** @var LaserOrder|MockInterface $order */
        $order = Mockery::mock(LaserOrder::class);
        $order
            ->shouldReceive('toArray')
            ->andReturn([]);

        /** @var IDataBaseCreateLaserOrder|MockInterface $iDataBaseCreateLaserOrder */
        $iDataBaseCreateLaserOrder = Mockery::mock(IDataBaseCreateLaserOrder::class);
        $iDataBaseCreateLaserOrder->shouldReceive('createLaserOrder')->with($user, $price, $timeConsumption, $priceWithoutDiscount, $dsParts, $dsPackages)->andReturn($order);

        $input = [
            'businessName' => 'laser',
            'parts' => $partNames,
            'packages' => $packageNames,
        ];
        /** @var StoreRequest|MockInterface $request */
        $request = Mockery::mock(StoreRequest::class);
        $request->shouldReceive('safe->all')->andReturn($input);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'checkAuthentication' => $checkAuthentication,
            'iDataBaseCreateLaserOrder' => $iDataBaseCreateLaserOrder,
            'iDataBaseRetrieveLaserOrders' => $iDataBaseRetrieveLaserOrders,
            'iCalculateLaserOrder' => $iCalculateLaserOrder,
            'iLaserPriceCalculator' => $iLaserPriceCalculator,
            'iLaserTimeConsumptionCalculator' => $iLaserTimeConsumptionCalculator,
        ];

        $response = (new OrdersController(...$controllerArgs))->store($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertCount(0, $response->original);
    }

    public function testDefaultRegularStore(): void
    {
        $gender = $this->faker->randomElement(['Male', 'Female']);

        /** @var User|MockInterface $user */
        $user = Mockery::mock(User::class);
        $user->shouldReceive('__get')->andReturn($user);
        $user->shouldReceive('__set');
        $user->shouldReceive('setAttribute');
        $user->shouldReceive('getAttribute')->with('authenticatableRole')->andReturn($user);
        $user->shouldReceive('getAttribute')->with('role')->andReturn($user);
        $user->shouldReceive('getAttribute')->with('roleName')->andReturn($user);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('');
        $user->shouldReceive('getAttribute')->with('privilegesSubjects')->andReturn([]);
        $user->shouldReceive('getAttribute')->with('gender')->andReturn($gender);
        $user->shouldReceive('getAttribute');
        $user->shouldReceive('getKey')->andReturn($this->faker->numberBetween(1, 100));

        /** @var CheckAuthentication|MockInterface $checkAuthentication */
        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication
            ->shouldReceive('getAuthenticated')
            ->andReturn($user);

        /** @var RegularOrder|MockInterface $order */
        $order = Mockery::mock(RegularOrder::class);
        $order
            ->shouldReceive('toArray')
            ->andReturn([]);

        /** @var IDataBaseCreateDefaultRegularOrder|MockInterface $iDataBaseCreateDefaultRegularOrder */
        $iDataBaseCreateDefaultRegularOrder = Mockery::mock(IDataBaseCreateDefaultRegularOrder::class);
        $iDataBaseCreateDefaultRegularOrder->shouldReceive('createDefaultRegularOrder')->with($user)->andReturn($order);

        $input = [
            'businessName' => 'regular',
        ];

        /** @var StoreRequest|MockInterface $request */
        $request = Mockery::mock(StoreRequest::class);
        $request->shouldReceive('safe->all')->andReturn($input);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'checkAuthentication' => $checkAuthentication,
            'iDataBaseCreateDefaultRegularOrder' => $iDataBaseCreateDefaultRegularOrder,
        ];

        $response = (new OrdersController(...$controllerArgs))->store($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertCount(0, $response->original);
    }

    public function testRegularDestroy(): void
    {
        $id = $this->faker->numberBetween(1, 100);

        /** @var RegularOrder|MockInterface $order */
        $order = Mockery::mock(RegularOrder::class);

        /** @var IDataBaseRetrieveRegularOrders|MockInterface $iDataBaseRetrieveRegularOrders */
        $iDataBaseRetrieveRegularOrders = Mockery::mock(IDataBaseRetrieveRegularOrders::class);
        $iDataBaseRetrieveRegularOrders->shouldReceive('getRegularOrderById')->once()->with($id)->andReturn($order);

        /** @var IDataBaseDeleteRegularOrder|MockInterface $iDataBaseDeleteRegularOrder */
        $iDataBaseDeleteRegularOrder = Mockery::mock(IDataBaseDeleteRegularOrder::class);
        $iDataBaseDeleteRegularOrder->shouldReceive('deleteRegularOrder')->once()->with($order);

        /** @var DestroyRequest|MockInterface $request */
        $request = Mockery::mock(DestroyRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn(['businessName' => 'regular', 'childOrderId' => $id]);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'iDataBaseDeleteRegularOrder' => $iDataBaseDeleteRegularOrder,
            'iDataBaseRetrieveRegularOrders' => $iDataBaseRetrieveRegularOrders
        ];

        $response = (new OrdersController(...$controllerArgs))->destroy($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsString($response->original);
    }

    public function testLaserDestroy(): void
    {
        $id = $this->faker->numberBetween(1, 100);

        /** @var LaserOrder|MockInterface $order */
        $order = Mockery::mock(LaserOrder::class);

        /** @var IDataBaseRetrieveLaserOrders|MockInterface $iDataBaseRetrieveLaserOrders */
        $iDataBaseRetrieveLaserOrders = Mockery::mock(IDataBaseRetrieveLaserOrders::class);
        $iDataBaseRetrieveLaserOrders->shouldReceive('getLaserOrderById')->once()->with($id)->andReturn($order);

        /** @var IDataBaseDeleteLaserOrder|MockInterface $iDataBaseDeleteLaserOrder */
        $iDataBaseDeleteLaserOrder = Mockery::mock(IDataBaseDeleteLaserOrder::class);
        $iDataBaseDeleteLaserOrder->shouldReceive('deleteLaserOrder')->once()->with($order);

        /** @var DestroyRequest|MockInterface $request */
        $request = Mockery::mock(DestroyRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn(['businessName' => 'laser', 'childOrderId' => $id]);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'iDataBaseDeleteLaserOrder' => $iDataBaseDeleteLaserOrder,
            'iDataBaseRetrieveLaserOrders' => $iDataBaseRetrieveLaserOrders
        ];

        $response = (new OrdersController(...$controllerArgs))->destroy($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsString($response->original);
    }

    public function testCalculateTime(): void
    {
        $gender = $this->faker->randomElement(['Male', 'Female']);

        /** @var User|MockInterface $user */
        $user = Mockery::mock(User::class);

        /** @var CheckAuthentication|MockInterface $checkAuthentication */
        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication->shouldReceive('getAuthenticated')->once()->andReturn($user);

        /** @var CalculatePartsAndPackagesRquest|MockInterface $request */
        $request = Mockery::mock(CalculatePartsAndPackagesRquest::class);
        $request->shouldReceive('safe->all')->once()->andReturn(['gender' => $gender, 'parts' => $partNames = $this->randomArray($this->faker->numberBetween(1, 5)), 'packages' => $packageNames = $this->randomArray($this->faker->numberBetween(1, 3))]);

        /** @var DSParts|MockInterface $dsParts */
        $dsParts = Mockery::mock(DSParts::class);
        /** @var DSPackages|MockInterface $dsPackages */
        $dsPackages = Mockery::mock(DSPackages::class);

        /** @var IDataBaseRetrieveLaserOrders|MockInterface $iDataBaseRetrieveLaserOrders */
        $iDataBaseRetrieveLaserOrders = Mockery::mock(IDataBaseRetrieveLaserOrders::class);
        $iDataBaseRetrieveLaserOrders->shouldReceive('collectDSPartsFromNames')->with($partNames, $gender)->andReturn($dsParts);
        $iDataBaseRetrieveLaserOrders->shouldReceive('collectDSPacakgesFromNames')->with($packageNames, $gender)->andReturn($dsPackages);

        /** @var ILaserTimeConsumptionCalculator|MockInterface $iLaserTimeConsumptionCalculator */
        $iLaserTimeConsumptionCalculator = Mockery::mock(ILaserTimeConsumptionCalculator::class);

        /** @var ICalculateLaserOrder|MockInterface $iCalculateLaserOrder */
        $iCalculateLaserOrder = Mockery::mock(ICalculateLaserOrder::class);
        $iCalculateLaserOrder->shouldReceive('calculateTimeConsumption')->once()->with($dsParts, $dsPackages, $iLaserTimeConsumptionCalculator)->andReturn(5);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'checkAuthentication' => $checkAuthentication,
            'iCalculateLaserOrder' => $iCalculateLaserOrder,
            'iDataBaseRetrieveLaserOrders' => $iDataBaseRetrieveLaserOrders,
            'iLaserTimeConsumptionCalculator' => $iLaserTimeConsumptionCalculator,
        ];

        $response = (new OrdersController(...$controllerArgs))->calculateTime($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsInt($response->original);
    }

    public function testCalculatePrice(): void
    {
        $gender = $this->faker->randomElement(['Male', 'Female']);

        /** @var User|MockInterface $user */
        $user = Mockery::mock(User::class);

        /** @var CheckAuthentication|MockInterface $checkAuthentication */
        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication->shouldReceive('getAuthenticated')->once()->andReturn($user);

        /** @var CalculatePartsAndPackagesRquest|MockInterface $request */
        $request = Mockery::mock(CalculatePartsAndPackagesRquest::class);
        $request->shouldReceive('safe->all')->once()->andReturn(['gender' => $gender, 'parts' => $partNames = $this->randomArray($this->faker->numberBetween(1, 5)), 'packages' => $packageNames = $this->randomArray($this->faker->numberBetween(1, 3))]);

        /** @var DSParts|MockInterface $dsParts */
        $dsParts = Mockery::mock(DSParts::class);
        /** @var DSPackages|MockInterface $dsPackages */
        $dsPackages = Mockery::mock(DSPackages::class);

        /** @var IDataBaseRetrieveLaserOrders|MockInterface $iDataBaseRetrieveLaserOrders */
        $iDataBaseRetrieveLaserOrders = Mockery::mock(IDataBaseRetrieveLaserOrders::class);
        $iDataBaseRetrieveLaserOrders->shouldReceive('collectDSPartsFromNames')->with($partNames, $gender)->andReturn($dsParts);
        $iDataBaseRetrieveLaserOrders->shouldReceive('collectDSPacakgesFromNames')->with($packageNames, $gender)->andReturn($dsPackages);

        /** @var ILaserPriceCalculator|MockInterface $iLaserPriceCalculator */
        $iLaserPriceCalculator = Mockery::mock(ILaserPriceCalculator::class);

        /** @var ICalculateLaserOrder|MockInterface $iCalculateLaserOrder */
        $iCalculateLaserOrder = Mockery::mock(ICalculateLaserOrder::class);
        $iCalculateLaserOrder->shouldReceive('calculatePrice')->once()->with($dsParts, $dsPackages, $iLaserPriceCalculator)->andReturn(5);
        $iCalculateLaserOrder->shouldReceive('calculatePriceWithoutDiscount')->once()->with($dsParts, $dsPackages, $iLaserPriceCalculator)->andReturn(5);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'checkAuthentication' => $checkAuthentication,
            'iCalculateLaserOrder' => $iCalculateLaserOrder,
            'iDataBaseRetrieveLaserOrders' => $iDataBaseRetrieveLaserOrders,
            'iLaserPriceCalculator' => $iLaserPriceCalculator,
        ];

        $response = (new OrdersController(...$controllerArgs))->calculatePrice($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertIsArray($response->original);
        $this->assertCount(2, $response->original);
        $this->assertArrayHasKey('price', $response->original);
        $this->assertEquals(5, $response->original['price']);
        $this->assertArrayHasKey('priceWithoutDiscount', $response->original);
        $this->assertEquals(5, $response->original['priceWithoutDiscount']);
    }

    private function randomArray(int $count = 5): array
    {
        $array = [];
        for ($i = 0; $i < $count; $i++) {
            $array[] = $this->faker->lexify();
        }
        return $array;
    }
}
