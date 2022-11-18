<?php

namespace Database\Seeders;

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
use App\Models\UserColumn;
use Illuminate\Database\Seeder;

class DatabaseRoleSeeder extends Seeder
{
    public function run(): void
    {
        ($adminRoleName = new RoleName(['name' => 'admin']))->saveOrFail();
        $adminRoleName->fresh();
        ($adminRole = new Role)->saveOrFail();
        $adminRole->fresh();
        (new AdminRole([(new Role)->getForeignKey() => $adminRole->getKey(), (new RoleName)->getForeignKey() => $adminRoleName->getKey()]))->saveOrFail();

        ($doctorRoleName = new RoleName(['name' => 'doctor']))->saveOrFail();
        $doctorRoleName->fresh();
        ($doctorRole = new Role)->saveOrFail();
        $doctorRole->fresh();
        (new DoctorRole([(new Role)->getForeignKey() => $doctorRole->getKey(), (new RoleName)->getForeignKey() => $doctorRoleName->getKey()]))->saveOrFail();


        ($secretaryRoleName = new RoleName(['name' => 'secretary']))->saveOrFail();
        $secretaryRoleName->fresh();
        ($secretaryRole = new Role)->saveOrFail();
        $secretaryRole->fresh();
        (new SecretaryRole([(new Role)->getForeignKey() => $secretaryRole->getKey(), (new RoleName)->getForeignKey() => $secretaryRoleName->getKey()]))->saveOrFail();

        ($operatorRoleName = new RoleName(['name' => 'operator']))->saveOrFail();
        $operatorRoleName->fresh();
        ($operatorRole = new Role)->saveOrFail();
        $operatorRole->fresh();
        (new OperatorRole([(new Role)->getForeignKey() => $operatorRole->getKey(), (new RoleName)->getForeignKey() => $operatorRoleName->getKey()]))->saveOrFail();

        ($patientRoleName = new RoleName(['name' => 'patient']))->saveOrFail();
        $patientRoleName->fresh();
        ($patientRole = new Role)->saveOrFail();
        $patientRole->fresh();
        (new PatientRole([(new Role)->getForeignKey() => $patientRole->getKey(), (new RoleName)->getForeignKey() => $patientRoleName->getKey()]))->saveOrFail();

        $this->setAdminDefaultPrivileges();
        $this->setDoctorDefaultPrivileges();
        $this->setSecretaryDefaultPrivileges();
        $this->setOperatorDefaultPrivileges();
        $this->setPatientDefaultPrivileges();
    }

    public function setAdminDefaultPrivileges(): void
    {
        $adminRoleName = RoleName::query()->where('name', '=',  'admin')->firstOrFail();
        $adminRole = $adminRoleName->childRoleModel->role;

        foreach (Role::query()->get() as $role) {
            $roleName = $role->childRoleModel->roleName->name;
            if (in_array($roleName, ['admin'])) {
                continue;
            }

            (new CreateUser(['subject' => $adminRole->getKey(), 'object' => $role->getKey()]))->saveOrFail();

            /** @var Business $business */
            foreach (Business::query()->get() as $business) {
                (new CreateOrder(['subject' => $adminRole->getKey(), 'object' => $role->getKey(), $business->getForeignKey() => $business->getKey()]))->saveOrFail();
                (new CreateVisit(['subject' => $adminRole->getKey(), 'object' => $role->getKey(), $business->getForeignKey() => $business->getKey()]))->saveOrFail();
            }

            (new RetrieveUser(['subject' => $adminRole->getKey(), 'object' => $role->getKey()]))->saveOrFail();

            /** @var Business $business */
            foreach (Business::query()->get() as $business) {
                (new RetrieveOrder(['subject' => $adminRole->getKey(), 'object' => $role->getKey(), $business->getForeignKey() => $business->getKey()]))->saveOrFail();
                (new RetrieveVisit(['subject' => $adminRole->getKey(), 'object' => $role->getKey(), $business->getForeignKey() => $business->getKey()]))->saveOrFail();
            }

            /** @var UserColumn $userColumn */
            foreach (UserColumn::query()->get() as $userColumn) {
                (new UpdateUser(['subject' => $adminRole->getKey(), 'object' => $role->getKey(), $userColumn->getForeignKey() => $userColumn->getKey()]))->saveOrFail();
            }

            (new DeleteUser(['subject' => $adminRole->getKey(), 'object' => $role->getKey()]))->saveOrFail();

            /** @var Business $business */
            foreach (Business::query()->get() as $business) {
                (new DeleteOrder(['subject' => $adminRole->getKey(), 'object' => $role->getKey(), $business->getForeignKey() => $business->getKey()]))->saveOrFail();
                (new DeleteVisit(['subject' => $adminRole->getKey(), 'object' => $role->getKey(), $business->getForeignKey() => $business->getKey()]))->saveOrFail();
            }

            (new Privilege([(new Role)->getForeignKey() => $adminRole->getKey(), 'object' => $role->getKey(), (new PrivilegeName)->getForeignKey() => PrivilegeName::query()->where('name', '=', 'readRoles')->firstOrFail()->getKey(), 'boolean_value' => true]))->saveOrFail();
            (new Privilege([(new Role)->getForeignKey() => $adminRole->getKey(), 'object' => $role->getKey(), (new PrivilegeName)->getForeignKey() => PrivilegeName::query()->where('name', '=', 'writeRoles')->firstOrFail()->getKey(), 'boolean_value' => true]))->saveOrFail();
            (new Privilege([(new Role)->getForeignKey() => $adminRole->getKey(), 'object' => $role->getKey(), (new PrivilegeName)->getForeignKey() => PrivilegeName::query()->where('name', '=', 'editBusinessDefaults')->firstOrFail()->getKey(), 'boolean_value' => true]))->saveOrFail();
            (new Privilege([(new Role)->getForeignKey() => $adminRole->getKey(), 'object' => $role->getKey(), (new PrivilegeName)->getForeignKey() => PrivilegeName::query()->where('name', '=', 'editAvatar')->firstOrFail()->getKey(), 'boolean_value' => true]))->saveOrFail();
            (new Privilege([(new Role)->getForeignKey() => $adminRole->getKey(), 'object' => $role->getKey(), (new PrivilegeName)->getForeignKey() => PrivilegeName::query()->where('name', '=', 'editRegularOrderPrice')->firstOrFail()->getKey(), 'boolean_value' => true]))->saveOrFail();
            (new Privilege([(new Role)->getForeignKey() => $adminRole->getKey(), 'object' => $role->getKey(), (new PrivilegeName)->getForeignKey() => PrivilegeName::query()->where('name', '=', 'editRegularOrderNeededTime')->firstOrFail()->getKey(), 'boolean_value' => true]))->saveOrFail();
        }

        // self

        /** @var Business $business */
        foreach (Business::query()->get() as $business) {
            (new CreateOrder(['subject' => $adminRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
            (new CreateVisit(['subject' => $adminRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
        }

        (new RetrieveUser(['subject' => $adminRole->getKey(), 'object' => null]))->saveOrFail();

        /** @var Business $business */
        foreach (Business::query()->get() as $business) {
            (new RetrieveOrder(['subject' => $adminRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
            (new RetrieveVisit(['subject' => $adminRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
        }

        /** @var UserColumn $userColumn */
        foreach (UserColumn::query()->get() as $userColumn) {
            (new UpdateUser(['subject' => $adminRole->getKey(), 'object' => null, $userColumn->getForeignKey() => $userColumn->getKey()]))->saveOrFail();
        }

        (new DeleteUser(['subject' => $adminRole->getKey(), 'object' => null]))->saveOrFail();

        /** @var Business $business */
        foreach (Business::query()->get() as $business) {
            (new DeleteOrder(['subject' => $adminRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
            (new DeleteVisit(['subject' => $adminRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
        }

        (new Privilege([(new Role)->getForeignKey() => $adminRole->getKey(), 'object' => null, (new PrivilegeName)->getForeignKey() => PrivilegeName::query()->where('name', '=', 'readRoles')->firstOrFail()->getKey(), 'boolean_value' => true]))->saveOrFail();
        (new Privilege([(new Role)->getForeignKey() => $adminRole->getKey(), 'object' => null, (new PrivilegeName)->getForeignKey() => PrivilegeName::query()->where('name', '=', 'writeRoles')->firstOrFail()->getKey(), 'boolean_value' => true]))->saveOrFail();
        (new Privilege([(new Role)->getForeignKey() => $adminRole->getKey(), 'object' => null, (new PrivilegeName)->getForeignKey() => PrivilegeName::query()->where('name', '=', 'editBusinessDefaults')->firstOrFail()->getKey(), 'boolean_value' => true]))->saveOrFail();
        (new Privilege([(new Role)->getForeignKey() => $adminRole->getKey(), 'object' => null, (new PrivilegeName)->getForeignKey() => PrivilegeName::query()->where('name', '=', 'editAvatar')->firstOrFail()->getKey(), 'boolean_value' => true]))->saveOrFail();
        (new Privilege([(new Role)->getForeignKey() => $adminRole->getKey(), 'object' => null, (new PrivilegeName)->getForeignKey() => PrivilegeName::query()->where('name', '=', 'editRegularOrderPrice')->firstOrFail()->getKey(), 'boolean_value' => true]))->saveOrFail();
        (new Privilege([(new Role)->getForeignKey() => $adminRole->getKey(), 'object' => null, (new PrivilegeName)->getForeignKey() => PrivilegeName::query()->where('name', '=', 'editRegularOrderNeededTime')->firstOrFail()->getKey(), 'boolean_value' => true]))->saveOrFail();
    }

    public function setDoctorDefaultPrivileges(): void
    {
        $doctorRoleName = RoleName::query()->where('name', '=',  'doctor')->firstOrFail();
        $doctorRole = $doctorRoleName->childRoleModel->role;

        foreach (Role::query()->get() as $role) {
            $roleName = $role->childRoleModel->roleName->name;
            if (in_array($roleName, ['doctor', 'admin'])) {
                continue;
            }

            (new CreateUser(['subject' => $doctorRole->getKey(), 'object' => $role->getKey()]))->saveOrFail();

            /** @var Business $business */
            foreach (Business::query()->get() as $business) {
                (new CreateOrder(['subject' => $doctorRole->getKey(), 'object' => $role->getKey(), $business->getForeignKey() => $business->getKey()]))->saveOrFail();
                (new CreateVisit(['subject' => $doctorRole->getKey(), 'object' => $role->getKey(), $business->getForeignKey() => $business->getKey()]))->saveOrFail();
            }

            (new RetrieveUser(['subject' => $doctorRole->getKey(), 'object' => $role->getKey()]))->saveOrFail();

            /** @var Business $business */
            foreach (Business::query()->get() as $business) {
                (new RetrieveOrder(['subject' => $doctorRole->getKey(), 'object' => $role->getKey(), $business->getForeignKey() => $business->getKey()]))->saveOrFail();
                (new RetrieveVisit(['subject' => $doctorRole->getKey(), 'object' => $role->getKey(), $business->getForeignKey() => $business->getKey()]))->saveOrFail();
            }

            /** @var UserColumn $userColumn */
            foreach (UserColumn::query()->get() as $userColumn) {
                (new UpdateUser(['subject' => $doctorRole->getKey(), 'object' => $role->getKey(), $userColumn->getForeignKey() => $userColumn->getKey()]))->saveOrFail();
            }

            (new DeleteUser(['subject' => $doctorRole->getKey(), 'object' => $role->getKey()]))->saveOrFail();

            /** @var Business $business */
            foreach (Business::query()->get() as $business) {
                (new DeleteOrder(['subject' => $doctorRole->getKey(), 'object' => $role->getKey(), $business->getForeignKey() => $business->getKey()]))->saveOrFail();
                (new DeleteVisit(['subject' => $doctorRole->getKey(), 'object' => $role->getKey(), $business->getForeignKey() => $business->getKey()]))->saveOrFail();
            }

            (new Privilege([(new Role)->getForeignKey() => $doctorRole->getKey(), 'object' => $role->getKey(), (new PrivilegeName)->getForeignKey() => PrivilegeName::query()->where('name', '=', 'editRegularOrderPrice')->firstOrFail()->getKey(), 'boolean_value' => true]))->saveOrFail();
            (new Privilege([(new Role)->getForeignKey() => $doctorRole->getKey(), 'object' => $role->getKey(), (new PrivilegeName)->getForeignKey() => PrivilegeName::query()->where('name', '=', 'editRegularOrderNeededTime')->firstOrFail()->getKey(), 'boolean_value' => true]))->saveOrFail();
        }

        // self

        /** @var Business $business */
        foreach (Business::query()->get() as $business) {
            (new CreateOrder(['subject' => $doctorRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
            (new CreateVisit(['subject' => $doctorRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
        }

        (new RetrieveUser(['subject' => $doctorRole->getKey(), 'object' => null]))->saveOrFail();

        /** @var Business $business */
        foreach (Business::query()->get() as $business) {
            (new RetrieveOrder(['subject' => $doctorRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
            (new RetrieveVisit(['subject' => $doctorRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
        }

        /** @var UserColumn $userColumn */
        foreach (UserColumn::query()->get() as $userColumn) {
            (new UpdateUser(['subject' => $doctorRole->getKey(), 'object' => null, $userColumn->getForeignKey() => $userColumn->getKey()]))->saveOrFail();
        }

        (new DeleteUser(['subject' => $doctorRole->getKey(), 'object' => null]))->saveOrFail();

        /** @var Business $business */
        foreach (Business::query()->get() as $business) {
            (new DeleteOrder(['subject' => $doctorRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
            (new DeleteVisit(['subject' => $doctorRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
        }

        (new Privilege([(new Role)->getForeignKey() => $doctorRole->getKey(), 'object' => null, (new PrivilegeName)->getForeignKey() => PrivilegeName::query()->where('name', '=', 'editAvatar')->firstOrFail()->getKey(), 'boolean_value' => true]))->saveOrFail();
        (new Privilege([(new Role)->getForeignKey() => $doctorRole->getKey(), 'object' => null, (new PrivilegeName)->getForeignKey() => PrivilegeName::query()->where('name', '=', 'editRegularOrderPrice')->firstOrFail()->getKey(), 'boolean_value' => true]))->saveOrFail();
        (new Privilege([(new Role)->getForeignKey() => $doctorRole->getKey(), 'object' => null, (new PrivilegeName)->getForeignKey() => PrivilegeName::query()->where('name', '=', 'editRegularOrderNeededTime')->firstOrFail()->getKey(), 'boolean_value' => true]))->saveOrFail();
    }

    public function setSecretaryDefaultPrivileges(): void
    {
        $secretaryRoleName = RoleName::query()->where('name', '=',  'secretary')->firstOrFail();
        $secretaryRole = $secretaryRoleName->childRoleModel->role;

        foreach (Role::query()->get() as $role) {
            $roleName = $role->childRoleModel->roleName->name;
            if (!in_array($roleName, ['patient'])) {
                continue;
            }

            (new CreateUser(['subject' => $secretaryRole->getKey(), 'object' => $role->getKey()]))->saveOrFail();

            /** @var Business $business */
            foreach (Business::query()->get() as $business) {
                (new CreateOrder(['subject' => $secretaryRole->getKey(), 'object' => $role->getKey(), $business->getForeignKey() => $business->getKey()]))->saveOrFail();
                (new CreateVisit(['subject' => $secretaryRole->getKey(), 'object' => $role->getKey(), $business->getForeignKey() => $business->getKey()]))->saveOrFail();
            }

            (new RetrieveUser(['subject' => $secretaryRole->getKey(), 'object' => $role->getKey()]))->saveOrFail();

            /** @var Business $business */
            foreach (Business::query()->get() as $business) {
                (new RetrieveOrder(['subject' => $secretaryRole->getKey(), 'object' => $role->getKey(), $business->getForeignKey() => $business->getKey()]))->saveOrFail();
                (new RetrieveVisit(['subject' => $secretaryRole->getKey(), 'object' => $role->getKey(), $business->getForeignKey() => $business->getKey()]))->saveOrFail();
            }

            /** @var UserColumn $userColumn */
            foreach (UserColumn::query()->get() as $userColumn) {
                (new UpdateUser(['subject' => $secretaryRole->getKey(), 'object' => $role->getKey(), $userColumn->getForeignKey() => $userColumn->getKey()]))->saveOrFail();
            }
        }

        // self

        /** @var Business $business */
        foreach (Business::query()->get() as $business) {
            (new CreateOrder(['subject' => $secretaryRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
            (new CreateVisit(['subject' => $secretaryRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
        }

        (new RetrieveUser(['subject' => $secretaryRole->getKey(), 'object' => null]))->saveOrFail();

        /** @var Business $business */
        foreach (Business::query()->get() as $business) {
            (new RetrieveOrder(['subject' => $secretaryRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
            (new RetrieveVisit(['subject' => $secretaryRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
        }

        /** @var UserColumn $userColumn */
        foreach (UserColumn::query()->get() as $userColumn) {
            (new UpdateUser(['subject' => $secretaryRole->getKey(), 'object' => null, $userColumn->getForeignKey() => $userColumn->getKey()]))->saveOrFail();
        }

        (new DeleteUser(['subject' => $secretaryRole->getKey(), 'object' => null]))->saveOrFail();

        /** @var Business $business */
        foreach (Business::query()->get() as $business) {
            (new DeleteOrder(['subject' => $secretaryRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
            (new DeleteVisit(['subject' => $secretaryRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
        }

        (new Privilege([(new Role)->getForeignKey() => $secretaryRole->getKey(), 'object' => null, (new PrivilegeName)->getForeignKey() => PrivilegeName::query()->where('name', '=', 'editAvatar')->firstOrFail()->getKey(), 'boolean_value' => true]))->saveOrFail();
    }

    public function setOperatorDefaultPrivileges(): void
    {
        $operatorRoleName = RoleName::query()->where('name', '=',  'operator')->firstOrFail();
        $operatorRole = $operatorRoleName->childRoleModel->role;

        // self

        /** @var Business $business */
        foreach (Business::query()->get() as $business) {
            (new CreateOrder(['subject' => $operatorRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
            (new CreateVisit(['subject' => $operatorRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
        }

        (new RetrieveUser(['subject' => $operatorRole->getKey(), 'object' => null]))->saveOrFail();

        /** @var Business $business */
        foreach (Business::query()->get() as $business) {
            (new RetrieveOrder(['subject' => $operatorRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
            (new RetrieveVisit(['subject' => $operatorRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
        }

        /** @var UserColumn $userColumn */
        foreach (UserColumn::query()->get() as $userColumn) {
            (new UpdateUser(['subject' => $operatorRole->getKey(), 'object' => null, $userColumn->getForeignKey() => $userColumn->getKey()]))->saveOrFail();
        }

        (new DeleteUser(['subject' => $operatorRole->getKey(), 'object' => null]))->saveOrFail();

        /** @var Business $business */
        foreach (Business::query()->get() as $business) {
            (new DeleteOrder(['subject' => $operatorRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
            (new DeleteVisit(['subject' => $operatorRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
        }

        (new Privilege([(new Role)->getForeignKey() => $operatorRole->getKey(), 'object' => null, (new PrivilegeName)->getForeignKey() => PrivilegeName::query()->where('name', '=', 'editAvatar')->firstOrFail()->getKey(), 'boolean_value' => true]))->saveOrFail();
    }

    public function setPatientDefaultPrivileges(): void
    {
        $patientRoleName = RoleName::query()->where('name', '=',  'patient')->firstOrFail();
        $patientRole = $patientRoleName->childRoleModel->role;

        // self

        /** @var Business $business */
        foreach (Business::query()->get() as $business) {
            (new CreateOrder(['subject' => $patientRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
            (new CreateVisit(['subject' => $patientRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
        }

        (new RetrieveUser(['subject' => $patientRole->getKey(), 'object' => null]))->saveOrFail();

        /** @var Business $business */
        foreach (Business::query()->get() as $business) {
            (new RetrieveOrder(['subject' => $patientRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
            (new RetrieveVisit(['subject' => $patientRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
        }

        /** @var UserColumn $userColumn */
        foreach (UserColumn::query()->get() as $userColumn) {
            (new UpdateUser(['subject' => $patientRole->getKey(), 'object' => null, $userColumn->getForeignKey() => $userColumn->getKey()]))->saveOrFail();
        }

        (new DeleteUser(['subject' => $patientRole->getKey(), 'object' => null]))->saveOrFail();

        /** @var Business $business */
        foreach (Business::query()->get() as $business) {
            (new DeleteOrder(['subject' => $patientRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
            (new DeleteVisit(['subject' => $patientRole->getKey(), 'object' => null, $business->getForeignKey() => $business->getKey()]))->saveOrFail();
        }

        (new Privilege([(new Role)->getForeignKey() => $patientRole->getKey(), 'object' => null, (new PrivilegeName)->getForeignKey() => PrivilegeName::query()->where('name', '=', 'editAvatar')->firstOrFail()->getKey(), 'boolean_value' => true]))->saveOrFail();
    }
}
