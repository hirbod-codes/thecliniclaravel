<?php

namespace Database\Interactions\Orders\Interfaces;

use App\Models\Order\RegularOrder;

interface IDataBaseDeleteRegularOrder
{
    public function deleteRegularOrder(RegularOrder $regularOrder): void;
}
