<?php

namespace Database\Seeders;

use App\Models\Privilege;
use App\Models\PrivilegeValue;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use TheClinicDataStructures\DataStructures\User\DSAdmin;
use TheClinicDataStructures\DataStructures\User\DSDoctor;
use TheClinicDataStructures\DataStructures\User\DSOperator;
use TheClinicDataStructures\DataStructures\User\DSPatient;
use TheClinicDataStructures\DataStructures\User\DSSecretary;

class DatabasePrivilegeValueSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Role::all() as $role) {
            switch ($role->name) {
                case 'admin':
                    foreach (DSAdmin::getUserPrivileges() as $privilege => $privilegeValue) {
                        PrivilegeValue::factory()
                            ->for(Privilege::where('name', $privilege)->first(), 'privilege')
                            ->for($role, 'role')
                            ->privilegeValue((new PrivilegeValue)->convertPrivilegeValueToString($privilegeValue))
                            ->create();
                    }
                    break;

                case 'doctor':
                    foreach (DSDoctor::getUserPrivileges() as $privilege => $privilegeValue) {
                        PrivilegeValue::factory()
                            ->for(Privilege::where('name', $privilege)->first(), 'privilege')
                            ->for($role, 'role')
                            ->privilegeValue((new PrivilegeValue)->convertPrivilegeValueToString($privilegeValue))
                            ->create();
                    }
                    break;

                case 'secretary':
                    foreach (DSSecretary::getUserPrivileges() as $privilege => $privilegeValue) {
                        PrivilegeValue::factory()
                            ->for(Privilege::where('name', $privilege)->first(), 'privilege')
                            ->for($role, 'role')
                            ->privilegeValue((new PrivilegeValue)->convertPrivilegeValueToString($privilegeValue))
                            ->create();
                    }
                    break;

                case 'operator':
                    foreach (DSOperator::getUserPrivileges() as $privilege => $privilegeValue) {
                        PrivilegeValue::factory()
                            ->for(Privilege::where('name', $privilege)->first(), 'privilege')
                            ->for($role, 'role')
                            ->privilegeValue((new PrivilegeValue)->convertPrivilegeValueToString($privilegeValue))
                            ->create();
                    }
                    break;

                case 'patient':
                    foreach (DSPatient::getUserPrivileges() as $privilege => $privilegeValue) {
                        PrivilegeValue::factory()
                            ->for(Privilege::where('name', $privilege)->first(), 'privilege')
                            ->for($role, 'role')
                            ->privilegeValue((new PrivilegeValue)->convertPrivilegeValueToString($privilegeValue))
                            ->create();
                    }
                    break;

                default:
                    throw new \RuntimeException('This role does not exist!', 500);
                    break;
            }
        }
    }
}
