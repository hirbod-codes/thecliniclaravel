<?php

namespace App\UseCases\Orders\Deletion;

use App\Models\Auth\User;
use App\Models\Order\RegularOrder;
use App\UseCases\Orders\Interfaces\IDataBaseDeleteRegularOrder;

class RegularOrderDeletion
{
    public function deleteRegularOrder(RegularOrder $regularOrder, User $targetUser, IDataBaseDeleteRegularOrder $db): void
    {
        $db->deleteRegularOrder($regularOrder, $targetUser);
    }
}
