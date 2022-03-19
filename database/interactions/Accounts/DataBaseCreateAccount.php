<?php

namespace Database\Interactions\Accounts;

use App\Models\User;
use Database\Seeders\DatabaseUsersSeeder;
use Database\Traits\ResolveUserModel;
use Illuminate\Support\Facades\DB;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseCreateAccount;

class DataBaseCreateAccount implements IDataBaseCreateAccount
{
    use ResolveUserModel;

    private DatabaseUsersSeeder $databaseUsersSeeder;

    public function __construct(DatabaseUsersSeeder|null $databaseUsersSeeder = null)
    {
        $this->databaseUsersSeeder = $databaseUsersSeeder ?: new DatabaseUsersSeeder;
    }

    public function createAccount(array $input): DSUser
    {
        DB::beginTransaction();

        if (User::where('firstname', $input['firstname'])->where('lastname', $input['lastname'])->first() !== null) {
            throw new \RuntimeException('A user with same first name and last name already exists.', 500);
        }

        $ruleName = $input["rule"];
        unset($input["rule"]);

        $dsUser = $this->databaseUsersSeeder->{'create' . ucfirst($ruleName)}(1, $input)->getDataStructure();

        DB::commit();

        return $dsUser;
    }
}
