<?php

namespace Database\Interactions\Business;

use App\Models\BusinessDefault;
use Database\Interactions\Business\Interfaces\IDataBaseUpdateBusinessSettings;

class DataBaseUpdateBusinessSettings implements IDataBaseUpdateBusinessSettings
{
    /**
     * @param array $attributes
     * @return void
     *
     * @throws \Throwable
     */
    public function update(array $attributes): void
    {
        BusinessDefault::query()->firstOrFail()->updateOrFail($attributes);
    }
}
