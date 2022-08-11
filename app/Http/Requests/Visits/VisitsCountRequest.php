<?php

namespace App\Http\Requests\Visits;

use App\Auth\CheckAuthentication;
use App\Http\Requests\BaseFormRequest;
use App\Models\Privileges\RetrieveVisit;
use App\Models\RoleName;
use App\Rules\ProhibitExtraFeilds;

class VisitsCountRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $input = $this->safe()->all();
        $user = (new CheckAuthentication)->getAuthenticated();
        $retrieveVisits = $user->authenticatableRole->role->role->retrieveVisitSubjects;

        $roleNameModel = RoleName::query()->where('name', '=', $input['roleName'])->firstOrFail();
        $targetUserRoleName = $roleNameModel->name;

        /** @var RetrieveVisit $retrieveVisit */
        foreach ($retrieveVisits as $retrieveVisit) {
            if ($retrieveVisit->relatedBusiness->name !== $input['businessName']) {
                continue;
            }

            if (($retrieveVisit->object === null) || ($retrieveVisit->object !== null && $retrieveVisit->relatedObject->childRoleModel->roleName->name !== $targetUserRoleName)) {
                continue;
            }

            return true;
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
            'roleName' => array_merge((include(base_path() . '/app/Rules/BuiltInRules/Models/role.php'))['roleName_optional'], ['required_with:operator,timestamp', 'prohibits:accountId,regularOrderId']),
            'businessName' => (include(base_path() . '/app/Rules/BuiltInRules/business.php'))['businessNames'],
        ];

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
