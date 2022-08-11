<?php

namespace App\DataStructures\Interfaces;

use Illuminate\Contracts\Support\Arrayable as SupportArrayable;

interface Arrayable extends SupportArrayable
{
    function toArray(): array;
}
