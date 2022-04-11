<?php

namespace App\Http\Controllers;

use App\Auth\CheckAuthentication;
use App\Models\User;
use Database\Traits\ResolveUserModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use TheClinicUseCases\Privileges\PrivilegesManagement;
use TheClinicUseCases\Privileges\Interfaces\IDataBaseCreateRole;

class RolesController extends Controller
{
    use ResolveUserModel;

    private PrivilegesManagement $privilegesManagement;

    private CheckAuthentication $checkAuthentication;

    public function __construct(
        CheckAuthentication|null $checkAuthentication = null,
        PrivilegesManagement|null $privilegesManagement = null,
        IDataBaseCreateRole|null $iDataBaseCreateRole = null

    ) {
        $this->privilegesManagement = $privilegesManagement ?: new PrivilegesManagement;
        $this->checkAuthentication = $checkAuthentication ?: new CheckAuthentication;
        $this->iDataBaseCreateRole = $iDataBaseCreateRole ?: new IDataBaseCreateRole;
    }

    public function index(): JsonResponse
    {
        $authenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        return response()->json($this->privilegesManagement->getPrivileges($authenticated));
    }

    public function store(Request $request)
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        $this->privilegesManagement->createRole($dsAuthenticated, $request->customRoleName, $request->privilegeValue, $this->iDataBaseCreateRole);

        return response('New role successfully created.');
    }

    public function update(Request $request)
    {
        $authenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        /** @var \App\Models\User $user */
        $user = User::query()->where((new User)->getKeyName(), '=', $request->accountId)->first();
        $dsUser = $user->authenticatableRole()->getDataStructure();

        $this->privilegesManagement->setUserPrivilege($authenticated, $dsUser, $request->privilege, $request->value);

        return response('The privilege successfully changed.');
    }

    public function show(bool|null $self = null, int|null $accountId = null): JsonResponse
    {
        $authenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        if (!is_null($self) && $self) {
            return response()->json($this->privilegesManagement->getSelfPrivileges($authenticated));
        } else {
            /** @var \App\Models\User $user */
            $user = User::query()->where((new User)->getKeyName(), '=', $accountId)->first();
            $dsUser = $user->authenticatableRole()->getDataStructure();

            return response()->json($this->privilegesManagement->getUserPrivileges(
                $authenticated,
                $dsUser
            ));
        }
    }
}
