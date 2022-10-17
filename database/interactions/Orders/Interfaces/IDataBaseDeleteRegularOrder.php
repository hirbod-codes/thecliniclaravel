<?php

namespace Database\Interactions\Orders\Interfaces;

use App\Models\Order\RegularOrder;
use App\Models\User;

interface IDataBaseDeleteRegularOrder
{
    public function deleteRegularOrder(RegularOrder $regularOrder): void;
}
