<?php

namespace App\Models\rules\Traits;

use App\Http\Controllers\CheckAuthentication;
use TheClinicDataStructures\DataStructures\User\DSUser;

trait HasDataStructure
{
    public function getDataStructure(array $additionalArgs = []): DSUser
    {
        $DS = $this->DS;

        return new $DS(...array_merge(
            $this->toArrayWithoutRelations(),
            $additionalArgs,
            ['ICheckAuthentication' => new CheckAuthentication]
        ));
    }
}
