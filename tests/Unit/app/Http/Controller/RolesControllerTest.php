<?php

namespace Tests\Unit\app\Http\Controller;

use App\Auth\CheckAuthentication;
use App\Http\Controllers\RolesController;
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
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Privileges\Interfaces\IDataBaseCreateRole;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        /** @var \TheClinicUseCases\Privileges\PrivilegesManagement|\Mockery\MockInterface $privilegesManagement */
        $this->privilegesManagement = Mockery::mock(PrivilegesManagement::class);

        /** @var IDataBaseCreateRole|\Mockery\MockInterface $iDataBaseCreateRole */
        $this->iDataBaseCreateRole = Mockery::mock(IDataBaseCreateRole::class);
    }

    private function instantiate(): RolesController
    {
        return new RolesController($this->checkAuthentication, $this->privilegesManagement, $this->iDataBaseCreateRole);
    }

    public function testRun()
    {
        $methods = [
            'testIndex',
            'testStore',
            'testUpdate',
            'testShow',
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

        /** @var Request|\Mockery\Mockinterface $request */
        $request = Mockery::mock(Request::class);
        $request->customRoleName = '';
        $request->privilegeValue = ['name' => 'value'];

        $this->privilegesManagement
            ->shouldReceive('createRole')
            ->with($dsUser, $request->customRoleName, $request->privilegeValue, $this->iDataBaseCreateRole)
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

        /** @var Request|\Mockery\Mockinterface $request */
        $request = Mockery::mock(Request::class);
        $request->accountId = $this->user->{(new PatientRole)->getKeyName()};
        $request->privilege = $privilege;
        $request->value = $value;

        $this->privilegesManagement->shouldReceive('setUserPrivilege')
            ->with($dsAdminUser, \Mockery::on(function (DSUser $value) use ($request) {
                if ($value->getId() === $request->accountId) {
                    return true;
                }
                return false;
            }), $privilege, $value);

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

        $result = $this->instantiate()->show($user->{(new PatientRole)->getKeyName()}, true);
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertCount(0, $result->original);

        $this->privilegesManagement->shouldReceive('getUserPrivileges')
            ->with($this->dsUser, $dsUser)
            ->andReturn([]);

        $result = $this->instantiate()->show($user->{(new PatientRole)->getKeyName()});
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertCount(0, $result->original);
    }
}
