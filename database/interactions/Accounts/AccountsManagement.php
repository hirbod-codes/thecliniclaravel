<?php

namespace Database\Interactions\Accounts;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AccountsManagement
{
    /**
     * @param string|integer $placeholder can be user's id or username or email or phonenumber
     * @return string The username
     *
     * @throws ModelNotFoundException
     */
    public function resolveUsername(string|int $placeholder): string
    {
        if (is_int($placeholder)) {
            if (($user = User::query()
                ->where((new User)->getKeyName(), '=', $placeholder)
                ->first()) !== null) {
                return $user->username;
            } else {
                throw new ModelNotFoundException('', 404);
            }
        }

        if (($user = User::query()
            ->where('username', '=', $placeholder)
            ->first()) !== null) {
            return $user->username;
        }

        if (($user = User::query()
            ->where('email', '=', $placeholder)
            ->first()) !== null) {
            return $user->username;
        }

        if (($user = User::query()
            ->where('phonenumber', '=', $placeholder)
            ->first()) !== null) {
            return $user->username;
        }

        if (Str::contains($placeholder, '-')) {
            $firstname = explode('-', $placeholder)[0];
            $lastname = explode('-', $placeholder)[1];

            $firstnameRules = array_merge((include(base_path() . '/app/Rules/BuiltInRules/Models/User/firstname.php'))['firstname_optional'], ['required_with:lastname']);
            $lastnameRules = array_merge((include(base_path() . '/app/Rules/BuiltInRules/Models/User/lastname.php'))['lastname_optional'], ['required_with:firstname']);

            $validator = Validator::make(['firstname' => $firstname, 'lastname' => $lastname], [
                'firstname' => $firstnameRules,
                'lastname' => $lastnameRules,
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->toArray()], 422);
            }

            if (($user = User::query()
                ->where('firstname', '=', $firstname)
                ->where('lastname', '=', $lastname)
                ->first()) === null) {
                return $user->username;
            }
        }

        throw new ModelNotFoundException('', 404);
    }
}
