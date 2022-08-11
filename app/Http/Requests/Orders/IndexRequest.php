<?php

namespace App\Http\Requests\Orders;

use App\Auth\CheckAuthentication;
use App\Http\Requests\BaseFormRequest;
use App\Models\User;
use App\Rules\ProhibitExtraFeilds;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;

class IndexRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = (new CheckAuthentication)->getAuthenticated();
        $userRoleName = $user->authenticatableRole->role->roleName->name;
        $input = $this->safe()->all();

        if (isset($input['username'])) {
            $targetUser = User::query()->where('username', '=', $input['username'])->firstOrFail();
            $targetUserRoleName = $targetUser->authenticatableRole->role->roleName->name;
            $isSelf = $user->getKey() === $targetUser->getKey();

            foreach ($targetUser->authenticatablerole->role->role->retrieveOrderSubjects as $retrieveOrder) {
                if ($retrieveOrder->relatedBusiness->name !== ($path = explode('/', Request::path()))[1]) {
                    continue;
                }

                if (($isSelf && $retrieveOrder->object !== null) || (!$isSelf && ($retrieveOrder->object === null || ($retrieveOrder->object !== null && $retrieveOrder->relatedObject->childRoleModel->roleName->name !== $targetUserRoleName)))) {
                    continue;
                }

                return true;
            }
        } else {
            foreach ($user->authenticatablerole->role->role->retrieveOrderSubjects as $retrieveOrder) {
                if ($retrieveOrder->relatedBusiness->name !== ($path = explode('/', Request::path()))[1]) {
                    continue;
                }

                if (($retrieveOrder->object === null && $input['roleName'] !== $userRoleName) || ($retrieveOrder->object !== null && $retrieveOrder->relatedObject->childRoleModel->roleName->name !== $input['roleName'])) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $array = [
            'roleName' =>           array_merge((include(base_path() . '/app/Rules/BuiltInRules/Models/role.php'))['roleName_optional'], ['required_without:username']),
            'username' =>           ['required_without:count', 'prohibits:roleName,count,lastOrderId', 'string'],
            'count' =>              ['required_without:username', 'string', 'prohibits:username'],
            'lastOrderId' =>        ['string', 'numeric', 'integer'],
            'priceOtherwiseTime' => ['boolean'],
            'operator' =>           ['string', 'prohibited_if:priceOtherwiseTime,null', 'required_with:priceOtherwiseTime', Rule::in(['<', '>', '=', '<>', '>=', '<='])],
            'price' =>              ['numeric', 'integer', 'prohibited_if:priceOtherwiseTime,null', 'prohibits:timeConsumption', 'required_if:priceOtherwiseTime,true'],
            'timeConsumption' =>    ['numeric', 'integer', 'prohibited_if:priceOtherwiseTime,null', 'prohibits:price', 'required_if:priceOtherwiseTime,false'],
        ];

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
