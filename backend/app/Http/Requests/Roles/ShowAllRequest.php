<?php

namespace App\Http\Requests\Roles;

use App\Auth\CheckAuthentication;
use App\Models\Privilege;
use App\Models\User;
use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;

class ShowAllRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $id = $this->safe()->all()['accountId'];
        /** @var User $targetUser */
        $targetUser = User::query()->whereKey($id)->firstOrFail();
        return ($user = (new CheckAuthentication)->getAuthenticated()) === null
            ? false
            : ($user->authenticatableRole->role->role->privilegesSubjects->search(function (Privilege $v, $k) use ($targetUser) {
                return $v->privilegeName->name === 'readRoles' && $targetUser->authenticatableRole->role->role->getKey() === $v->relatedObject->getKey();
            }, true) !== false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $array = [
            'accountId' => ['required', 'string', 'integer', 'numeric', 'min:0'],
        ];

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
