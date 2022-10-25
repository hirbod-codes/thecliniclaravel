<?php

namespace App\Http\Requests\Orders;

use App\Auth\CheckAuthentication;
use App\Models\Package\Package;
use App\Models\Part\Part;
use App\Rules\Orders\PartsPackagesRequirement;
use App\Rules\ProhibitExtraFeilds;
use Illuminate\Validation\Rule;
use App\Http\Requests\BaseFormRequest;
use App\Models\User;

class StoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = (new CheckAuthentication)->getAuthenticated();
        $input = $this->safe()->all();
        /** @var User $targetUser */
        $targetUser = User::query()->whereKey(intval($input['accountId']))->firstOrFail();
        $isSelf = $targetUser->getKey() === $user->getKey();

        foreach ($user->authenticatableRole->role->role->createOrderSubjects as $createOrder) {
            if ($createOrder->relatedBusiness->name !== $input['businessName']) {
                continue;
            }

            if (($isSelf && $createOrder->object !== null) || (!$isSelf && ($createOrder->object === null || ($createOrder->object !== null && $createOrder->relatedObject->getKey() !== $targetUser->authenticatableRole->role->role->getKey())))) {
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
        $parts = array_map(function (Part $part) {
            return $part->name;
        }, Part::query()->get()->all());
        $packages = array_map(function (Package $package) {
            return $package->name;
        }, Package::query()->get()->all());

        $array = [
            'accountId' => ['integer', 'numeric', 'min:1', 'exists:' . (new User)->getTable() . ',' . (new User)->getKeyName()],
            'businessName' => (include(base_path() . '/app/Rules/BuiltInRules/business.php'))['businessNames'],

            'packages' => ['prohibited_unless:businessName,laser', 'array'],
            'packages.*' => ['required_unless:packages,null', 'string', Rule::in($packages)],
            'parts' => ['prohibited_unless:businessName,laser', 'array', 'bail', new PartsPackagesRequirement()],
            'parts.*' => ['required_unless:parts,null', 'string', Rule::in($parts)],

            'price' => ['integer', 'numeric', 'min:1', 'prohibited_if:businessName,laser'],
            'timeConsumption' => ['integer', 'numeric', 'min:5', 'prohibited_if:businessName,laser'],
        ];

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
