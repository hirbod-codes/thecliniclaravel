<?php

namespace App\Http\Controllers;

use App\Auth\CheckAuthentication;
use App\Http\Requests\Roles\DataTypeRequest;
use App\Http\Requests\Roles\DestroyRequest;
use App\Http\Requests\Roles\ShowAllRequest;
use App\Http\Requests\Roles\ShowRoleNameRequest;
use App\Http\Requests\Roles\StoreRequest;
use App\Http\Requests\Roles\UpdateRequest;
use App\Models\Business;
use App\Models\Privilege;
use App\Models\PrivilegeName;
use App\Models\Privileges\CreateOrder;
use App\Models\Privileges\CreateUser;
use App\Models\Privileges\CreateVisit;
use App\Models\Privileges\DeleteOrder;
use App\Models\Privileges\DeleteUser;
use App\Models\Privileges\DeleteVisit;
use App\Models\Privileges\RetrieveOrder;
use App\Models\Privileges\RetrieveUser;
use App\Models\Privileges\RetrieveVisit;
use App\Models\Privileges\UpdateUser;
use App\Models\Role;
use App\Models\RoleName;
use App\Models\Roles\AdminRole;
use App\Models\Roles\DoctorRole;
use App\Models\Roles\OperatorRole;
use App\Models\Roles\PatientRole;
use App\Models\Roles\SecretaryRole;
use App\Models\User;
use App\Models\UserColumn;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RolesController extends Controller
{
    public function dataType(DataTypeRequest $request): Response
    {
        $input = $request->safe()->all();
        $dataType = strtolower(class_basename(RoleName::query()->where('name', '=', $input['roleName'])->firstOrFail()->childRoleModel->getUserTypeModelFullName()));

        return response($dataType);
    }

    public function index()
    // : JsonResponse
    {
        // return response()->json(Role::query()->get()->all());
    }

    public function store(StoreRequest $request)
    {
        //
    }

    public function update(UpdateRequest $request)
    {
        //
    }

    public function showAll(ShowAllRequest $request)
    // : JsonResponse
    {
        //
    }

    public function showRoleName(ShowRoleNameRequest $request): Response
    {
        $input = $request->safe()->all();
        $childRoleModel = User::query()->whereKey($input['accountId'])->firstOrFail()->authenticatableRole->role;
        $authenticatedRoleName = $childRoleModel->roleName->name;

        return response($authenticatedRoleName);
    }

    public function show(): JsonResponse
    {
        $childRoleModel = (new CheckAuthentication)->getAuthenticated()->authenticatableRole->role;
        $authenticatedRoleName = $childRoleModel->roleName->name;
        /** @var Role $role */
        $role = $childRoleModel->role;

        $roles = array_merge(
            $this->getRoleName(),
            $this->getPrivilege(),
            $this->getPrivilegeName(),
            $this->getBusiness(),
            $this->getUserColumn(),
            $this->getCreateUser($role),
            $this->getCreateOrder($role),
            $this->getCreateVisit($role),
            $this->getRetrieveUser($role),
            $this->getRetrieveOrder($role),
            $this->getRetrieveVisit($role),
            $this->getUpdateUser($role),
            $this->getDeleteUser($role),
            $this->getDeleteOrder($role),
            $this->getDeleteVisit($role),
        );

        $businessTable = Str::camel((new Business)->getTable());
        $roleNameTable = Str::camel((new RoleName)->getTable());
        $businessForeignName = (new Business)->getForeignKey();
        $roleForeignKey = $role->getForeignKey();
        $privilegeNameTable = Str::camel((new PrivilegeName)->getTable());
        $userColumnTable = Str::camel((new UserColumn)->getTable());
        foreach ($roles as $k => &$v) {
            switch ($k) {
                case Str::camel((new CreateUser)->getTable()):
                    $temp = [];
                    foreach ($v as $attributes) {
                        if ($attributes['object'] === null) {
                            $temp[] = 'self';
                            continue;
                        }
                        $temp[] = $this->findRoleName($roles[$roleNameTable], $attributes['object'])['name'];
                    }
                    $v = $temp;
                    break;

                case Str::camel((new CreateOrder)->getTable()):
                    $temp = [];
                    foreach ($v as $attributes) {
                        if (!isset($temp[$this->findBusiness($roles[$businessTable], $attributes[$businessForeignName])['name']])) {
                            $temp[$this->findBusiness($roles[$businessTable], $attributes[$businessForeignName])['name']] = [];
                        }

                        if ($attributes['object'] === null) {
                            $temp[$this->findBusiness($roles[$businessTable], $attributes[$businessForeignName])['name']][] = 'self';
                            continue;
                        }
                        $temp[$this->findBusiness($roles[$businessTable], $attributes[$businessForeignName])['name']][] = $this->findRoleName($roles[$roleNameTable], $attributes['object'])['name'];
                    }
                    $v = $temp;
                    break;

                case Str::camel((new CreateVisit)->getTable()):
                    $temp = [];
                    foreach ($v as $attributes) {
                        if (!isset($temp[$this->findBusiness($roles[$businessTable], $attributes[$businessForeignName])['name']])) {
                            $temp[$this->findBusiness($roles[$businessTable], $attributes[$businessForeignName])['name']] = [];
                        }

                        if ($attributes['object'] === null) {
                            $temp[$this->findBusiness($roles[$businessTable], $attributes[$businessForeignName])['name']][] = 'self';
                            continue;
                        }
                        $temp[$this->findBusiness($roles[$businessTable], $attributes[$businessForeignName])['name']][] = $this->findRoleName($roles[$roleNameTable], $attributes['object'])['name'];
                    }
                    $v = $temp;
                    break;

                case Str::camel((new RetrieveUser)->getTable()):
                    $temp = [];
                    foreach ($v as $attributes) {
                        if ($attributes['object'] === null) {
                            $temp[] = 'self';
                            continue;
                        }
                        $temp[] = $this->findRoleName($roles[$roleNameTable], $attributes['object'])['name'];
                    }
                    $v = $temp;
                    break;

                case Str::camel((new RetrieveOrder)->getTable()):
                    $temp = [];
                    foreach ($v as $attributes) {
                        if (!isset($temp[$this->findBusiness($roles[$businessTable], $attributes[$businessForeignName])['name']])) {
                            $temp[$this->findBusiness($roles[$businessTable], $attributes[$businessForeignName])['name']] = [];
                        }

                        if ($attributes['object'] === null) {
                            $temp[$this->findBusiness($roles[$businessTable], $attributes[$businessForeignName])['name']][] = 'self';
                            continue;
                        }
                        $temp[$this->findBusiness($roles[$businessTable], $attributes[$businessForeignName])['name']][] = $this->findRoleName($roles[$roleNameTable], $attributes['object'])['name'];
                    }
                    $v = $temp;
                    break;

                case Str::camel((new RetrieveVisit)->getTable()):
                    $temp = [];
                    foreach ($v as $attributes) {
                        if (!isset($temp[$this->findBusiness($roles[$businessTable], $attributes[$businessForeignName])['name']])) {
                            $temp[$this->findBusiness($roles[$businessTable], $attributes[$businessForeignName])['name']] = [];
                        }

                        if ($attributes['object'] === null) {
                            $temp[$this->findBusiness($roles[$businessTable], $attributes[$businessForeignName])['name']][] = 'self';
                            continue;
                        }
                        $temp[$this->findBusiness($roles[$businessTable], $attributes[$businessForeignName])['name']][] = $this->findRoleName($roles[$roleNameTable], $attributes['object'])['name'];
                    }
                    $v = $temp;
                    break;

                case Str::camel((new UpdateUser)->getTable()):
                    $temp = [];
                    foreach ($v as $attributes) {
                        if ($attributes['object'] === null) {
                            $temp[] = ['name' => 'self', 'column' => $this->findUserColumn($roles[$userColumnTable], $attributes[(new UserColumn)->getForeignKey()])['name']];
                            continue;
                        }
                        $temp[] = [
                            'name' => $this->findRoleName($roles[$roleNameTable], $attributes['object'])['name'],
                            'column' => $this->findUserColumn($roles[$userColumnTable], $attributes[(new UserColumn)->getForeignKey()])['name']
                        ];
                    }
                    $v = $temp;
                    break;

                case Str::camel((new DeleteUser)->getTable()):
                    $temp = [];
                    foreach ($v as $attributes) {
                        if ($attributes['object'] === null) {
                            $temp[] = 'self';
                            continue;
                        }
                        $temp[] = $this->findRoleName($roles[$roleNameTable], $attributes['object'])['name'];
                    }
                    $v = $temp;
                    break;

                case Str::camel((new DeleteOrder)->getTable()):
                    $temp = [];
                    foreach ($v as $attributes) {
                        if (!isset($temp[$this->findBusiness($roles[$businessTable], $attributes[$businessForeignName])['name']])) {
                            $temp[$this->findBusiness($roles[$businessTable], $attributes[$businessForeignName])['name']] = [];
                        }

                        if ($attributes['object'] === null) {
                            $temp[$this->findBusiness($roles[$businessTable], $attributes[$businessForeignName])['name']][] = 'self';
                            continue;
                        }
                        $temp[$this->findBusiness($roles[$businessTable], $attributes[$businessForeignName])['name']][] = $this->findRoleName($roles[$roleNameTable], $attributes['object'])['name'];
                    }
                    $v = $temp;
                    break;

                case Str::camel((new DeleteVisit)->getTable()):
                    $temp = [];
                    foreach ($v as $attributes) {
                        if (!isset($temp[$this->findBusiness($roles[$businessTable], $attributes[$businessForeignName])['name']])) {
                            $temp[$this->findBusiness($roles[$businessTable], $attributes[$businessForeignName])['name']] = [];
                        }

                        if ($attributes['object'] === null) {
                            $temp[$this->findBusiness($roles[$businessTable], $attributes[$businessForeignName])['name']][] = 'self';
                            continue;
                        }
                        $temp[$this->findBusiness($roles[$businessTable], $attributes[$businessForeignName])['name']][] = $this->findRoleName($roles[$roleNameTable], $attributes['object'])['name'];
                    }
                    $v = $temp;
                    break;

                case Str::camel((new Privilege)->getTable()):
                    $temp = [];
                    foreach ($v as $attributes) {
                        if (!isset($temp[$this->findPrivilegeName($roles[$privilegeNameTable], $attributes[(new PrivilegeName)->getForeignKey()])['name']])) {
                            $temp[$this->findPrivilegeName($roles[$privilegeNameTable], $attributes[(new PrivilegeName)->getForeignKey()])['name']] = [];
                        }

                        $value = ['string_value' => $attributes['string_value'], 'integer_value' => $attributes['integer_value'], 'boolean_value' => $attributes['boolean_value'], 'timestamp_value' => $attributes['timestamp_value'], 'json_value' => $attributes['json_value']];
                        if ($attributes['object'] === null) {
                            $temp[$this->findPrivilegeName($roles[$privilegeNameTable], $attributes[(new PrivilegeName)->getForeignKey()])['name']]['self'] = $value;
                            continue;
                        }
                        $temp[$this->findPrivilegeName($roles[$privilegeNameTable], $attributes[(new PrivilegeName)->getForeignKey()])['name']][$this->findRoleName($roles[$roleNameTable], $attributes['object'])['name']] = $value;
                    }
                    $v = $temp;
                    break;
                default:
                    break;
            }
        }

        $roles['role'] = $authenticatedRoleName;

        unset($roles[$roleNameTable]);
        unset($roles[$privilegeNameTable]);
        unset($roles[$businessTable]);
        unset($roles[$userColumnTable]);

        return response()->json($roles);
    }

    public function findRoleName(array $roleNames, int $key): array
    {
        $roleNameKeyName = (new RoleName)->getKeyName();
        foreach ($roleNames as $roleName) {
            if ($roleName[$roleNameKeyName] === $key) {
                return $roleName;
            }
        }
    }

    public function findPrivilegeName(array $privilegeNames, int $key): array
    {
        $privilegeNameKeyName = (new PrivilegeName)->getKeyName();
        foreach ($privilegeNames as $privilegeName) {
            if ($privilegeName[$privilegeNameKeyName] === $key) {
                return $privilegeName;
            }
        }
    }

    public function findBusiness(array $businesss, int $key): array
    {
        $businessKeyName = (new Business)->getKeyName();
        foreach ($businesss as $business) {
            if ($business[$businessKeyName] === $key) {
                return $business;
            }
        }
    }

    public function findUserColumn(array $userColumns, int $key): array
    {
        $userColumnKeyName = (new UserColumn)->getKeyName();
        foreach ($userColumns as $userColumn) {
            if ($userColumn[$userColumnKeyName] === $key) {
                return $userColumn;
            }
        }
    }

    public function getRoleName(): array
    {
        $roles = DB::table((new RoleName)->getTable())
            ->get()
            ->toArray()
            //
        ;

        $roles = array_map(function ($obj) {
            $arr = [];
            foreach ($obj as $k => $v) {
                $arr[$k] = $v;
            }
            return Arr::undot($arr);
        }, $roles);

        return [Str::camel((new RoleName)->getTable()) => $roles];
    }

    public function getPrivilegeName(): array
    {
        $roles = DB::table((new PrivilegeName)->getTable())
            ->get()
            ->toArray()
            //
        ;

        $roles = array_map(function ($obj) {
            $arr = [];
            foreach ($obj as $k => $v) {
                $arr[$k] = $v;
            }
            return Arr::undot($arr);
        }, $roles);

        return [Str::camel((new PrivilegeName)->getTable()) => $roles];
    }

    public function getPrivilege(): array
    {
        $roles = DB::table((new Privilege)->getTable())
            ->get()
            ->toArray()
            //
        ;

        $roles = array_map(function ($obj) {
            $arr = [];
            foreach ($obj as $k => $v) {
                $arr[$k] = $v;
            }
            return Arr::undot($arr);
        }, $roles);

        return [Str::camel((new Privilege)->getTable()) => $roles];
    }

    public function getBusiness(): array
    {
        $roles = DB::table((new Business)->getTable())
            ->get()
            ->toArray()
            //
        ;

        $roles = array_map(function ($obj) {
            $arr = [];
            foreach ($obj as $k => $v) {
                $arr[$k] = $v;
            }
            return Arr::undot($arr);
        }, $roles);

        return [Str::camel((new Business)->getTable()) => $roles];
    }

    public function getUserColumn(): array
    {
        $roles = DB::table((new UserColumn)->getTable())
            ->get()
            ->toArray()
            //
        ;

        $roles = array_map(function ($obj) {
            $arr = [];
            foreach ($obj as $k => $v) {
                $arr[$k] = $v;
            }
            return Arr::undot($arr);
        }, $roles);

        return [Str::camel((new UserColumn)->getTable()) => $roles];
    }

    public function getCreateUser(Role $role): array
    {
        $roles = DB::table((new CreateUser)->getTable())
            ->where('subject', '=', $role->getKey())
            ->get()
            ->toArray()
            //
        ;

        $roles = array_map(function ($obj) {
            $arr = [];
            foreach ($obj as $k => $v) {
                $arr[$k] = $v;
            }
            return Arr::undot($arr);
        }, $roles);

        return [Str::camel((new CreateUser)->getTable()) => $roles];
    }

    public function getCreateOrder(Role $role): array
    {
        $roles = DB::table((new CreateOrder)->getTable())
            ->where('subject', '=', $role->getKey())
            ->get()
            ->toArray()
            //
        ;

        $roles = array_map(function ($obj) {
            $arr = [];
            foreach ($obj as $k => $v) {
                $arr[$k] = $v;
            }
            return Arr::undot($arr);
        }, $roles);

        return [Str::camel((new CreateOrder)->getTable()) => $roles];
    }

    public function getCreateVisit(Role $role): array
    {
        $roles = DB::table((new CreateVisit)->getTable())
            ->where('subject', '=', $role->getKey())
            ->get()
            ->toArray()
            //
        ;

        $roles = array_map(function ($obj) {
            $arr = [];
            foreach ($obj as $k => $v) {
                $arr[$k] = $v;
            }
            return Arr::undot($arr);
        }, $roles);

        return [Str::camel((new CreateVisit)->getTable()) => $roles];
    }

    public function getRetrieveUser(Role $role): array
    {
        $roles = DB::table((new RetrieveUser)->getTable())
            ->where('subject', '=', $role->getKey())
            ->get()
            ->toArray()
            //
        ;

        $roles = array_map(function ($obj) {
            $arr = [];
            foreach ($obj as $k => $v) {
                $arr[$k] = $v;
            }
            return Arr::undot($arr);
        }, $roles);

        return [Str::camel((new RetrieveUser)->getTable()) => $roles];
    }

    public function getRetrieveOrder(Role $role): array
    {
        $roles = DB::table((new RetrieveOrder)->getTable())
            ->where('subject', '=', $role->getKey())
            ->get()
            ->toArray()
            //
        ;

        $roles = array_map(function ($obj) {
            $arr = [];
            foreach ($obj as $k => $v) {
                $arr[$k] = $v;
            }
            return Arr::undot($arr);
        }, $roles);

        return [Str::camel((new RetrieveOrder)->getTable()) => $roles];
    }

    public function getRetrieveVisit(Role $role): array
    {
        $roles = DB::table((new RetrieveVisit)->getTable())
            ->where('subject', '=', $role->getKey())
            ->get()
            ->toArray()
            //
        ;

        $roles = array_map(function ($obj) {
            $arr = [];
            foreach ($obj as $k => $v) {
                $arr[$k] = $v;
            }
            return Arr::undot($arr);
        }, $roles);

        return [Str::camel((new RetrieveVisit)->getTable()) => $roles];
    }

    public function getUpdateUser(Role $role): array
    {
        $roles = DB::table((new UpdateUser)->getTable())
            ->where('subject', '=', $role->getKey())
            ->get()
            ->toArray()
            //
        ;

        $roles = array_map(function ($obj) {
            $arr = [];
            foreach ($obj as $k => $v) {
                $arr[$k] = $v;
            }
            return Arr::undot($arr);
        }, $roles);

        return [Str::camel((new UpdateUser)->getTable()) => $roles];
    }

    public function getDeleteUser(Role $role): array
    {
        $roles = DB::table((new DeleteUser)->getTable())
            ->where('subject', '=', $role->getKey())
            ->get()
            ->toArray()
            //
        ;

        $roles = array_map(function ($obj) {
            $arr = [];
            foreach ($obj as $k => $v) {
                $arr[$k] = $v;
            }
            return Arr::undot($arr);
        }, $roles);

        return [Str::camel((new DeleteUser)->getTable()) => $roles];
    }

    public function getDeleteOrder(Role $role): array
    {
        $roles = DB::table((new DeleteOrder)->getTable())
            ->where('subject', '=', $role->getKey())
            ->get()
            ->toArray()
            //
        ;

        $roles = array_map(function ($obj) {
            $arr = [];
            foreach ($obj as $k => $v) {
                $arr[$k] = $v;
            }
            return Arr::undot($arr);
        }, $roles);

        return [Str::camel((new DeleteOrder)->getTable()) => $roles];
    }

    public function getDeleteVisit(Role $role): array
    {
        $roles = DB::table((new DeleteVisit)->getTable())
            ->where('subject', '=', $role->getKey())
            ->get()
            ->toArray()
            //
        ;

        $roles = array_map(function ($obj) {
            $arr = [];
            foreach ($obj as $k => $v) {
                $arr[$k] = $v;
            }
            return Arr::undot($arr);
        }, $roles);

        return [Str::camel((new DeleteVisit)->getTable()) => $roles];
    }

    public function destroy(DestroyRequest $request)
    {
        //
    }
}
