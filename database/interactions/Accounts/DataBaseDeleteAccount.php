<?php

namespace Database\Interactions\Accounts;

use App\Models\User;
use Database\Traits\ResolveUserModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseDeleteAccount;

class DataBaseDeleteAccount implements IDataBaseDeleteAccount
{
    use ResolveUserModel;

    public function deleteAccount(DSUser $user): void
    {
        try {
            DB::beginTransaction();

            $theModelClassFullName = $this->resolveRuleModelFullName($this->resolveRuleName($user));

            $theModel = $theModelClassFullName::where((new $theModelClassFullName)->getKeyName(), $user->getId())->first();

            if ($theModel === null) {
                throw new ModelNotFoundException('Failed to find the user.', 404);
            }

            /** @var User $theUserModel */
            $theUserModel = $theModel->user;

            $theModel->delete();
            $theUserModel->delete();

            DB::commit();

            Storage::disk('local')->delete('images/avatars' . strval($theUserModel->getKey()));
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
