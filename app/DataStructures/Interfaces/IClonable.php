<?php

namespace App\DataStructures\Interfaces;

interface IClonable
{
    public function cloneIt(): self;
}
