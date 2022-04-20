<?php

namespace App\Http\Controllers;

use App\Auth\CheckAuthentication;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AccountDocumentsController extends Controller
{
    private CheckAuthentication $checkAuthentication;

    public function __construce(CheckAuthentication|null $checkAuthentication = null)
    {
        $this->checkAuthentication = $checkAuthentication ?: new CheckAuthentication;
    }

    public function getAvatar(int $accountsId): string
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        if ($accountsId !== $dsAuthenticated->getId()) {
            $privileges = (new RolesController)->show(true);
            if ($privileges['accountsRead'] === false) {
                throw new \RuntimeException('You are not authorized for this action.', 403);
            }
        }

        if (Storage::disk('local')->exists('images/avaters/' . strval($accountsId))) {
            return Storage::disk('local')->get('images/avaters/' . strval($accountsId));
        }

        throw new \RuntimeException('The user\'s avatar file doesn\'t exisit.', 404);
    }

    public function setAvatar(Request $request)
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        if ($request->accountsId !== $dsAuthenticated->getId()) {
            $privileges = (new RolesController)->show(true);
            if ($privileges['accountsUpdate'] === false) {
                throw new \RuntimeException('You are not authorized for this action.', 403);
            }
        }

        $this->makeAvatar($request->file('avatar'), $request->accountId, 'local');
    }

    public function makeAvatar(File|UploadedFile|string $file, string $name, string|array $options = []): void
    {
        if (Storage::putFileAs('images/avatars/', $file, $name, $options) === false) {
            throw new \RuntimeException('Failed to create the avatar!!!', 500);
        }
    }
}
