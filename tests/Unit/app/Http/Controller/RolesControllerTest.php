<?php

namespace Tests\Unit\app\Http\Controller;

use App\Auth\CheckAuthentication;
use App\Http\Controllers\RolesController;
use App\Http\Requests\Roles\DestroyRequest;
use App\Http\Requests\Roles\ShowRequest;
use App\Http\Requests\Roles\StoreRequest;
use App\Http\Requests\Roles\UpdateRequest;
use App\Models\Auth\User;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;
use App\PoliciesLogicDataStructures\DataStructures\User\DSPatient;
use App\PoliciesLogicDataStructures\DataStructures\User\DSUser;
use App\PoliciesLogicUseCases\Privileges\Interfaces\IDataBaseCreateRole;
use App\PoliciesLogicUseCases\Privileges\Interfaces\IDataBaseDeleteRole;
use App\PoliciesLogicUseCases\Privileges\Interfaces\IPrivilegeSetter;
use App\PoliciesLogicUseCases\Privileges\PrivilegesManagement;

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

    private IPrivilegeSetter|MockInterface $ips;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        /** @var \App\PoliciesLogicUseCases\Privileges\PrivilegesManagement|\Mockery\MockInterface $privilegesManagement */
        $this->privilegesManagement = Mockery::mock(PrivilegesManagement::class);

        /** @var IDataBaseCreateRole|\Mockery\MockInterface $iDataBaseCreateRole */
        $this->iDataBaseCreateRole = Mockery::mock(IDataBaseCreateRole::class);

        /** @var IDataBaseDeleteRole|\Mockery\MockInterface $iDataBaseDeleteRole */
        $this->iDataBaseDeleteRole = Mockery::mock(IDataBaseDeleteRole::class);

        /** @var IPrivilegeSetter|\Mockery\MockInterface $ips */
        $this->ips = Mockery::mock(IPrivilegeSetter::class);
    }

    private function instantiate(): RolesController
    {
        return new RolesController($this->checkAuthentication, $this->privilegesManagement, $this->iDataBaseCreateRole, $this->iDataBaseDeleteRole, $this->ips);
    }

    public function testRun()
    {
        $methods = [
            'testStore',
            'testUpdate',
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
            'role' => '',
        ];

        /** @var StoreRequest|\Mockery\Mockinterface $request */
        $request = Mockery::mock(StoreRequest::class);
        $request->shouldReceive('safe->all')->andReturn($input);

        $this->privilegesManagement
            ->shouldReceive('createRole')
            ->with($dsUser, $input['customRoleName'], $input['privilegeValue'], $input['role'], $this->iDataBaseCreateRole)
            //
        ;

        $response = $this->instantiate()->store($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('New role successfully created.', $response->original);
    }

    private function testUpdate(): void
    {
        $user = $this->users['admin'];
        $dsUser = $user->getDataStructure();

        /** @var \App\Http\Controllers\CheckAuthentication|\Mockery\MockInterface $checkAuthentication */
        $this->checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $this->checkAuthentication->shouldReceive("getAuthenticatedDSUser")->andReturn($dsUser);

        $roleName = 'custom_role';
        $privilegeValues = ['privilege' => 'value'];

        $input = [
            'roleName' => $roleName,
            'privilegeValues' => $privilegeValues,
        ];

        /** @var UpdateRequest|\Mockery\Mockinterface $request */
        $request = Mockery::mock(UpdateRequest::class);
        $request->shouldReceive('safe->all')->andReturn($input);

        $this->privilegesManagement->shouldReceive('setRolePrivilege')
            ->with($dsUser, $roleName, $privilegeValues, $this->ips);

        $result = $this->instantiate()->update($request);
        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals('The privilege successfully changed.', $result->original);
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
