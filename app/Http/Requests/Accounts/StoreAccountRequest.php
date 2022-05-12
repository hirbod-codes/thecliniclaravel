<?php

namespace App\Http\Requests\Accounts;

use App\Rules\CheckEnryptedValuesIds;
use App\Rules\ProhibitExtraFeilds;
use Database\Traits\ResolveUserModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class StoreAccountRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        $roleName = array_reverse(explode('/', $this->path()))[0];

        $array = include(base_path() . '/app/Rules/BuiltInRules/Models/User/rules.php');

        $role = $this->findSimilarRole($roleName);

        if (!is_null($role)) {
            $array = array_merge($array, include(base_path() . '/app/Rules/BuiltInRules/Models/' . Str::studly($role) . '/updateRules.php'));
        } else {
            $array['data'][] = 'array';
            $array['data'][] = 'min:1';
        }

        $array['code_created_at_encrypted'] = ['required', 'string'];
        $array['code_encrypted'] = ['required', 'string'];
        $array['code'] = ['required', 'string', 'regex:/\A[0-9]{6}\z/'];
        $array['phonenumber_encrypted'] = ['required', 'string', new CheckEnryptedValuesIds];
        $array['phonenumber_verified_at_encrypted'] = ['required', 'string'];

        $array[array_key_first($array)][] = new ProhibitExtraFeilds($array);

        return $array;
    }
}
