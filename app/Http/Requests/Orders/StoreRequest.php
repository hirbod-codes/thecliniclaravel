<?php

namespace App\Http\Requests\Orders;

use App\Auth\CheckAuthentication;
use App\Models\Package\Package;
use App\Models\Part\Part;
use App\Rules\Orders\PartsPackagesRequirement;
use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use TheClinicDataStructures\DataStructures\User\DSAdmin;

class StoreRequest extends FormRequest
{
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
        $dsUser = (new CheckAuthentication)->getAuthenticatedDSUser();

        $parts = array_map(function (Part $part) {
            return $part->name;
        }, Part::query()->get()->all());
        $packages = array_map(function (Package $package) {
            return $package->name;
        }, Package::query()->get()->all());

        $array = [
            'accountId' => ['integer', 'numeric', 'min:1'],
            'businessName' => ['required', 'string', Rule::in(['laser', 'regular'])],

            'packages' => ['prohibited_unless:businessName,laser', 'array'],
            'packages.*' => ['required_unless:packages,null', 'string', Rule::in($packages)],
            'parts' => ['prohibited_unless:businessName,laser', 'array', 'bail', new PartsPackagesRequirement()],
            'parts.*' => ['required_unless:parts,null', 'string', Rule::in($parts)],

            'price' => ['integer', 'numeric', 'min:1', 'prohibited_if:businessName,laser'],
            'timeConsumption' => ['integer', 'numeric', 'min:1', 'prohibited_if:businessName,laser'],
        ];

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
