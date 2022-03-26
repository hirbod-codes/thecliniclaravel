<?php

namespace Tests\Unit\app\Http\Controller;

use TheClinicUseCases\Privileges\PrivilegesManagement;
use App\Auth\CheckAuthentication;
use App\Http\Controllers\AuthController;
use TheClinicUseCases\Accounts\AccountsManagement;
use Database\Interactions\Accounts\DataBaseCreateAccount;
use Database\Traits\ResolveUserModel;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseCreateAccount;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;
use TheClinicUseCases\Accounts\Authentication;

class AuthControllerTest extends TestCase
{
    use GetAuthenticatables;

    private Generator $faker;

    private Authentication|MockInterface $authentication;

    private PrivilegesManagement|MockInterface $privilegesManagement;

    private CheckAuthentication|MockInterface $checkAuthentication;

    private AccountsManagement|MockInterface $accountsManagement;

    private DataBaseCreateAccount|MockInterface $dataBaseCreateAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        /** @var \TheClinicUseCases\Accounts\Authentication|\Mockery\MockInterface $authentication */
        $this->authentication = Mockery::mock(Authentication::class);

        /** @var \TheClinicUseCases\Privileges\PrivilegesManagement|\Mockery\MockInterface $privilegesManagement */
        $this->privilegesManagement = Mockery::mock(PrivilegesManagement::class);

        /** @var \App\Auth\CheckAuthentication|\Mockery\MockInterface $checkAuthentication */
        $this->checkAuthentication = Mockery::mock(CheckAuthentication::class);

        /** @var \TheClinicUseCases\Privileges\PrivilegesManagement|\Mockery\MockInterface $privilegesManagement */
        $this->privilegesManagement = Mockery::mock(PrivilegesManagement::class);

        /** @var \TheClinicUseCases\Accounts\Interfaces\IDataBaseDeleteAccount|\Mockery\MockInterface $dataBaseCreateAccount */
        $this->dataBaseCreateAccount = Mockery::mock(IDataBaseCreateAccount::class);

        /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
        $this->accountsManagement = Mockery::mock(AccountsManagement::class);
    }

    private function instantiate(): AuthController
    {
        return new AuthController(
            $this->authentication,
            $this->privilegesManagement,
            $this->checkAuthentication,
            $this->accountsManagement,
            $this->dataBaseCreateAccount,
        );
    }

    public function testRegister(): void
    {
        $ruleName = 'admin';
        $input = ['role' => 'patient'];
        $requestInput = [];

        /** @var Request|MockInterface $request */
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('all')->andreturn($requestInput);

        $this->accountsManagement
            ->shouldReceive('signupAccount')
            ->with(array_merge($requestInput, $input), $this->dataBaseCreateAccount, $this->checkAuthentication)
            ->andReturn(($user = $this->getAuthenticatable($ruleName))->getDataStructure());

        $response = $this->instantiate()->register($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertCount(count($dsUserArray = $user->getDataStructure()->toArray()), $response->original);

        foreach ($dsUserArray as $key => $value) {
            $this->assertNotFalse(array_search($key, array_keys($response->original)));
            $this->assertEquals($value, $response->original[$key]);
        }
    }
}
