<?php

namespace Database\Interactions\Accounts\Interfaces;

use App\Models\User;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

interface IDataBaseCreateAccount
{
    public function createAccount(string $userType, string $roleName, array $input, array $specialInput, null|string|File|UploadedFile $avatar = null): User;
}
