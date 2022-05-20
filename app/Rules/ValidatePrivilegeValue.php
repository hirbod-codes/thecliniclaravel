<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use TheClinicDataStructures\DataStructures\User\DSUser;

class ValidatePrivilegeValue implements Rule
{
    private string|null $unknownPrivilege;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!is_array($value) || count($value) === 0) {
            return false;
        }

        $privileges = DSUser::getPrivileges();

        foreach ($value as $privilegeName => $privilegeValue) {
            if (!in_array($privilegeName, $privileges)) {
                $this->unknownPrivilege = $privilegeName;
                return false;
            }
            if (is_null($privilegeValue)) {
                $this->unknownPrivilege = null;
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if (!isset($this->unknownPrivilege)) {
            return 'The requested \'privilegeValue\' parameter is required.';
        } elseif ($this->unknownPrivilege !== null) {
            return 'The requested privilege: ' . $this->unknownPrivilege . ' is unknown.';
        } else {
            return 'The requested privilege value must not be null.';
        }
    }
}
