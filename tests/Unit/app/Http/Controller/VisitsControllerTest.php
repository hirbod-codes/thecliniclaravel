<?php

namespace Tests\Unit\app\Http\Controller;

use App\Auth\CheckAuthentication;
use App\Http\Controllers\Visits\VisitsController;
use App\Models\Auth\User;
use App\Models\Order\LaserOrder;
use App\Models\Order\Order;
use App\Models\Order\RegularOrder;
use App\Models\Visit\LaserVisit;
use App\Models\Visit\RegularVisit;
use Database\Factories\WeekDaysPeriodsFactory;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;
use TheClinicDataStructures\DataStructures\Order\Laser\DSLaserOrder;
use TheClinicDataStructures\DataStructures\Order\Regular\DSRegularOrder;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicDataStructures\DataStructures\Visit\Laser\DSLaserVisit;
use TheClinicDataStructures\DataStructures\Visit\Laser\DSLaserVisits;
use TheClinicDataStructures\DataStructures\Visit\Regular\DSRegularVisit;
use TheClinicDataStructures\DataStructures\Visit\Regular\DSRegularVisits;
use TheClinicUseCases\Visits\Creation\LaserVisitCreation;
use TheClinicUseCases\Visits\Deletion\LaserVisitDeletion;
use TheClinicUseCases\Visits\Interfaces\IDataBaseCreateLaserVisit;
use TheClinicUseCases\Visits\Interfaces\IDataBaseDeleteLaserVisit;
use TheClinicUseCases\Visits\Interfaces\IDataBaseRetrieveLaserVisits;
use TheClinicUseCases\Visits\Retrieval\LaserVisitRetrieval;

class VisitsControllerTest extends TestCase
{
    use GetAuthenticatables;

    private Generator $faker;

    private CheckAuthentication|MockInterface $checkAuthentication;

    private User $authenticated;
    private DSUser $dsAuthenticated;

    private User $userRole;
    private DSUser $dsUserRole;

    private LaserOrder $laserOrder;
    private DSLaserOrder $dsLaserOrder;

    private RegularOrder $regularOrder;
    private DSRegularOrder $dsRegularOrder;

    private LaserVisit $laserVisit;
    private array $laserVisits;
    private DSLaserVisit $dsLaserVisit;
    private DSLaserVisits $dsLaserVisits;

    private RegularVisit $regularVisit;
    private array $regularVisits;
    private DSRegularVisit $dsRegularVisit;
    private DSRegularVisits $dsRegularVisits;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        $safety = 0;
        while (isset($this->laserVisit) && $this->laserVisit !== null && $safety < 500) {
            $this->authenticated = $this->getAuthenticatable('admin');
            $this->dsAuthenticated = $this->authenticated->getDataStructure();

            $this->userRole = $this->getAuthenticatable('patient');
            $this->dsUserRole = $this->userRole->getDataStructure();

            $found = false;
            /** @var Order $order */
            foreach ($this->userRole->user->orders as $order) {
                /**
                 * @var LaserOrder $laserOrder
                 * @var RegularOrder $regularOrder
                 */
                if (($laserOrder = $order->laserOrder) !== null && ($regularOrder = $order->regularOrder) !== null) {
                    $found = true;
                    $this->laserOrder = $laserOrder;
                    $this->dsLaserOrder = $laserOrder->getDSLaserOrder();

                    $this->regularOrder = $regularOrder;
                    $this->dsRegularOrder = $regularOrder->getDSRegularOrder();
                }
            }
            if (!$found) {
                continue;
            }

            if (
                count($this->laserVisits = $this->laserOrder->laserVisits) === 0 ||
                count($this->regularVisits = $this->regularOrder->regularVisits) === 0
            ) {
                continue;
            }

            $this->laserVisit = $this->faker->randomElement($this->laserVisits);
            $this->dsLaserVisits = LaserVisit::getDSLaserVisits($this->laserVisits, "ASC");
            $this->dsLaserVisit = $this->laserVisit->getDSLaserVisit();

            $this->regularVisit = $this->faker->randomElement($this->regularVisits);
            $this->dsRegularVisits = RegularVisit::getDSRegularVisits($this->regularVisits, "ASC");
            $this->dsRegularVisit = $this->regularVisit->getDSRegularVisit();

            $safety++;
        }

        /** @var \App\Http\Controllers\CheckAuthentication|\Mockery\MockInterface $checkAuthentication */
        $this->checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $this->checkAuthentication->shouldReceive("getAuthenticatedDSUser")->andReturn($this->dsAuthenticated);
        $this->checkAuthentication->shouldReceive("getAuthenticated")->andReturn($this->authenticated);
    }

    public function testLaserIndex(): void
    {
        /** @var LaserVisitRetrieval|MockInterface $laserVisitRetrieval */
        $laserVisitRetrieval = Mockery::mock(LaserVisitRetrieval::class);

        /** @var IDataBaseRetrieveLaserVisits|MockInterface $iDataBaseRetrieveLaserVisits */
        $iDataBaseRetrieveLaserVisits = Mockery::mock(IDataBaseRetrieveLaserVisits::class);

        $args = [
            'checkAuthentication' => $this->checkAuthentication,
            'iDataBaseRetrieveLaserVisits' => $iDataBaseRetrieveLaserVisits,
            'laserVisitRetrieval' => $laserVisitRetrieval
        ];

        // getVisitsByUser
        $sortByTimestamp = 'asc';

        $laserVisitRetrieval
            ->shouldReceive('getVisitsByUser')
            ->with(
                $this->dsAuthenticated,
                $this->dsUserRole,
                $sortByTimestamp,
                $iDataBaseRetrieveLaserVisits
            )
            ->andReturn($this->dsLaserVisits)
            //
        ;

        $jsonResponse = (new VisitsController(...$args))->laserIndex($this->authenticated->getKey(), 'asc');
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsLaserVisitsArray = $this->dsLaserVisits->toArray()), $jsonResponse->original);
        foreach ($dsLaserVisitsArray as $key => $value) {
            $this->assertNotFalse(array_search($key, $jsonResponse->original));
            $this->assertEquals($value, $jsonResponse->original[$key]);
        }

        // getVisitsByOrder
        $sortByTimestamp = 'asc';

        $laserVisitRetrieval
            ->shouldReceive('getVisitsByOrder')
            ->with(
                $this->dsAuthenticated,
                $this->dsUserRole,
                $this->dsLaserOrder,
                $sortByTimestamp,
                $iDataBaseRetrieveLaserVisits
            )
            ->andReturn($this->dsLaserVisits)
            //
        ;

        $jsonResponse = (new VisitsController(...$args))->laserIndex($this->authenticated->getKey(), 'asc', $this->laserOrder->getKey());
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsLaserVisitsArray = $this->dsLaserVisits->toArray()), $jsonResponse->original);
        foreach ($dsLaserVisitsArray as $key => $value) {
            $this->assertNotFalse(array_search($key, $jsonResponse->original));
            $this->assertEquals($value, $jsonResponse->original[$key]);
        }

        // getVisitsByTimestamp
        $timestamp = $this->faker->numberBetween(500000, 1000000);
        $operator = $this->faker->randomElement(['<>', '=', '<=', '<', '>=', '>']);
        $sortByTimestamp = 'asc';

        $laserVisitRetrieval
            ->shouldReceive('getVisitsByTimestamp')
            ->with(
                $this->dsAuthenticated,
                $this->dsUserRole,
                $operator,
                $timestamp,
                $sortByTimestamp,
                $iDataBaseRetrieveLaserVisits
            )
            ->andReturn($this->dsLaserVisits)
            //
        ;

        $jsonResponse = (new VisitsController(...$args))->laserIndex($this->authenticated->getKey(), 'asc', null, $timestamp, $operator);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsLaserVisitsArray = $this->dsLaserVisits->toArray()), $jsonResponse->original);
        foreach ($dsLaserVisitsArray as $key => $value) {
            $this->assertNotFalse(array_search($key, $jsonResponse->original));
            $this->assertEquals($value, $jsonResponse->original[$key]);
        }
    }

    public function testRegularIndex(): void
    {
        /** @var RegularVisitRetrieval|MockInterface $regularVisitRetrieval */
        $regularVisitRetrieval = Mockery::mock(RegularVisitRetrieval::class);

        /** @var IDataBaseRetrieveRegularVisits|MockInterface $iDataBaseRetrieveRegularVisits */
        $iDataBaseRetrieveRegularVisits = Mockery::mock(IDataBaseRetrieveRegularVisits::class);

        $args = [
            'checkAuthentication' => $this->checkAuthentication,
            'iDataBaseRetrieveRegularVisits' => $iDataBaseRetrieveRegularVisits,
            'regularVisitRetrieval' => $regularVisitRetrieval
        ];

        // getVisitsByUser
        $sortByTimestamp = 'asc';

        $regularVisitRetrieval
            ->shouldReceive('getVisitsByUser')
            ->with(
                $this->dsAuthenticated,
                $this->dsUserRole,
                $sortByTimestamp,
                $iDataBaseRetrieveRegularVisits
            )
            ->andReturn($this->dsRegularVisits)
            //
        ;

        $jsonResponse = (new VisitsController(...$args))->regularIndex($this->authenticated->getKey(), 'asc');
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsRegularVisitsArray = $this->dsRegularVisits->toArray()), $jsonResponse->original);
        foreach ($dsRegularVisitsArray as $key => $value) {
            $this->assertNotFalse(array_search($key, $jsonResponse->original));
            $this->assertEquals($value, $jsonResponse->original[$key]);
        }

        // getVisitsByOrder
        $sortByTimestamp = 'asc';

        $regularVisitRetrieval
            ->shouldReceive('getVisitsByOrder')
            ->with(
                $this->dsAuthenticated,
                $this->dsUserRole,
                $this->dsRegularOrder,
                $sortByTimestamp,
                $iDataBaseRetrieveRegularVisits
            )
            ->andReturn($this->dsRegularVisits)
            //
        ;

        $jsonResponse = (new VisitsController(...$args))->regularIndex($this->authenticated->getKey(), 'asc', $this->RegularOrder->getKey());
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsRegularVisitsArray = $this->dsRegularVisits->toArray()), $jsonResponse->original);
        foreach ($dsRegularVisitsArray as $key => $value) {
            $this->assertNotFalse(array_search($key, $jsonResponse->original));
            $this->assertEquals($value, $jsonResponse->original[$key]);
        }

        // getVisitsByTimestamp
        $timestamp = $this->faker->numberBetween(500000, 1000000);
        $operator = $this->faker->randomElement(['<>', '=', '<=', '<', '>=', '>']);
        $sortByTimestamp = 'asc';

        $regularVisitRetrieval
            ->shouldReceive('getVisitsByTimestamp')
            ->with(
                $this->dsAuthenticated,
                $this->dsUserRole,
                $operator,
                $timestamp,
                $sortByTimestamp,
                $iDataBaseRetrieveRegularVisits
            )
            ->andReturn($this->dsRegularVisits)
            //
        ;

        $jsonResponse = (new VisitsController(...$args))->regularIndex($this->authenticated->getKey(), 'asc', null, $timestamp, $operator);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsRegularVisitsArray = $this->dsRegularVisits->toArray()), $jsonResponse->original);
        foreach ($dsRegularVisitsArray as $key => $value) {
            $this->assertNotFalse(array_search($key, $jsonResponse->original));
            $this->assertEquals($value, $jsonResponse->original[$key]);
        }
    }

    public function testLaserStore(): void
    {
        /** @var Request|MockInterface $request */
        $request = Mockery::mock(Request::class);
        $request->laserOrderId = $this->laserOrder->getKey();
        $request->targetUserId = $this->userRole->user->getKey();

        /** @var IDataBaseCreateLaserVisit|MockInterface $iDataBaseCreateLaserVisit */
        $iDataBaseCreateLaserVisit = Mockery::mock(IDataBaseCreateLaserVisit::class);

        /** @var LaserVisitCreation|MockInterface $laserVisitCreation */
        $laserVisitCreation = Mockery::mock(LaserVisitCreation::class);
        $laserVisitCreation
            ->shouldReceive('create')
            ->with($this->dsLaserOrder, $this->dsUserRole, $this->dsAuthenticated, $iDataBaseCreateLaserVisit)
            ->andReturn($this->dsLaserVisits)
            //
        ;

        // The laserVisitCreation is not null
        $args = [
            'checkAuthentication' => $this->checkAuthentication,
            'laserVisitCreation' => $laserVisitCreation
        ];

        $jsonResponse = (new VisitsController(...$args))->laserStore($request);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsLaserVisitsArray = $this->dsLaserVisits->toArray()), $jsonResponse->original);
        foreach ($dsLaserVisitsArray as $key => $value) {
            $this->assertNotFalse(array_search($key, $jsonResponse->original));
            $this->assertEquals($value, $jsonResponse->original[$key]);
        }

        // The laserVisitCreation and request->weekDayPeriods are null
        $args = [
            'checkAuthentication' => $this->checkAuthentication,
        ];

        $jsonResponse = (new VisitsController(...$args))->laserStore($request);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsLaserVisitsArray = $this->dsLaserVisits->toArray()), $jsonResponse->original);
        foreach ($dsLaserVisitsArray as $key => $value) {
            $this->assertNotFalse(array_search($key, $jsonResponse->original));
            $this->assertEquals($value, $jsonResponse->original[$key]);
        }

        // The laserVisitCreation is null
        $request->weekDayPeriods = (new WeekDaysPeriodsFactory)->generateDSWeekDaysPeriods();

        $args = [
            'checkAuthentication' => $this->checkAuthentication,
        ];

        $jsonResponse = (new VisitsController(...$args))->laserStore($request);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsLaserVisitsArray = $this->dsLaserVisits->toArray()), $jsonResponse->original);
        foreach ($dsLaserVisitsArray as $key => $value) {
            $this->assertNotFalse(array_search($key, $jsonResponse->original));
            $this->assertEquals($value, $jsonResponse->original[$key]);
        }
    }

    public function testRegularStore(): void
    {
        /** @var Request|MockInterface $request */
        $request = Mockery::mock(Request::class);
        $request->regularOrderId = $this->regularOrder->getKey();
        $request->targetUserId = $this->userRole->user->getKey();

        /** @var IDataBaseCreateRegularVisit|MockInterface $iDataBaseCreateRegularVisit */
        $iDataBaseCreateRegularVisit = Mockery::mock(IDataBaseCreateRegularVisit::class);

        /** @var RegularVisitCreation|MockInterface $regularVisitCreation */
        $regularVisitCreation = Mockery::mock(RegularVisitCreation::class);
        $regularVisitCreation
            ->shouldReceive('create')
            ->with($this->dsRegularOrder, $this->dsUserRole, $this->dsAuthenticated, $iDataBaseCreateRegularVisit)
            ->andReturn($this->dsRegularVisits)
            //
        ;

        // The RegularVisitCreation is not null
        $args = [
            'checkAuthentication' => $this->checkAuthentication,
            'regularVisitCreation' => $regularVisitCreation
        ];

        $jsonResponse = (new VisitsController(...$args))->regularStore($request);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsRegularVisitsArray = $this->dsRegularVisits->toArray()), $jsonResponse->original);
        foreach ($dsRegularVisitsArray as $key => $value) {
            $this->assertNotFalse(array_search($key, $jsonResponse->original));
            $this->assertEquals($value, $jsonResponse->original[$key]);
        }

        // The RegularVisitCreation and request->weekDayPeriods are null
        $args = [
            'checkAuthentication' => $this->checkAuthentication,
        ];

        $jsonResponse = (new VisitsController(...$args))->regularStore($request);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsRegularVisitsArray = $this->dsRegularVisits->toArray()), $jsonResponse->original);
        foreach ($dsRegularVisitsArray as $key => $value) {
            $this->assertNotFalse(array_search($key, $jsonResponse->original));
            $this->assertEquals($value, $jsonResponse->original[$key]);
        }

        // The RegularVisitCreation is null
        $request->weekDayPeriods = (new WeekDaysPeriodsFactory)->generateDSWeekDaysPeriods();

        $args = [
            'checkAuthentication' => $this->checkAuthentication,
        ];

        $jsonResponse = (new VisitsController(...$args))->regularStore($request);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsRegularVisitsArray = $this->dsRegularVisits->toArray()), $jsonResponse->original);
        foreach ($dsRegularVisitsArray as $key => $value) {
            $this->assertNotFalse(array_search($key, $jsonResponse->original));
            $this->assertEquals($value, $jsonResponse->original[$key]);
        }
    }

    public function testLaserShow(): void
    {
        $timestamp = $this->faker->numberBetween(500000, 1000000);

        /** @var IDataBaseRetrieveLaserVisits|MockInterface $laserVisitRetrieval */
        $iDataBaseRetrieveLaserVisits = Mockery::mock(IDataBaseRetrieveLaserVisits::class);

        /** @var LaserVisitRetrieval|MockInterface $laserVisitRetrieval */
        $laserVisitRetrieval = Mockery::mock(LaserVisitRetrieval::class);
        $laserVisitRetrieval
            ->shouldReceive('getVisitsByTimestamp')
            ->with($this->dsAuthenticated, '=', $timestamp, 'desc', $iDataBaseRetrieveLaserVisits)
            ->andReturn($this->dsLaserVisits)
            //
        ;

        $args = [
            'checkAuthentication' => $this->checkAuthentication,
            'laserVisitRetrieval' => $laserVisitRetrieval
        ];
        $jsonResponse = (new VisitsController(...$args))->laserShow($timestamp);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsRegularVisitsArray = $this->dsRegularVisits->toArray()), $jsonResponse->original);
        foreach ($dsRegularVisitsArray as $key => $value) {
            $this->assertNotFalse(array_search($key, $jsonResponse->original));
            $this->assertEquals($value, $jsonResponse->original[$key]);
        }
    }

    public function testRegularShow(): void
    {
        $timestamp = $this->faker->numberBetween(500000, 1000000);

        /** @var IDataBaseRetrieveRegularVisits|MockInterface $iDataBaseRetrieveRegularVisits */
        $iDataBaseRetrieveRegularVisits = Mockery::mock(IDataBaseRetrieveRegularVisits::class);

        /** @var RegularVisitRetrieval|MockInterface $regularVisitRetrieval */
        $regularVisitRetrieval = Mockery::mock(RegularVisitRetrieval::class);
        $regularVisitRetrieval
            ->shouldReceive('getVisitsByTimestamp')
            ->with($this->dsAuthenticated, '=', $timestamp, 'desc', $iDataBaseRetrieveRegularVisits)
            ->andReturn($this->dsRegularVisits)
            //
        ;

        $args = [
            'checkAuthentication' => $this->checkAuthentication,
            'regularVisitRetrieval' => $regularVisitRetrieval
        ];

        $jsonResponse = (new VisitsController(...$args))->regularShow($timestamp);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsRegularVisitsArray = $this->dsRegularVisits->toArray()), $jsonResponse->original);
        foreach ($dsRegularVisitsArray as $key => $value) {
            $this->assertNotFalse(array_search($key, $jsonResponse->original));
            $this->assertEquals($value, $jsonResponse->original[$key]);
        }
    }

    public function testLaserDestroy(): void
    {
        /** @var IDataBaseDeleteLaserVisit|MockInterface $iDataBaseDeleteLaserVisit */
        $iDataBaseDeleteLaserVisit = Mockery::mock(IDataBaseDeleteLaserVisit::class);

        /** @var LaserVisitDeletion|MockInterface $laserVisitDeletion */
        $laserVisitDeletion = Mockery::mock(LaserVisitDeletion::class);
        $laserVisitDeletion
            ->shouldReceive('delete')
            ->with($this->dsLaserVisit, $this->dsUserRole, $this->dsAuthenticated, $iDataBaseDeleteLaserVisit)
            //
        ;

        $args = [
            'checkAuthentication' => $this->checkAuthentication,
            'iDataBaseDeleteLaserVisit' => $iDataBaseDeleteLaserVisit,
            'laserVisitDeletion' => $laserVisitDeletion
        ];

        $response = (new VisitsController(...$args))->laserDestroy($this->laserVisit->getKey(), $this->dsUserRole->user->getKey());
        $this->assertInstanceOf(Response::class, $response);
        $this->assertIsString($response->original);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRegularDestroy(): void
    {
        /** @var IDataBaseDeleteRegularVisit|MockInterface $iDataBaseDeleteRegularVisit */
        $iDataBaseDeleteRegularVisit = Mockery::mock(IDataBaseDeleteRegularVisit::class);

        /** @var RegularVisitDeletion|MockInterface $regularVisitDeletion */
        $regularVisitDeletion = Mockery::mock(RegularVisitDeletion::class);
        $regularVisitDeletion
            ->shouldReceive('delete')
            ->with($this->dsRegularVisit, $this->dsUserRole, $this->dsAuthenticated, $iDataBaseDeleteRegularVisit)
            //
        ;

        $args = [
            'checkAuthentication' => $this->checkAuthentication,
            'iDataBaseDeleteRegularVisit' => $iDataBaseDeleteRegularVisit,
            'regularVisitDeletion' => $regularVisitDeletion
        ];

        $response = (new VisitsController(...$args))->RegularDestroy($this->regularVisit->getKey(), $this->dsUserRole->user->getKey());
        $this->assertInstanceOf(Response::class, $response);
        $this->assertIsString($response->original);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
