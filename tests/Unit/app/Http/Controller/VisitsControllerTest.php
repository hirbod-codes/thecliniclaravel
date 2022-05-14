<?php

namespace Tests\Unit\app\Http\Controller;

use App\Auth\CheckAuthentication;
use App\Http\Controllers\Visits\VisitsController;
use App\Http\Requests\Visits\LaserIndexRequest;
use App\Http\Requests\Visits\LaserStoreRequest;
use App\Http\Requests\Visits\RegularIndexRequest;
use App\Http\Requests\Visits\RegularStoreRequest;
use App\Models\Auth\User;
use App\Models\Order\LaserOrder;
use App\Models\Order\Order;
use App\Models\Order\RegularOrder;
use App\Models\Visit\LaserVisit;
use App\Models\Visit\RegularVisit;
use Database\Factories\WeekDaysPeriodsFactory;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;
use TheClinic\Visit\IFindVisit;
use TheClinicDataStructures\DataStructures\Order\Laser\DSLaserOrder;
use TheClinicDataStructures\DataStructures\Order\Regular\DSRegularOrder;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicDataStructures\DataStructures\Visit\Laser\DSLaserVisit;
use TheClinicDataStructures\DataStructures\Visit\Laser\DSLaserVisits;
use TheClinicDataStructures\DataStructures\Visit\Regular\DSRegularVisit;
use TheClinicDataStructures\DataStructures\Visit\Regular\DSRegularVisits;
use TheClinicUseCases\Visits\Creation\LaserVisitCreation;
use TheClinicUseCases\Visits\Creation\RegularVisitCreation;
use TheClinicUseCases\Visits\Deletion\LaserVisitDeletion;
use TheClinicUseCases\Visits\Deletion\RegularVisitDeletion;
use TheClinicUseCases\Visits\Interfaces\IDataBaseCreateLaserVisit;
use TheClinicUseCases\Visits\Interfaces\IDataBaseCreateRegularVisit;
use TheClinicUseCases\Visits\Interfaces\IDataBaseDeleteLaserVisit;
use TheClinicUseCases\Visits\Interfaces\IDataBaseDeleteRegularVisit;
use TheClinicUseCases\Visits\Interfaces\IDataBaseRetrieveLaserVisits;
use TheClinicUseCases\Visits\Interfaces\IDataBaseRetrieveRegularVisits;
use TheClinicUseCases\Visits\Retrieval\LaserVisitRetrieval;
use TheClinicUseCases\Visits\Retrieval\RegularVisitRetrieval;

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
    private array|Collection $laserVisits;
    private DSLaserVisit $dsLaserVisit;
    private DSLaserVisits $dsLaserVisits;

    private RegularVisit $regularVisit;
    private array|Collection $regularVisits;
    private DSRegularVisit $dsRegularVisit;
    private DSRegularVisits $dsRegularVisits;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        $safety = 0;
        while ((!isset($this->laserVisit) || $this->laserVisit === null) && $safety < 500) {
            $this->authenticated = $this->getAuthenticatable('admin');
            $this->dsAuthenticated = $this->authenticated->getDataStructure();

            $this->userRole = $this->getAuthenticatable('patient');
            $this->dsUserRole = $this->userRole->getDataStructure();

            $laserOrderFound = false;
            $regularOrderFound = false;
            /** @var Order $order */
            foreach ($this->userRole->user->orders as $order) {
                /** @var LaserOrder $laserOrder */
                if (($laserOrder = $order->laserOrder) !== null) {
                    $laserOrderFound = true;
                    $this->laserOrder = $laserOrder;
                    $this->dsLaserOrder = $laserOrder->getDSLaserOrder();
                }

                /** @var RegularOrder $regularOrder */
                if (($regularOrder = $order->regularOrder) !== null) {
                    $regularOrderFound = true;
                    $this->regularOrder = $regularOrder;
                    $this->dsRegularOrder = $regularOrder->getDSRegularOrder();
                }

                if ($regularOrderFound && $laserOrderFound) {
                    break;
                }
            }
            if (!$regularOrderFound || !$laserOrderFound) {
                $safety++;
                continue;
            }

            if (
                count($this->laserVisits = $this->laserOrder->laserVisits) === 0 ||
                count($this->regularVisits = $this->regularOrder->regularVisits) === 0
            ) {
                $safety++;
                continue;
            }

            $this->laserVisit = $this->laserVisits[0];
            $this->dsLaserVisits = LaserVisit::getDSLaserVisits($this->laserVisits, "ASC");
            $this->dsLaserVisit = $this->laserVisit->getDSLaserVisit();

            $this->regularVisit = $this->regularVisits[0];
            $this->dsRegularVisits = RegularVisit::getDSRegularVisits($this->regularVisits, "ASC");
            $this->dsRegularVisit = $this->regularVisit->getDSRegularVisit();

            break;
        }

        if (!isset($this->laserVisit) || $this->laserVisit === null) {
            throw new ModelNotFoundException('', 404);
        }

        /** @var \App\Http\Controllers\CheckAuthentication|\Mockery\MockInterface $checkAuthentication */
        $this->checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $this->checkAuthentication->shouldReceive("getAuthenticatedDSUser")->andReturn($this->dsAuthenticated);
        $this->checkAuthentication->shouldReceive("getAuthenticated")->andReturn($this->authenticated);
    }

    public function testLaserIndex(): void
    {
        /** @var LaserIndexRequest|MockInterface $request */
        $request = Mockery::mock(LaserIndexRequest::class);

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
        $input = [
            'accountId' => $this->userRole->user->getKey(),
            'sortByTimestamp' => $sortByTimestamp = 'asc',
        ];
        $request->shouldReceive('safe->all')->andReturn($input);

        $laserVisitRetrieval
            ->shouldReceive('getVisitsByUser')
            ->with(
                $this->dsAuthenticated,
                Mockery::on(function (DSUser $dsUser): bool {
                    if ($dsUser->getId() === $this->dsUserRole->getId()) {
                        return true;
                    }
                    return false;
                }),
                $sortByTimestamp,
                $iDataBaseRetrieveLaserVisits
            )
            ->andReturn($this->dsLaserVisits)
            //
        ;

        $jsonResponse = (new VisitsController(...$args))->laserIndex($request);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsLaserVisitsArray = $this->dsLaserVisits->toArray()), $jsonResponse->original);
        foreach ($dsLaserVisitsArray as $key => $value) {
            $this->assertNotFalse(array_search($key, array_keys($jsonResponse->original)), '$key: ' . strval($key) . "\narray: " . json_encode($jsonResponse->original, JSON_PRETTY_PRINT));
            $this->assertEquals($value, $jsonResponse->original[$key]);
        }

        // getVisitsByOrder
        $input = [
            'accountId' => $this->userRole->user->getKey(),
            'regularOrderId' => $this->regularOrder->getKey(),
            'sortByTimestamp' => $sortByTimestamp = 'asc',
        ];
        $request->shouldReceive('safe->all')->andReturn($input);

        $laserVisitRetrieval
            ->shouldReceive('getVisitsByOrder')
            ->with(
                $this->dsAuthenticated,
                Mockery::on(function (DSUser $dsUser): bool {
                    if ($dsUser->getId() === $this->dsUserRole->getId()) {
                        return true;
                    }
                    return false;
                }),
                Mockery::on(function (DSLaserOrder $dsLaserOrder): bool {
                    if ($dsLaserOrder->getId() === $this->dsLaserOrder->getId()) {
                        return true;
                    }
                    return false;
                }),
                $sortByTimestamp,
                $iDataBaseRetrieveLaserVisits
            )
            ->andReturn($this->dsLaserVisits)
            //
        ;

        $jsonResponse = (new VisitsController(...$args))->laserIndex($request);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsLaserVisitsArray = $this->dsLaserVisits->toArray()), $jsonResponse->original);
        foreach ($dsLaserVisitsArray as $key => $value) {
            $this->assertNotFalse(array_search($key, array_keys($jsonResponse->original)));
            $this->assertEquals($value, $jsonResponse->original[$key]);
        }

        // getVisitsByTimestamp
        $input = [
            'accountId' => $this->userRole->user->getKey(),
            'regularOrderId' => $this->regularOrder->getKey(),
            'sortByTimestamp' => $sortByTimestamp = 'asc',
            'timestamp' => $timestamp = $this->faker->numberBetween(500000, 1000000),
            'operator' => $operator = $this->faker->randomElement(['<>', '=', '<=', '<', '>=', '>']),
        ];
        $request->shouldReceive('safe->all')->andReturn($input);

        $laserVisitRetrieval
            ->shouldReceive('getVisitsByTimestamp')
            ->with(
                $this->dsAuthenticated,
                $operator,
                $timestamp,
                $sortByTimestamp,
                $iDataBaseRetrieveLaserVisits
            )
            ->andReturn($this->dsLaserVisits)
            //
        ;

        $jsonResponse = (new VisitsController(...$args))->laserIndex($request);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsLaserVisitsArray = $this->dsLaserVisits->toArray()), $jsonResponse->original);
        foreach ($dsLaserVisitsArray as $key => $value) {
            $this->assertNotFalse(array_search($key, array_keys($jsonResponse->original)));
            $this->assertEquals($value, $jsonResponse->original[$key]);
        }
    }

    public function testRegularIndex(): void
    {
        /** @var RegularIndexRequest|MockInterface $request */
        $request = Mockery::mock(RegularIndexRequest::class);

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
        $input = [
            'accountId' => $this->userRole->user->getKey(),
            'sortByTimestamp' => $sortByTimestamp = 'asc',
        ];
        $request->shouldReceive('safe->all')->andReturn($input);

        $regularVisitRetrieval
            ->shouldReceive('getVisitsByUser')
            ->with(
                $this->dsAuthenticated,
                Mockery::on(function (DSUser $dsUser): bool {
                    if ($dsUser->getId() === $this->dsUserRole->getId()) {
                        return true;
                    }
                    return false;
                }),
                $sortByTimestamp,
                $iDataBaseRetrieveRegularVisits
            )
            ->andReturn($this->dsRegularVisits)
            //
        ;

        $jsonResponse = (new VisitsController(...$args))->regularIndex($request);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsRegularVisitsArray = $this->dsRegularVisits->toArray()), $jsonResponse->original);
        foreach ($dsRegularVisitsArray as $key => $value) {
            $this->assertNotFalse(array_search($key, array_keys($jsonResponse->original)));
            $this->assertEquals($value, $jsonResponse->original[$key]);
        }

        // getVisitsByOrder
        $input = [
            'accountId' => $this->userRole->user->getKey(),
            'regularOrderId' => $this->regularOrder->getKey(),
            'sortByTimestamp' => $sortByTimestamp = 'asc',
        ];
        $request->shouldReceive('safe->all')->andReturn($input);

        $regularVisitRetrieval
            ->shouldReceive('getVisitsByOrder')
            ->with(
                $this->dsAuthenticated,
                Mockery::on(function (DSUser $dsUser): bool {
                    if ($dsUser->getId() === $this->dsUserRole->getId()) {
                        return true;
                    }
                    return false;
                }),
                Mockery::on(function (DSRegularOrder $dsRegularOrder): bool {
                    if ($dsRegularOrder->getId() === $this->dsRegularOrder->getId()) {
                        return true;
                    }
                    return false;
                }),
                $sortByTimestamp,
                $iDataBaseRetrieveRegularVisits
            )
            ->andReturn($this->dsRegularVisits)
            //
        ;

        $jsonResponse = (new VisitsController(...$args))->regularIndex($request);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsRegularVisitsArray = $this->dsRegularVisits->toArray()), $jsonResponse->original);
        foreach ($dsRegularVisitsArray as $key => $value) {
            $this->assertNotFalse(array_search($key, array_keys($jsonResponse->original)));
            $this->assertEquals($value, $jsonResponse->original[$key]);
        }

        // getVisitsByTimestamp
        $input = [
            'accountId' => $this->userRole->user->getKey(),
            'regularOrderId' => $this->regularOrder->getKey(),
            'sortByTimestamp' => $sortByTimestamp = 'asc',
            'timestamp' => $timestamp = $this->faker->numberBetween(500000, 1000000),
            'operator' => $operator = $this->faker->randomElement(['<>', '=', '<=', '<', '>=', '>']),
        ];
        $request->shouldReceive('safe->all')->andReturn($input);

        $regularVisitRetrieval
            ->shouldReceive('getVisitsByTimestamp')
            ->with(
                $this->dsAuthenticated,
                $operator,
                $timestamp,
                $sortByTimestamp,
                $iDataBaseRetrieveRegularVisits
            )
            ->andReturn($this->dsRegularVisits)
            //
        ;

        $jsonResponse = (new VisitsController(...$args))->regularIndex($request);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsRegularVisitsArray = $this->dsRegularVisits->toArray()), $jsonResponse->original);
        foreach ($dsRegularVisitsArray as $key => $value) {
            $this->assertNotFalse(array_search($key, array_keys($jsonResponse->original)));
            $this->assertEquals($value, $jsonResponse->original[$key]);
        }
    }

    public function testLaserStore(): void
    {
        $input = [
            'laserOrderId' => $this->laserOrder->getKey(),
            'targetUserId' => $this->userRole->user->getKey(),
            'weekDaysPeriods' => null,
            'dateTimePeriod' => null,
        ];
        /** @var LaserStoreRequest|MockInterface $request */
        $request = Mockery::mock(LaserStoreRequest::class);
        $request->shouldReceive('safe->all')->andReturn($input);

        /** @var IFindVisit|MockInterface $iFindVisit */
        $iFindVisit = Mockery::mock(IFindVisit::class);

        /** @var IDataBaseCreateLaserVisit|MockInterface $iDataBaseCreateLaserVisit */
        $iDataBaseCreateLaserVisit = Mockery::mock(IDataBaseCreateLaserVisit::class);

        /** @var LaserVisitCreation|MockInterface $laserVisitCreation */
        $laserVisitCreation = Mockery::mock(LaserVisitCreation::class);
        $laserVisitCreation
            ->shouldReceive('create')
            ->with(
                Mockery::on(function (DSLaserOrder $dsLaserOrder): bool {
                    if ($dsLaserOrder->getId() === $this->dsLaserOrder->getId()) {
                        return true;
                    }
                    return false;
                }),
                Mockery::on(function (DSUser $dsUserRole): bool {
                    if ($dsUserRole->getId() === $this->dsUserRole->getId()) {
                        return true;
                    }
                    return false;
                }),
                $this->dsAuthenticated,
                $iDataBaseCreateLaserVisit,
                Mockery::on(function (IFindVisit $iFindVisit): bool {
                    return true;
                })
            )
            ->andReturn($this->dsLaserVisit)
            //
        ;

        // The laserVisitCreation is not null
        $args = [
            'checkAuthentication' => $this->checkAuthentication,
            'laserVisitCreation' => $laserVisitCreation,
            'iDataBaseCreateLaserVisit' => $iDataBaseCreateLaserVisit
        ];

        $jsonResponse = (new VisitsController(...$args))->laserStore($request);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsLaserVisitArray = $this->dsLaserVisit->toArray()), $jsonResponse->original);
        foreach ($dsLaserVisitArray as $key => $value) {
            $this->assertNotFalse(array_search($key, array_keys($jsonResponse->original)));
            $this->assertEquals($value, $jsonResponse->original[$key]);
        }

        $input['weekDaysPeriods'] = (new WeekDaysPeriodsFactory)->generateDSWeekDaysPeriods()->toArray();
        $request->shouldReceive('safe->all')->andReturn($input);

        $jsonResponse = (new VisitsController(...$args))->laserStore($request);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsLaserVisitArray = $this->dsLaserVisit->toArray()), $jsonResponse->original);
        foreach ($dsLaserVisitArray as $key => $value) {
            $this->assertNotFalse(array_search($key, array_keys($jsonResponse->original)));
            $this->assertEquals($value, $jsonResponse->original[$key]);
        }
    }

    public function testRegularStore(): void
    {
        $input = [
            'regularOrderId' => $this->regularOrder->getKey(),
            'targetUserId' => $this->userRole->user->getKey(),
            'weekDaysPeriods' => null,
            'dateTimePeriod' => null,
        ];
        /** @var RegularStoreRequest|MockInterface $request */
        $request = Mockery::mock(RegularStoreRequest::class);
        $request->shouldReceive('safe->all')->andReturn($input);

        /** @var IFindVisit|MockInterface $iFindVisit */
        $iFindVisit = Mockery::mock(IFindVisit::class);

        /** @var IDataBaseCreateRegularVisit|MockInterface $iDataBaseCreateRegularVisit */
        $iDataBaseCreateRegularVisit = Mockery::mock(IDataBaseCreateRegularVisit::class);

        /** @var RegularVisitCreation|MockInterface $regularVisitCreation */
        $regularVisitCreation = Mockery::mock(RegularVisitCreation::class);
        $regularVisitCreation
            ->shouldReceive('create')
            ->with(
                Mockery::on(function (DSRegularOrder $dsRegularOrder): bool {
                    if ($dsRegularOrder->getId() === $this->dsRegularOrder->getId()) {
                        return true;
                    }
                    return false;
                }),
                Mockery::on(function (DSUser $dsUserRole): bool {
                    if ($dsUserRole->getId() === $this->dsUserRole->getId()) {
                        return true;
                    }
                    return false;
                }),
                $this->dsAuthenticated,
                $iDataBaseCreateRegularVisit,
                Mockery::on(function (IFindVisit $iFindVisit): bool {
                    return true;
                })
            )
            ->andReturn($this->dsRegularVisit)
            //
        ;

        // The regularVisitCreation is not null
        $args = [
            'checkAuthentication' => $this->checkAuthentication,
            'regularVisitCreation' => $regularVisitCreation,
            'iDataBaseCreateRegularVisit' => $iDataBaseCreateRegularVisit
        ];

        $jsonResponse = (new VisitsController(...$args))->regularStore($request);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsRegularVisitArray = $this->dsRegularVisit->toArray()), $jsonResponse->original);
        foreach ($dsRegularVisitArray as $key => $value) {
            $this->assertNotFalse(array_search($key, array_keys($jsonResponse->original)));
            $this->assertEquals($value, $jsonResponse->original[$key]);
        }

        $input['weekDaysPeriods'] = (new WeekDaysPeriodsFactory)->generateDSWeekDaysPeriods()->toArray();
        $request->shouldReceive('safe->all')->andReturn($input);

        $jsonResponse = (new VisitsController(...$args))->regularStore($request);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsRegularVisitArray = $this->dsRegularVisit->toArray()), $jsonResponse->original);
        foreach ($dsRegularVisitArray as $key => $value) {
            $this->assertNotFalse(array_search($key, array_keys($jsonResponse->original)));
            $this->assertEquals($value, $jsonResponse->original[$key]);
        }
    }

    public function testLaserShow(): void
    {
        $timestamp = $this->faker->numberBetween(500000, 1000000);
        $dsLaserVisits = new DSLaserVisits;
        $dsLaserVisits[] = $this->dsLaserVisit;

        /** @var IDataBaseRetrieveLaserVisits|MockInterface $laserVisitRetrieval */
        $iDataBaseRetrieveLaserVisits = Mockery::mock(IDataBaseRetrieveLaserVisits::class);

        /** @var LaserVisitRetrieval|MockInterface $laserVisitRetrieval */
        $laserVisitRetrieval = Mockery::mock(LaserVisitRetrieval::class);
        $laserVisitRetrieval
            ->shouldReceive('getVisitsByTimestamp')
            ->with($this->dsAuthenticated, '=', $timestamp, 'desc', $iDataBaseRetrieveLaserVisits)
            ->andReturn($dsLaserVisits)
            //
        ;

        $args = [
            'checkAuthentication' => $this->checkAuthentication,
            'iDataBaseRetrieveLaserVisits' => $iDataBaseRetrieveLaserVisits,
            'laserVisitRetrieval' => $laserVisitRetrieval
        ];
        $jsonResponse = (new VisitsController(...$args))->laserShow($timestamp);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsLaserVisitsArray = $this->dsLaserVisit->toArray()), $jsonResponse->original);
        foreach ($dsLaserVisitsArray as $key => $value) {
            $this->assertNotFalse(array_search($key, array_keys($jsonResponse->original)));
            $this->assertEquals($value, $jsonResponse->original[$key], '$key: ' . strval($key));
        }
    }

    public function testRegularShow(): void
    {
        $timestamp = $this->faker->numberBetween(500000, 1000000);
        $dsRegularVisits = new DSRegularVisits;
        $dsRegularVisits[] = $this->dsRegularVisit;

        /** @var IDataBaseRetrieveRegularVisits|MockInterface $regularVisitRetrieval */
        $iDataBaseRetrieveRegularVisits = Mockery::mock(IDataBaseRetrieveRegularVisits::class);

        /** @var RegularVisitRetrieval|MockInterface $regularVisitRetrieval */
        $regularVisitRetrieval = Mockery::mock(RegularVisitRetrieval::class);
        $regularVisitRetrieval
            ->shouldReceive('getVisitsByTimestamp')
            ->with($this->dsAuthenticated, '=', $timestamp, 'desc', $iDataBaseRetrieveRegularVisits)
            ->andReturn($dsRegularVisits)
            //
        ;

        $args = [
            'checkAuthentication' => $this->checkAuthentication,
            'iDataBaseRetrieveRegularVisits' => $iDataBaseRetrieveRegularVisits,
            'regularVisitRetrieval' => $regularVisitRetrieval
        ];
        $jsonResponse = (new VisitsController(...$args))->RegularShow($timestamp);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsRegularVisitArray = $this->dsRegularVisit->toArray()), $jsonResponse->original);
        foreach ($dsRegularVisitArray as $key => $value) {
            $this->assertNotFalse(array_search($key, array_keys($jsonResponse->original)));
            $this->assertEquals($value, $jsonResponse->original[$key], '$key: ' . strval($key));
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
            ->with(
                Mockery::on(function (DSLaserVisit $dsLaserVisit): bool {
                    if ($dsLaserVisit->getId() === $this->dsLaserVisit->getId()) {
                        return true;
                    }
                    return false;
                }),
                Mockery::on(function (DSUser $dsUserRole): bool {
                    if ($dsUserRole->getId() === $this->dsUserRole->getId()) {
                        return true;
                    }
                    return false;
                }),
                $this->dsAuthenticated,
                $iDataBaseDeleteLaserVisit
            )
            //
        ;

        $args = [
            'checkAuthentication' => $this->checkAuthentication,
            'iDataBaseDeleteLaserVisit' => $iDataBaseDeleteLaserVisit,
            'laserVisitDeletion' => $laserVisitDeletion
        ];

        $response = (new VisitsController(...$args))->laserDestroy($this->laserVisit->getKey(), $this->userRole->user->getKey());
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
            ->with(
                Mockery::on(function (DSRegularVisit $dsRegularVisit): bool {
                    if ($dsRegularVisit->getId() === $this->dsRegularVisit->getId()) {
                        return true;
                    }
                    return false;
                }),
                Mockery::on(function (DSUser $dsUserRole): bool {
                    if ($dsUserRole->getId() === $this->dsUserRole->getId()) {
                        return true;
                    }
                    return false;
                }),
                $this->dsAuthenticated,
                $iDataBaseDeleteRegularVisit
            )
            //
        ;

        $args = [
            'checkAuthentication' => $this->checkAuthentication,
            'iDataBaseDeleteRegularVisit' => $iDataBaseDeleteRegularVisit,
            'regularVisitDeletion' => $regularVisitDeletion
        ];

        $response = (new VisitsController(...$args))->regularDestroy($this->regularVisit->getKey(), $this->userRole->user->getKey());
        $this->assertInstanceOf(Response::class, $response);
        $this->assertIsString($response->original);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
