<?php

namespace App\Http\Controllers;

use App\Auth\CheckAuthentication;
use App\Http\Requests\Roles\DestroyRequest;
use App\Http\Requests\Roles\ShowAllRequest;
use App\Http\Requests\Roles\ShowRequest;
use App\Http\Requests\Roles\StoreRequest;
use App\Http\Requests\Roles\UpdateRequest;
use App\Models\Privilege;
use App\Models\PrivilegeValue;
use App\Models\Role;
use App\Models\User;
use Database\Interactions\Privileges\DataBaseCreateRole;
use Database\Interactions\Privileges\DataBaseDeleteRole;
use Database\Interactions\Privileges\PrivilegeSetter;
use Database\Traits\ResolveUserModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use TheClinicDataStructures\DataStructures\User\DSAdmin;
use TheClinicUseCases\Privileges\PrivilegesManagement;
use TheClinicUseCases\Privileges\Interfaces\IDataBaseCreateRole;
use TheClinicUseCases\Privileges\Interfaces\IDataBaseDeleteRole;
use TheClinicUseCases\Privileges\Interfaces\IPrivilegeSetter;

class RolesController extends Controller
{
    use ResolveUserModel;

    private PrivilegesManagement $privilegesManagement;

    private CheckAuthentication $checkAuthentication;

    private IDataBaseCreateRole $iDataBaseCreateRole;

    private IDataBaseDeleteRole $iDataBaseDeleteRole;

    private IPrivilegeSetter $ips;

    public function __construct(
        CheckAuthentication|null $checkAuthentication = null,
        PrivilegesManagement|null $privilegesManagement = null,
        IDataBaseCreateRole|null $iDataBaseCreateRole = null,
        IDataBaseDeleteRole|null $iDataBaseDeleteRole = null,
        IPrivilegeSetter|null $ips = null

    ) {
        $this->privilegesManagement = $privilegesManagement ?: new PrivilegesManagement;
        $this->checkAuthentication = $checkAuthentication ?: new CheckAuthentication;
        $this->iDataBaseCreateRole = $iDataBaseCreateRole ?: new DataBaseCreateRole;
        $this->iDataBaseDeleteRole = $iDataBaseDeleteRole ?: new DataBaseDeleteRole;
        $this->ips = $ips ?: new PrivilegeSetter;
    }

    public function index(): JsonResponse
    {
        $authenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        if ($authenticated instanceof DSAdmin) {
            return response()->json(Role::query()->get()->all());
        } else {
            return response()->json(['message' => trans_choice('auth.User-Not-Authorized', 0)], 403);
        }
    }

    public function store(StoreRequest $request)
    {
        $validateInput = $request->safe()->all();
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        $this->privilegesManagement->createRole($dsAuthenticated, $validateInput['customRoleName'], $validateInput['privilegeValue'], $validateInput['role'], $this->iDataBaseCreateRole);

        return response('New role successfully created.');
    }

    public function update(UpdateRequest $request)
    {
        $validateInput = $request->safe()->all();
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        $this->privilegesManagement->setRolePrivilege($dsAuthenticated, $validateInput['roleName'], $validateInput['privilegeValues'], $this->ips);

        return response('The privilege successfully changed.');
    }

    public function showAll(ShowAllRequest $request): JsonResponse
    {
        $input = $request->all();

        $temp = DB::table((new Privilege)->getTable())
            ->select([(new Privilege)->getTable() . '.name', (new PrivilegeValue)->getTable() . '.privilegeValue'])
            ->join((new PrivilegeValue)->getTable(), (new PrivilegeValue)->getTable() . '.' . (new Privilege)->getForeignKey(), '=', (new Privilege)->getTable() . '.' . (new Privilege)->getKeyName())
            ->join((new Role)->getTable(), (new Role)->getTable() . '.' . (new Role)->getKeyName(), '=', (new PrivilegeValue)->getTable() . '.' . (new Role)->getForeignKey())
            ->join((new User)->getTable(), (new User)->getTable() . '.' . (new Role)->getForeignKeyForName(), '=', (new Role)->getTable() . '.name')
            ->where((new User)->getTable() . '.' . (new User)->getKeyName(), '=', $input['accountId'])
            // ->where((new Privilege)->getTable() . '.name', '=', $input['privilege'])
            ->get()
            ->toArray()
            //
        ;

        $result = [];
        array_walk($temp, function ($v, $k) use (&$result) {
            if (!$v->privilegeValue) {
                return;
            }

            $result[$v->name] = $v->privilegeValue;
        });

        return response()->json($result);
    }

    public function show(ShowRequest $request): JsonResponse
    {
        $input = $request->safe()->all();

        $result = DB::table((new Privilege)->getTable())
            ->select((new PrivilegeValue)->getTable() . '.privilegeValue')
            ->join((new PrivilegeValue)->getTable(), (new PrivilegeValue)->getTable() . '.' . (new Privilege)->getForeignKey(), '=', (new Privilege)->getTable() . '.' . (new Privilege)->getKeyName())
            ->join((new Role)->getTable(), (new Role)->getTable() . '.' . (new Role)->getKeyName(), '=', (new PrivilegeValue)->getTable() . '.' . (new Role)->getForeignKey())
            ->join((new User)->getTable(), (new User)->getTable() . '.' . (new Role)->getForeignKeyForName(), '=', (new Role)->getTable() . '.name')
            ->where((new User)->getTable() . '.' . (new User)->getKeyName(), '=', $input['accountId'])
            ->where((new Privilege)->getTable() . '.name', '=', $input['privilege'])
            ->get()
            ->map(function ($v, $k) {
                return $v->privilegeValue;
            })
            ->toArray()
            //
        ;

        return response()->json([$input['privilege'] => count($result) === 1 ? $result[0] : null]);
    }

    public function destroy(DestroyRequest $request)
    {
        $validateInput = $request->safe()->all();

        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        $this->privilegesManagement->deleteRole($dsAuthenticated, $validateInput['customRoleName'], $this->iDataBaseDeleteRole);

        return response('Requested role successfully deleted.');
    }
}
