<?php

namespace App\Http\Requests\Orders;

use App\Models\Package\Package;
use App\Models\Part\Part;
use App\Rules\Orders\PartsPackagesRequirement;
use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CalculatePartsAndPackagesRquest extends FormRequest
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
        $parts = array_map(function (Part $part) {
            return $part->name;
        }, Part::query()->get()->all());
        $packages = array_map(function (Package $package) {
            return $package->name;
        }, Package::query()->get()->all());

        $rules = [
            'packages' => ['array'],
            'packages.*' => ['string', Rule::in($packages)],
            'parts' => ['array', 'bail', new PartsPackagesRequirement()],
            'parts.*' => ['string', Rule::in($parts)],

            'gender' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/gender.php'))['gender_optional'],
        ];

        array_unshift($rules[array_key_first($rules)], new ProhibitExtraFeilds($rules));

        return $rules;
    }
}
