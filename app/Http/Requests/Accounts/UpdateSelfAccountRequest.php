<?php

namespace App\Http\Requests\Accounts;

use App\Auth\CheckAuthentication;
use App\Models\Role;
use App\Rules\ProhibitExtraFeilds;
use Database\Traits\ResolveUserModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateSelfAccountRequest extends FormRequest
{
    use ResolveUserModel;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $dsUser = (new CheckAuthentication)->getAuthenticatedDSUser();

        $array = include(base_path() . '/app/Rules/BuiltInRules/Models/User/updateRules.php');

        $role = $this->resolveRuleName($dsUser);
        $role = $this->resolveRuleType($role);
        if ($role === 'custom') {
            $role = null;
        } elseif (Str::contains($role, 'custom')) {
            $role = Str::replace('custom_', '', $role);
        }

        if (!is_null($role)) {
            $array = array_merge($array, include(base_path() . '/app/Rules/BuiltInRules/Models/' . Str::studly($role) . '/updateRules.php'));
        } else {
            $array['data'][] = 'array';
            $array['data'][] = 'min:1';
        }

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
