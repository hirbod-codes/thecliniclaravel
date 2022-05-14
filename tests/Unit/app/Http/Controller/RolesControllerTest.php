<?php

namespace Tests\Unit\app\Http\Controller;

use App\Auth\CheckAuthentication;
use App\Http\Controllers\RolesController;
use App\Http\Requests\Roles\DestroyRequest;
use App\Http\Requests\Roles\ShowRequest;
use App\Http\Requests\Roles\StoreRequest;
use App\Http\Requests\Roles\UpdateRequest;
use App\Models\Auth\User;
use App\Models\roles\PatientRole;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;
use TheClinicDataStructures\DataStructures\User\DSPatient;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicDataStructures\DataStructures\User\Interfaces\IPrivilege;
use TheClinicUseCases\Privileges\Interfaces\IDataBaseCreateRole;
use TheClinicUseCases\Privileges\Interfaces\IDataBaseDeleteRole;
use TheClinicUseCases\Privileges\PrivilegesManagement;

class RolesControllerTest extends TestCase
{
    use GetAuthenticatables;

    private Generator $faker;

    private string $ruleName;

    private User $user;

    private DSUser $dsUser;

    /**
     * @var array<string, \App\Models\Auth\User> ['ruleName' => \App\Models\Auth\User, ...]
     */
    private array $users;

    private PrivilegesManagement|MockInterface $privilegesManagement;

    private CheckAuthentication|MockInterface $checkAuthentication;

    private IDataBaseCreateRole|MockInterface $iDataBaseCreateRole;

    private IDataBaseDeleteRole|MockInterface $iDataBaseDeleteRole;

    private IPrivilege|MockInterface $ip;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        /** @var \TheClinicUseCases\Privileges\PrivilegesManagement|\Mockery\MockInterface $privilegesManagement */
        $this->privilegesManagement = Mockery::mock(PrivilegesManagement::class);

        /** @var IDataBaseCreateRole|\Mockery\MockInterface $iDataBaseCreateRole */
        $this->iDataBaseCreateRole = Mockery::mock(IDataBaseCreateRole::class);

        /** @var IDataBaseDeleteRole|\Mockery\MockInterface $iDataBaseDeleteRole */
        $this->iDataBaseDeleteRole = Mockery::mock(IDataBaseDeleteRole::class);

        /** @var IPrivilege|\Mockery\MockInterface $ip */
        $this->ip = Mockery::mock(IPrivilege::class);
    }

    private function instantiate(): RolesController
    {
        return new RolesController($this->checkAuthentication, $this->privilegesManagement, $this->iDataBaseCreateRole, $this->iDataBaseDeleteRole, $this->ip);
    }

    public function testRun()
    {
        $methods = [
            'testIndex',
            'testStore',
            'testUpdate',
            'testShow',
            'testDestroy',
        ];

        $this->users = $this->getAuthenticatables();

        foreach ($methods as $method) {
            do {
                // because of perfomance i chose a random user from $this->users.
                $this->ruleName = $this->faker->randomElement(array_keys($this->users));
            } while ($this->ruleName === 'admin');

            $this->user = $this->users[$this->ruleName];

            $this->dsUser = $this->user->getDataStructure();

            /** @var \App\Http\Controllers\CheckAuthentication|\Mockery\MockInterface $checkAuthentication */
            $this->checkAuthentication = Mockery::mock(CheckAuthentication::class);
            $this->checkAuthentication->shouldReceive("getAuthenticatedDSUser")->andReturn($this->dsUser);
            $this->checkAuthentication->shouldReceive("getAuthenticated")->andReturn($this->user);

            $this->{$method}();
        }
    }

    private function testStore(): void
    {
        $user = $this->users['admin'];
        $dsUser = $user->getDataStructure();

        /** @var \App\Http\Controllers\CheckAuthentication|\Mockery\MockInterface $checkAuthentication */
        $this->checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $this->checkAuthentication->shouldReceive("getAuthenticatedDSUser")->andReturn($dsUser);

        $input = [
            'customRoleName' => '',
            'privilegeValue' => ['name' => 'value'],
        ];

        /** @var StoreRequest|\Mockery\Mockinterface $request */
        $request = Mockery::mock(StoreRequest::class);
        $request->shouldReceive('safe->all')->andReturn($input);

        $this->privilegesManagement
            ->shouldReceive('createRole')
            ->with($dsUser, $input['customRoleName'], $input['privilegeValue'], $this->iDataBaseCreateRole)
            //
        ;

        $response = $this->instantiate()->store($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('New role successfully created.', $response->original);
    }

    private function testIndex()
    {
        $user = $this->users['admin'];
        $dsUser = $user->getDataStructure();

        /** @var \App\Http\Controllers\CheckAuthentication|\Mockery\MockInterface $checkAuthentication */
        $this->checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $this->checkAuthentication->shouldReceive("getAuthenticatedDSUser")->andReturn($dsUser);

        $this->privilegesManagement->shouldReceive('getPrivileges')
            ->with($dsUser)
            ->andReturn([]);

        $jsonResponse = $this->instantiate()->index();
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(0, $jsonResponse->original);
    }

    private function testUpdate(): void
    {
        $privilege = 'privilege';
        $value = 'value';
        $adminUser = $this->users['admin'];
        $dsAdminUser = $adminUser->getDataStructure();

        /** @var \App\Http\Controllers\CheckAuthentication|\Mockery\MockInterface $checkAuthentication */
        $this->checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $this->checkAuthentication->shouldReceive("getAuthenticatedDSUser")->andReturn($dsAdminUser);
        $this->checkAuthentication->shouldReceive("getAuthenticated")->andReturn($adminUser);

        $input = [
            'accountId' => $this->user->{(new PatientRole)->getKeyName()},
            'privilege' => $privilege,
            'value' => $value,
        ];

        /** @var UpdateRequest|\Mockery\Mockinterface $request */
        $request = Mockery::mock(UpdateRequest::class);
        $request->shouldReceive('safe->all')->andReturn($input);

        $this->privilegesManagement->shouldReceive('setUserPrivilege')
            ->with($dsAdminUser, \Mockery::on(function (DSUser $value) use ($input) {
                if ($value->getId() === $input['accountId']) {
                    return true;
                }
                return false;
            }), $privilege, $value, $this->ip);

        $result = $this->instantiate()->update($request);
        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals('The privilege successfully changed.', $result->original);
    }

    private function testShow(): void
    {
        $user = $this->getAuthenticatable('patient');
        $dsUser = $user->getDataStructure();

        $this->privilegesManagement->shouldReceive('getSelfPrivileges')
            ->with($this->dsUser)
            ->andReturn([]);

        /** @var ShowRequest|MockInterface $request */
        $request = Mockery::mock(ShowRequest::class);
        $request->shouldReceive('safe->all')
            ->andReturn([
                'self' => true
            ])
            //
        ;

        $result = $this->instantiate()->show($request);
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertCount(0, $result->original);

        $request->shouldReceive('safe->all')
            ->andReturn([
                'accountId' => 90
            ])
            //
        ;

        $this->privilegesManagement->shouldReceive('getUserPrivileges')
            ->with($this->dsUser, Mockery::on(function (DSUser $value) use ($dsUser) {
                if ($value instanceof DSPatient && $value->getUsername() === $dsUser->getUsername()) {
                    return true;
                } else {
                    return false;
                }
            }))
            ->andReturn([]);

        $result = $this->instantiate()->show($request);
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertCount(0, $result->original);
    }

    private function testDestroy(): void
    {
        $user = $this->users['admin'];
        $dsUser = $user->getDataStructure();

        /** @var \App\Http\Controllers\CheckAuthentication|\Mockery\MockInterface $checkAuthentication */
        $this->checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $this->checkAuthentication->shouldReceive("getAuthenticatedDSUser")->andReturn($dsUser);

        $input = [
            'customRoleName' => '',
        ];

        /** @var DestroyRequest|\Mockery\Mockinterface $request */
        $request = Mockery::mock(DestroyRequest::class);
        $request->shouldReceive('safe->all')->andReturn($input);

        $this->privilegesManagement
            ->shouldReceive('deleteRole')
            ->with($dsUser, $input['customRoleName'], $this->iDataBaseDeleteRole)
            //
        ;

        $response = $this->instantiate()->destroy($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('Requested role successfully deleted.', $response->original);
    }
}
