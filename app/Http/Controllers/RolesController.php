<?php

namespace App\Http\Controllers;

use App\Auth\CheckAuthentication;
use App\Models\Auth\User as Authenticatable;
use App\Models\Role;
use App\Models\User;
use Database\Traits\ResolveUserModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Privileges\PrivilegesManagement;

class RolesController extends Controller
{
    use ResolveUserModel;

    private PrivilegesManagement $privilegesManagement;

    private CheckAuthentication $checkAuthentication;

    public function __construct(
        CheckAuthentication|null $checkAuthentication = null,
        PrivilegesManagement|null $privilegesManagement = null
    ) {
        $this->privilegesManagement = $privilegesManagement ?: new PrivilegesManagement;
        $this->checkAuthentication = $checkAuthentication ?: new CheckAuthentication;
    }

    public function index(): JsonResponse
    {
        $authenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        return response()->json($this->privilegesManagement->getPrivileges($authenticated));
    }

    public function store(Request $request)
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
