<?php

namespace Tests\Unit\app\Http\Controller;

use App\Auth\CheckAuthentication;
use App\DataStructures\Time\DSDateTimePeriods;
use App\Http\Controllers\Visits\VisitsController;
use App\Http\Requests\Visits\IndexRequest;
use App\Http\Requests\Visits\LaserStoreRequest;
use App\Http\Requests\Visits\RegularStoreRequest;
use Database\Interactions\Accounts\AccountsManagement;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use App\PoliciesLogic\Visit\IFindVisit;
use App\DataStructures\Time\DSWeeklyTimePatterns;
use App\Http\Requests\Visits\LaserDestroyRequest;
use App\Http\Requests\Visits\LaserShowAvailableRequest;
use App\Http\Requests\Visits\RegularDestroyRequest;
use App\Http\Requests\Visits\RegularShowAvailableRequest;
use App\Models\Order\LaserOrder;
use App\Models\Order\RegularOrder;
use App\Models\User;
use App\Models\Visit\LaserVisit;
use App\Models\Visit\RegularVisit;
use App\PoliciesLogic\Visit\CustomVisit;
use App\PoliciesLogic\Visit\FastestVisit;
use App\PoliciesLogic\Visit\WeeklyVisit;
use Database\Interactions\Accounts\Interfaces\IDataBaseRetrieveAccounts;
use Database\Interactions\Orders\Interfaces\IDataBaseRetrieveLaserOrders;
use Database\Interactions\Orders\Interfaces\IDataBaseRetrieveRegularOrders;
use Database\Interactions\Visits\Interfaces\IDataBaseCreateLaserVisit;
use Database\Interactions\Visits\Interfaces\IDataBaseCreateRegularVisit;
use Database\Interactions\Visits\Interfaces\IDataBaseDeleteLaserVisit;
use Database\Interactions\Visits\Interfaces\IDataBaseDeleteRegularVisit;
use Database\Interactions\Visits\Interfaces\IDataBaseRetrieveLaserVisits;
use Database\Interactions\Visits\Interfaces\IDataBaseRetrieveRegularVisits;
use Database\Interactions\Visits\VisitsManagement;

/**
 * @covers \App\Http\Controllers\Visits\VisitsController
 */
class VisitsControllerTest extends TestCase
{
    private Generator $faker;

    private CheckAuthentication|MockInterface $checkAuthentication;

    private AccountsManagement|MockInterface $accountsManagement;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        /** @var CheckAuthentication|\Mockery\MockInterface $checkAuthentication */
        $this->checkAuthentication = Mockery::mock(CheckAuthentication::class);

        /** @var AccountsManagement|\Mockery\MockInterface $accountsManagement */
        $this->accountsManagement = Mockery::mock(AccountsManagement::class);
    }

    public function testLaserIndexByUser(): void
    {
        $validatedInput = [
            'businessName' => 'laser',
            'accountId' => 10,
            'sortByTimestamp' => 'asc',
        ];

        /** @var IndexRequest|\Mockery\MockInterface $request */
        $request = Mockery::mock(IndexRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn($validatedInput);

        /** @var User|\Mockery\MockInterface $user */
        $user = Mockery::mock(User::class);

        $this->accountsManagement->shouldReceive('resolveUsername')->once()->with((int)$validatedInput['accountId'])->andReturn('username');

        /** @var IDataBaseRetrieveAccounts|\Mockery\MockInterface $iDataBaseRetrieveAccounts */
        $iDataBaseRetrieveAccounts = Mockery::mock(IDataBaseRetrieveAccounts::class);
        $iDataBaseRetrieveAccounts->shouldReceive('getAccount')->once()->with('username')->andReturn($user);

        /** @var IDataBaseRetrieveLaserVisits|\Mockery\MockInterface $iDataBaseRetrieveLaserVisits */
        $iDataBaseRetrieveLaserVisits = Mockery::mock(IDataBaseRetrieveLaserVisits::class);
        $iDataBaseRetrieveLaserVisits->shouldReceive('getVisitsByUser')->once()->with($user, $validatedInput['sortByTimestamp'])->andReturn(['orders']);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'accountsManagement' => $this->accountsManagement,
            'iDataBaseRetrieveAccounts' => $iDataBaseRetrieveAccounts,
            'iDataBaseRetrieveLaserVisits' => $iDataBaseRetrieveLaserVisits,
        ];

        $response = (new VisitsController(...$controllerArgs))->index($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('orders', $response->original[0]);
    }

    public function testLaserIndexByOrder(): void
    {
        $validatedInput = [
            'businessName' => 'laser',
            'orderId' => 10,
            'sortByTimestamp' => 'asc',
        ];

        /** @var IndexRequest|\Mockery\MockInterface $request */
        $request = Mockery::mock(IndexRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn($validatedInput);

        /** @var LaserOrder|\Mockery\MockInterface $order */
        $order = Mockery::mock(LaserOrder::class);

        /** @var IDataBaseRetrieveLaserOrders|\Mockery\MockInterface $iDataBaseRetrieveLaserOrders */
        $iDataBaseRetrieveLaserOrders = Mockery::mock(IDataBaseRetrieveLaserOrders::class);
        $iDataBaseRetrieveLaserOrders->shouldReceive('getLaserOrderById')->once()->with((int)$validatedInput['orderId'])->andReturn($order);

        /** @var IDataBaseRetrieveLaserVisits|\Mockery\MockInterface $iDataBaseRetrieveLaserVisits */
        $iDataBaseRetrieveLaserVisits = Mockery::mock(IDataBaseRetrieveLaserVisits::class);
        $iDataBaseRetrieveLaserVisits->shouldReceive('getVisitsByOrder')->once()->with($order, $validatedInput['sortByTimestamp'])->andReturn(['orders']);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'iDataBaseRetrieveLaserOrders' => $iDataBaseRetrieveLaserOrders,
            'iDataBaseRetrieveLaserVisits' => $iDataBaseRetrieveLaserVisits,
        ];

        $response = (new VisitsController(...$controllerArgs))->index($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('orders', $response->original[0]);
    }

    public function testLaserIndexByTimestamp(): void
    {
        $validatedInput = [
            'businessName' => 'laser',
            'roleName' => 'patient',
            'operator' => '>',
            'timestamp' => 333333333,
            'count' => 10,
            'lastVisitTimestamp' => 20,
            'sortByTimestamp' => 'asc',
        ];

        /** @var IndexRequest|\Mockery\MockInterface $request */
        $request = Mockery::mock(IndexRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn($validatedInput);

        /** @var IDataBaseRetrieveLaserVisits|\Mockery\MockInterface $iDataBaseRetrieveLaserVisits */
        $iDataBaseRetrieveLaserVisits = Mockery::mock(IDataBaseRetrieveLaserVisits::class);
        $iDataBaseRetrieveLaserVisits->shouldReceive('getVisitsByTimestamp')->once()->with(
            $validatedInput['roleName'],
            $validatedInput['operator'],
            $validatedInput['timestamp'],
            $validatedInput['sortByTimestamp'],
            $validatedInput['count'],
            $validatedInput['lastVisitTimestamp']
        )->andReturn(['orders']);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'iDataBaseRetrieveLaserVisits' => $iDataBaseRetrieveLaserVisits,
        ];

        $response = (new VisitsController(...$controllerArgs))->index($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('orders', $response->original[0]);
    }

    public function testRegularIndexByUser(): void
    {
        $validatedInput = [
            'businessName' => 'regular',
            'accountId' => 10,
            'sortByTimestamp' => 'asc',
        ];

        /** @var IndexRequest|\Mockery\MockInterface $request */
        $request = Mockery::mock(IndexRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn($validatedInput);

        /** @var User|\Mockery\MockInterface $user */
        $user = Mockery::mock(User::class);

        $this->accountsManagement->shouldReceive('resolveUsername')->once()->with((int)$validatedInput['accountId'])->andReturn('username');

        /** @var IDataBaseRetrieveAccounts|\Mockery\MockInterface $iDataBaseRetrieveAccounts */
        $iDataBaseRetrieveAccounts = Mockery::mock(IDataBaseRetrieveAccounts::class);
        $iDataBaseRetrieveAccounts->shouldReceive('getAccount')->once()->with('username')->andReturn($user);

        /** @var IDataBaseRetrieveRegularVisits|\Mockery\MockInterface $iDataBaseRetrieveRegularVisits */
        $iDataBaseRetrieveRegularVisits = Mockery::mock(IDataBaseRetrieveRegularVisits::class);
        $iDataBaseRetrieveRegularVisits->shouldReceive('getVisitsByUser')->once()->with($user, $validatedInput['sortByTimestamp'])->andReturn(['orders']);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'accountsManagement' => $this->accountsManagement,
            'iDataBaseRetrieveAccounts' => $iDataBaseRetrieveAccounts,
            'iDataBaseRetrieveRegularVisits' => $iDataBaseRetrieveRegularVisits,
        ];

        $response = (new VisitsController(...$controllerArgs))->index($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('orders', $response->original[0]);
    }

    public function testRegularIndexByOrder(): void
    {
        $validatedInput = [
            'businessName' => 'regular',
            'orderId' => 10,
            'sortByTimestamp' => 'asc',
        ];

        /** @var IndexRequest|\Mockery\MockInterface $request */
        $request = Mockery::mock(IndexRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn($validatedInput);

        /** @var RegularOrder|\Mockery\MockInterface $order */
        $order = Mockery::mock(RegularOrder::class);

        /** @var IDataBaseRetrieveRegularOrders|\Mockery\MockInterface $iDataBaseRetrieveRegularOrders */
        $iDataBaseRetrieveRegularOrders = Mockery::mock(IDataBaseRetrieveRegularOrders::class);
        $iDataBaseRetrieveRegularOrders->shouldReceive('getRegularOrderById')->once()->with((int)$validatedInput['orderId'])->andReturn($order);

        /** @var IDataBaseRetrieveRegularVisits|\Mockery\MockInterface $iDataBaseRetrieveRegularVisits */
        $iDataBaseRetrieveRegularVisits = Mockery::mock(IDataBaseRetrieveRegularVisits::class);
        $iDataBaseRetrieveRegularVisits->shouldReceive('getVisitsByOrder')->once()->with($order, $validatedInput['sortByTimestamp'])->andReturn(['orders']);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'iDataBaseRetrieveRegularOrders' => $iDataBaseRetrieveRegularOrders,
            'iDataBaseRetrieveRegularVisits' => $iDataBaseRetrieveRegularVisits,
        ];

        $response = (new VisitsController(...$controllerArgs))->index($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('orders', $response->original[0]);
    }

    public function testRegularIndexByTimestamp(): void
    {
        $validatedInput = [
            'businessName' => 'regular',
            'roleName' => 'patient',
            'operator' => '>',
            'timestamp' => 333333333,
            'count' => 10,
            'lastVisitTimestamp' => 20,
            'sortByTimestamp' => 'asc',
        ];

        /** @var IndexRequest|\Mockery\MockInterface $request */
        $request = Mockery::mock(IndexRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn($validatedInput);

        /** @var IDataBaseRetrieveRegularVisits|\Mockery\MockInterface $iDataBaseRetrieveRegularVisits */
        $iDataBaseRetrieveRegularVisits = Mockery::mock(IDataBaseRetrieveRegularVisits::class);
        $iDataBaseRetrieveRegularVisits->shouldReceive('getVisitsByTimestamp')->once()->with(
            $validatedInput['roleName'],
            $validatedInput['operator'],
            $validatedInput['timestamp'],
            $validatedInput['sortByTimestamp'],
            $validatedInput['count'],
            $validatedInput['lastVisitTimestamp']
        )->andReturn(['orders']);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'iDataBaseRetrieveRegularVisits' => $iDataBaseRetrieveRegularVisits,
        ];

        $response = (new VisitsController(...$controllerArgs))->index($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('orders', $response->original[0]);
    }

    public function testVisitsCount(): void
    {
        $this->markTestIncomplete();
    }

    public function testLaserStoreWeekDaysPeriods(): void
    {
        $validatedInput = [
            'laserOrderId' => 10,
            'weeklyTimePatterns' => ['Monday' => [['start' => '10:00:00', 'end' => '20:00:00']]],
        ];

        /** @var LaserStoreRequest|\Mockery\MockInterface $request */
        $request = Mockery::mock(LaserStoreRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn($validatedInput);

        /** @var LaserOrder|\Mockery\MockInterface $laserOrder */
        $laserOrder = Mockery::mock(LaserOrder::class);

        /** @var IDataBaseRetrieveLaserOrders|\Mockery\MockInterface $iDataBaseRetrieveLaserOrders */
        $iDataBaseRetrieveLaserOrders = Mockery::mock(IDataBaseRetrieveLaserOrders::class);
        $iDataBaseRetrieveLaserOrders->shouldReceive('getLaserOrderById')->once()->with(10)->andReturn($laserOrder);

        /** @var WeeklyVisit|\Mockery\MockInterface $weeklyVisit */
        $weeklyVisit = Mockery::mock(WeeklyVisit::class);

        /** @var VisitsManagement|\Mockery\MockInterface $visitsManagement */
        $visitsManagement = Mockery::mock(VisitsManagement::class);
        $visitsManagement->shouldReceive('getLaserVisitFinder')->once()->with($laserOrder, Mockery::on(function (object $value) {
            return $value instanceof DSWeeklyTimePatterns;
        }))->andReturn($weeklyVisit);

        /** @var LaserVisit|\Mockery\MockInterface $laserVisit */
        $laserVisit = Mockery::mock(LaserVisit::class);
        $laserVisit->shouldReceive('toArray')->once()->andReturn(['visits']);

        /** @var IDataBaseCreateLaserVisit|\Mockery\MockInterface $iDataBaseCreateLaserVisit */
        $iDataBaseCreateLaserVisit = Mockery::mock(IDataBaseCreateLaserVisit::class);
        $iDataBaseCreateLaserVisit->shouldReceive('createLaserVisit')->once()->with($laserOrder, Mockery::on(function (IFindVisit $iFindVisit) {
            return $iFindVisit instanceof WeeklyVisit;
        }))->andReturn($laserVisit);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'visitsManagement' => $visitsManagement,
            'iDataBaseRetrieveLaserOrders' => $iDataBaseRetrieveLaserOrders,
            'iDataBaseCreateLaserVisit' => $iDataBaseCreateLaserVisit,
        ];

        $response = (new VisitsController(...$controllerArgs))->laserStore($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('visits', $response->original[0]);
    }

    public function testLaserStoreDateTimePeriod(): void
    {
        $validatedInput = [
            'laserOrderId' => 10,
            'dateTimePeriod' => [['start' => '2000-01-01 10:00:00', 'end' => '2000-01-01 20:00:00']],
        ];

        /** @var LaserStoreRequest|\Mockery\MockInterface $request */
        $request = Mockery::mock(LaserStoreRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn($validatedInput);

        /** @var LaserOrder|\Mockery\MockInterface $laserOrder */
        $laserOrder = Mockery::mock(LaserOrder::class);

        /** @var IDataBaseRetrieveLaserOrders|\Mockery\MockInterface $iDataBaseRetrieveLaserOrders */
        $iDataBaseRetrieveLaserOrders = Mockery::mock(IDataBaseRetrieveLaserOrders::class);
        $iDataBaseRetrieveLaserOrders->shouldReceive('getLaserOrderById')->once()->with(10)->andReturn($laserOrder);

        /** @var CustomVisit|\Mockery\MockInterface $customVisit */
        $customVisit = Mockery::mock(CustomVisit::class);

        /** @var VisitsManagement|\Mockery\MockInterface $visitsManagement */
        $visitsManagement = Mockery::mock(VisitsManagement::class);
        $visitsManagement->shouldReceive('getLaserVisitFinder')->once()->with($laserOrder, Mockery::on(function (object $value) {
            return $value instanceof DSDateTimePeriods;
        }))->andReturn($customVisit);

        /** @var LaserVisit|\Mockery\MockInterface $laserVisit */
        $laserVisit = Mockery::mock(LaserVisit::class);
        $laserVisit->shouldReceive('toArray')->once()->andReturn(['visits']);

        /** @var IDataBaseCreateLaserVisit|\Mockery\MockInterface $iDataBaseCreateLaserVisit */
        $iDataBaseCreateLaserVisit = Mockery::mock(IDataBaseCreateLaserVisit::class);
        $iDataBaseCreateLaserVisit->shouldReceive('createLaserVisit')->once()->with($laserOrder, Mockery::on(function (IFindVisit $iFindVisit) {
            return $iFindVisit instanceof CustomVisit;
        }))->andReturn($laserVisit);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'visitsManagement' => $visitsManagement,
            'iDataBaseRetrieveLaserOrders' => $iDataBaseRetrieveLaserOrders,
            'iDataBaseCreateLaserVisit' => $iDataBaseCreateLaserVisit,
        ];

        $response = (new VisitsController(...$controllerArgs))->laserStore($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('visits', $response->original[0]);
    }

    public function testLaserStore(): void
    {
        $validatedInput = [
            'laserOrderId' => 10,
        ];

        /** @var LaserStoreRequest|\Mockery\MockInterface $request */
        $request = Mockery::mock(LaserStoreRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn($validatedInput);

        /** @var LaserOrder|\Mockery\MockInterface $laserOrder */
        $laserOrder = Mockery::mock(LaserOrder::class);

        /** @var IDataBaseRetrieveLaserOrders|\Mockery\MockInterface $iDataBaseRetrieveLaserOrders */
        $iDataBaseRetrieveLaserOrders = Mockery::mock(IDataBaseRetrieveLaserOrders::class);
        $iDataBaseRetrieveLaserOrders->shouldReceive('getLaserOrderById')->once()->with(10)->andReturn($laserOrder);

        /** @var FastestVisit|\Mockery\MockInterface $fastestVisit */
        $fastestVisit = Mockery::mock(FastestVisit::class);

        /** @var VisitsManagement|\Mockery\MockInterface $visitsManagement */
        $visitsManagement = Mockery::mock(VisitsManagement::class);
        $visitsManagement->shouldReceive('getLaserVisitFinder')->once()->with($laserOrder, null)->andReturn($fastestVisit);

        /** @var LaserVisit|\Mockery\MockInterface $laserVisit */
        $laserVisit = Mockery::mock(LaserVisit::class);
        $laserVisit->shouldReceive('toArray')->once()->andReturn(['visits']);

        /** @var IDataBaseCreateLaserVisit|\Mockery\MockInterface $iDataBaseCreateLaserVisit */
        $iDataBaseCreateLaserVisit = Mockery::mock(IDataBaseCreateLaserVisit::class);
        $iDataBaseCreateLaserVisit->shouldReceive('createLaserVisit')->once()->with($laserOrder, Mockery::on(function (IFindVisit $iFindVisit) {
            return $iFindVisit instanceof FastestVisit;
        }))->andReturn($laserVisit);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'visitsManagement' => $visitsManagement,
            'iDataBaseCreateLaserVisit' => $iDataBaseCreateLaserVisit,
            'iDataBaseRetrieveLaserOrders' => $iDataBaseRetrieveLaserOrders,
        ];

        $response = (new VisitsController(...$controllerArgs))->laserStore($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('visits', $response->original[0]);
    }

    public function testRegularStoreWeekDaysPeriods(): void
    {
        $validatedInput = [
            'regularOrderId' => 10,
            'weeklyTimePatterns' => ['Monday' => [['start' => '10:00:00', 'end' => '20:00:00']]],
        ];

        /** @var RegularStoreRequest|\Mockery\MockInterface $request */
        $request = Mockery::mock(RegularStoreRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn($validatedInput);

        /** @var RegularOrder|\Mockery\MockInterface $regularOrder */
        $regularOrder = Mockery::mock(RegularOrder::class);

        /** @var IDataBaseRetrieveRegularOrders|\Mockery\MockInterface $iDataBaseRetrieveRegularOrders */
        $iDataBaseRetrieveRegularOrders = Mockery::mock(IDataBaseRetrieveRegularOrders::class);
        $iDataBaseRetrieveRegularOrders->shouldReceive('getRegularOrderById')->once()->with(10)->andReturn($regularOrder);

        /** @var WeeklyVisit|\Mockery\MockInterface $weeklyVisit */
        $weeklyVisit = Mockery::mock(WeeklyVisit::class);

        /** @var VisitsManagement|\Mockery\MockInterface $visitsManagement */
        $visitsManagement = Mockery::mock(VisitsManagement::class);
        $visitsManagement->shouldReceive('getRegularVisitFinder')->once()->with($regularOrder, Mockery::on(function (object $value) {
            return $value instanceof DSWeeklyTimePatterns;
        }))->andReturn($weeklyVisit);

        /** @var RegularVisit|\Mockery\MockInterface $regularVisit */
        $regularVisit = Mockery::mock(RegularVisit::class);
        $regularVisit->shouldReceive('toArray')->once()->andReturn(['visits']);

        /** @var IDataBaseCreateRegularVisit|\Mockery\MockInterface $iDataBaseCreateRegularVisit */
        $iDataBaseCreateRegularVisit = Mockery::mock(IDataBaseCreateRegularVisit::class);
        $iDataBaseCreateRegularVisit->shouldReceive('createRegularVisit')->once()->with($regularOrder, Mockery::on(function (IFindVisit $iFindVisit) {
            return $iFindVisit instanceof WeeklyVisit;
        }))->andReturn($regularVisit);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'visitsManagement' => $visitsManagement,
            'iDataBaseRetrieveRegularOrders' => $iDataBaseRetrieveRegularOrders,
            'iDataBaseCreateRegularVisit' => $iDataBaseCreateRegularVisit,
        ];

        $response = (new VisitsController(...$controllerArgs))->regularStore($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('visits', $response->original[0]);
    }

    public function testRegularStoreDateTimePeriod(): void
    {
        $validatedInput = [
            'regularOrderId' => 10,
            'dateTimePeriod' => [['start' => '2000-01-01 10:00:00', 'end' => '2000-01-01 20:00:00']],
        ];

        /** @var RegularStoreRequest|\Mockery\MockInterface $request */
        $request = Mockery::mock(RegularStoreRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn($validatedInput);

        /** @var RegularOrder|\Mockery\MockInterface $regularOrder */
        $regularOrder = Mockery::mock(RegularOrder::class);

        /** @var IDataBaseRetrieveRegularOrders|\Mockery\MockInterface $iDataBaseRetrieveRegularOrders */
        $iDataBaseRetrieveRegularOrders = Mockery::mock(IDataBaseRetrieveRegularOrders::class);
        $iDataBaseRetrieveRegularOrders->shouldReceive('getRegularOrderById')->once()->with(10)->andReturn($regularOrder);

        /** @var CustomVisit|\Mockery\MockInterface $customVisit */
        $customVisit = Mockery::mock(CustomVisit::class);

        /** @var VisitsManagement|\Mockery\MockInterface $visitsManagement */
        $visitsManagement = Mockery::mock(VisitsManagement::class);
        $visitsManagement->shouldReceive('getRegularVisitFinder')->once()->with($regularOrder, Mockery::on(function (object $value) {
            return $value instanceof DSDateTimePeriods;
        }))->andReturn($customVisit);

        /** @var RegularVisit|\Mockery\MockInterface $regularVisit */
        $regularVisit = Mockery::mock(RegularVisit::class);
        $regularVisit->shouldReceive('toArray')->once()->andReturn(['visits']);

        /** @var IDataBaseCreateRegularVisit|\Mockery\MockInterface $iDataBaseCreateRegularVisit */
        $iDataBaseCreateRegularVisit = Mockery::mock(IDataBaseCreateRegularVisit::class);
        $iDataBaseCreateRegularVisit->shouldReceive('createRegularVisit')->once()->with($regularOrder, Mockery::on(function (IFindVisit $iFindVisit) {
            return $iFindVisit instanceof CustomVisit;
        }))->andReturn($regularVisit);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'visitsManagement' => $visitsManagement,
            'iDataBaseRetrieveRegularOrders' => $iDataBaseRetrieveRegularOrders,
            'iDataBaseCreateRegularVisit' => $iDataBaseCreateRegularVisit,
        ];

        $response = (new VisitsController(...$controllerArgs))->regularStore($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('visits', $response->original[0]);
    }

    public function testRegularStore(): void
    {
        $validatedInput = [
            'regularOrderId' => 10,
        ];

        /** @var RegularStoreRequest|\Mockery\MockInterface $request */
        $request = Mockery::mock(RegularStoreRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn($validatedInput);

        /** @var RegularOrder|\Mockery\MockInterface $regularOrder */
        $regularOrder = Mockery::mock(RegularOrder::class);

        /** @var IDataBaseRetrieveRegularOrders|\Mockery\MockInterface $iDataBaseRetrieveRegularOrders */
        $iDataBaseRetrieveRegularOrders = Mockery::mock(IDataBaseRetrieveRegularOrders::class);
        $iDataBaseRetrieveRegularOrders->shouldReceive('getRegularOrderById')->once()->with(10)->andReturn($regularOrder);

        /** @var FastestVisit|\Mockery\MockInterface $fastestVisit */
        $fastestVisit = Mockery::mock(FastestVisit::class);

        /** @var VisitsManagement|\Mockery\MockInterface $visitsManagement */
        $visitsManagement = Mockery::mock(VisitsManagement::class);
        $visitsManagement->shouldReceive('getRegularVisitFinder')->once()->with($regularOrder, null)->andReturn($fastestVisit);

        /** @var RegularVisit|\Mockery\MockInterface $regularVisit */
        $regularVisit = Mockery::mock(RegularVisit::class);
        $regularVisit->shouldReceive('toArray')->once()->andReturn(['visits']);

        /** @var IDataBaseCreateRegularVisit|\Mockery\MockInterface $iDataBaseCreateRegularVisit */
        $iDataBaseCreateRegularVisit = Mockery::mock(IDataBaseCreateRegularVisit::class);
        $iDataBaseCreateRegularVisit->shouldReceive('createRegularVisit')->once()->with($regularOrder, Mockery::on(function (IFindVisit $iFindVisit) {
            return $iFindVisit instanceof FastestVisit;
        }))->andReturn($regularVisit);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'visitsManagement' => $visitsManagement,
            'iDataBaseCreateRegularVisit' => $iDataBaseCreateRegularVisit,
            'iDataBaseRetrieveRegularOrders' => $iDataBaseRetrieveRegularOrders,
        ];

        $response = (new VisitsController(...$controllerArgs))->regularStore($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('visits', $response->original[0]);
    }

    public function testLaserShowAvailable(): void
    {
        $validatedInput = [
            'laserOrderId' => 10,
        ];

        /** @var LaserShowAvailableRequest|\Mockery\MockInterface $request */
        $request = Mockery::mock(LaserShowAvailableRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn($validatedInput);

        /** @var FastestVisit|\Mockery\MockInterface $iFindVisit */
        $iFindVisit = Mockery::mock(FastestVisit::class);
        $iFindVisit->shouldReceive('findVisit')->once()->andReturn(0);

        /** @var VisitsManagement|\Mockery\MockInterface $visitsManagement */
        $visitsManagement = Mockery::mock(VisitsManagement::class);
        $visitsManagement->shouldReceive('getLaserVisitFinder')->once()->with(10, null)->andReturn($iFindVisit);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'visitsManagement' => $visitsManagement,
        ];

        $response = (new VisitsController(...$controllerArgs))->laserShowAvailable($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertArrayHasKey('availableVisitTimestamp', $response->original);
        $this->assertEquals(0, $response->original['availableVisitTimestamp']);
    }

    public function testRegularShowAvailable(): void
    {
        $validatedInput = [
            'regularOrderId' => 10,
        ];

        /** @var RegularShowAvailableRequest|\Mockery\MockInterface $request */
        $request = Mockery::mock(RegularShowAvailableRequest::class);
        $request->shouldReceive('safe->all')->once()->andReturn($validatedInput);

        /** @var FastestVisit|\Mockery\MockInterface $iFindVisit */
        $iFindVisit = Mockery::mock(FastestVisit::class);
        $iFindVisit->shouldReceive('findVisit')->once()->andReturn(0);

        /** @var VisitsManagement|\Mockery\MockInterface $visitsManagement */
        $visitsManagement = Mockery::mock(VisitsManagement::class);
        $visitsManagement->shouldReceive('getRegularVisitFinder')->once()->with(10, null)->andReturn($iFindVisit);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'visitsManagement' => $visitsManagement,
        ];

        $response = (new VisitsController(...$controllerArgs))->regularShowAvailable($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertArrayHasKey('availableVisitTimestamp', $response->original);
        $this->assertEquals(0, $response->original['availableVisitTimestamp']);
    }

    public function testLaserDestroy(): void
    {
        /** @var LaserDestroyRequest|\Mockery\MockInterface $request */
        $request = Mockery::mock(LaserDestroyRequest::class);

        /** @var LaserVisit|\Mockery\MockInterface $visit */
        $visit = Mockery::mock(LaserVisit::class);

        /** @var IDataBaseRetrieveLaserVisits|\Mockery\MockInterface $iDataBaseRetrieveLaserVisits */
        $iDataBaseRetrieveLaserVisits = Mockery::mock(IDataBaseRetrieveLaserVisits::class);
        $iDataBaseRetrieveLaserVisits->shouldReceive('getLaserVisitById')->once()->with(10)->andReturn($visit);

        /** @var IDataBaseDeleteLaserVisit|\Mockery\MockInterface $iDataBaseDeleteLaserVisit */
        $iDataBaseDeleteLaserVisit = Mockery::mock(IDataBaseDeleteLaserVisit::class);
        $iDataBaseDeleteLaserVisit->shouldReceive('deleteLaserVisit')->once()->with($visit);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'iDataBaseRetrieveLaserVisits' => $iDataBaseRetrieveLaserVisits,
            'iDataBaseDeleteLaserVisit' => $iDataBaseDeleteLaserVisit,
        ];

        $response = (new VisitsController(...$controllerArgs))->laserDestroy($request, 10);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsString($response->original);
    }

    public function testRegularDestroy(): void
    {
        /** @var RegularDestroyRequest|\Mockery\MockInterface $request */
        $request = Mockery::mock(RegularDestroyRequest::class);

        /** @var RegularVisit|\Mockery\MockInterface $visit */
        $visit = Mockery::mock(RegularVisit::class);

        /** @var IDataBaseRetrieveRegularVisits|\Mockery\MockInterface $iDataBaseRetrieveRegularVisits */
        $iDataBaseRetrieveRegularVisits = Mockery::mock(IDataBaseRetrieveRegularVisits::class);
        $iDataBaseRetrieveRegularVisits->shouldReceive('getRegularVisitById')->once()->with(10)->andReturn($visit);

        /** @var IDataBaseDeleteRegularVisit|\Mockery\MockInterface $iDataBaseDeleteRegularVisit */
        $iDataBaseDeleteRegularVisit = Mockery::mock(IDataBaseDeleteRegularVisit::class);
        $iDataBaseDeleteRegularVisit->shouldReceive('deleteRegularVisit')->once()->with($visit);

        /** @var array $controllerArgs */
        $controllerArgs = [
            'iDataBaseRetrieveRegularVisits' => $iDataBaseRetrieveRegularVisits,
            'iDataBaseDeleteRegularVisit' => $iDataBaseDeleteRegularVisit,
        ];

        $response = (new VisitsController(...$controllerArgs))->regularDestroy($request, 10);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsString($response->original);
    }
}
