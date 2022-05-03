<?php

namespace App\Http\Controllers;

use App\Auth\CheckAuthentication;
use App\Http\Requests\Roles\ShowRequest;
use App\Http\Requests\Roles\StoreRequest;
use App\Http\Requests\Roles\UpdateRequest;
use App\Models\User;
use Database\Interactions\Privileges\DataBaseCreateRole;
use Database\Interactions\Privileges\DataBaseDeleteRole;
use Database\Interactions\Privileges\Privileges;
use Database\Traits\ResolveUserModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use TheClinicDataStructures\DataStructures\User\Interfaces\IPrivilege;
use TheClinicUseCases\Privileges\PrivilegesManagement;
use TheClinicUseCases\Privileges\Interfaces\IDataBaseCreateRole;
use TheClinicUseCases\Privileges\Interfaces\IDataBaseDeleteRole;

class RolesController extends Controller
{
    use ResolveUserModel;

    private PrivilegesManagement $privilegesManagement;

    private CheckAuthentication $checkAuthentication;

    private IDataBaseCreateRole $iDataBaseCreateRole;

    private IDataBaseDeleteRole $iDataBaseDeleteRole;

    public function __construct(
        CheckAuthentication|null $checkAuthentication = null,
        PrivilegesManagement|null $privilegesManagement = null,
        IDataBaseCreateRole|null $iDataBaseCreateRole = null,
        IDataBaseDeleteRole|null $iDataBaseDeleteRole = null,
        IPrivilege|null $ip = null

    ) {
        $this->privilegesManagement = $privilegesManagement ?: new PrivilegesManagement;
        $this->checkAuthentication = $checkAuthentication ?: new CheckAuthentication;
        $this->iDataBaseCreateRole = $iDataBaseCreateRole ?: new DataBaseCreateRole;
        $this->iDataBaseDeleteRole = $iDataBaseDeleteRole ?: new DataBaseDeleteRole;
        $this->ip = $ip ?: new Privileges;
    }

    public function index(): JsonResponse
    {
        $authenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        return response()->json($this->privilegesManagement->getPrivileges($authenticated));
    }

    public function store(StoreRequest $request)
    {
        $validateInput = $request->safe()->all();
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        $this->privilegesManagement->createRole($dsAuthenticated, $validateInput['customRoleName'], $validateInput['privilegeValue'], $this->iDataBaseCreateRole);

        return response('New role successfully created.');
    }

    public function update(UpdateRequest $request)
    {
        $authenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        /** @var \App\Models\User $user */
        $user = User::query()->whereKey($request->accountId)->firstOrFail();
        $dsUser = $user->authenticatableRole()->getDataStructure();

        $this->privilegesManagement->setUserPrivilege($authenticated, $dsUser, $request->privilege, $request->value, $this->ip);

        return response('The privilege successfully changed.');
    }

    public function show(ShowRequest $request): JsonResponse
    {
        $validateInput = $request->safe()->all();
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        if (isset($validateInput['self']) && $validateInput['self']) {
            return response()->json($this->privilegesManagement->getSelfPrivileges($dsAuthenticated));
        } else {
            /** @var \App\Models\User $user */
            $user = User::query()->where((new User)->getKeyName(), '=', $validateInput['accountId'])->first();
            $dsUser = $user->authenticatableRole()->getDataStructure();

            return response()->json($this->privilegesManagement->getUserPrivileges(
                $dsAuthenticated,
                $dsUser
            ));
        }
    }

    public function destroy(Request $request)
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        $this->privilegesManagement->deleteRole($dsAuthenticated, $request->customRoleName, $this->iDataBaseDeleteRole);

        return response('New role successfully deleted.');
    }
}
