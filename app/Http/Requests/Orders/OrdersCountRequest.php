<?php

namespace App\Http\Requests\Orders;

use App\Auth\CheckAuthentication;
use App\Http\Requests\BaseFormRequest;
use App\Models\User;
use App\Rules\ProhibitExtraFeilds;

class OrdersCountRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        /** @var User $user */
        $user = (new CheckAuthentication)->getAuthenticated();
        $input = $this->safe()->all();

        $retrieveOrderModels = $user->authenticatableRole->role->role->retrieveOrderSubjects;
        foreach ($retrieveOrderModels as $retrieveOrderModel) {
            if ($retrieveOrderModel->relatedBusiness->name !== $input['businessName']) {
                continue;
            }

            if (($retrieveOrderModel->object === null && $input['roleName'] === $user->authenticatableRole->role->roleName->name) || ($retrieveOrderModel->object !== null && $retrieveOrderModel->relatedObject->childRoleModel->roleName->name === $input['roleName'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $array = [
            'roleName' => (include(base_path() . '/app/Rules/BuiltInRules/Models/role.php'))['roleName'],
            'businessName' => (include(base_path() . '/app/Rules/BuiltInRules/business.php'))['businessNames'],
        ];
        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));
        return $array;
    }
}
