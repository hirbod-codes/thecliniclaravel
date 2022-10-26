<?php

namespace Tests;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class ConsoleTest extends TestCase
{
    public function runTests(): void
    {
        $this->test();
    }

    public function test(): void
    {
        $t = User::query()->firstOrFail()->phonenumber_verified_at;
        $this->assertEquals(1, 1);
    }
}
