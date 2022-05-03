<?php

namespace App\Http\Controllers;

use App\Auth\CheckAuthentication;
use App\Http\Requests\AccountDocuments\GetAvatarRequest;
use App\Http\Requests\AccountDocuments\SetAvatarRequest;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use TheClinicDataStructures\DataStructures\User\DSUser;

class AccountDocumentsController extends Controller
{
    private CheckAuthentication $checkAuthentication;

    public function __construce(CheckAuthentication|null $checkAuthentication = null)
    {
        $this->checkAuthentication = $checkAuthentication ?: new CheckAuthentication;
    }

    public function getAvatar(GetAvatarRequest $request): string
    {
        $validatedInput = $request->safe()->all();
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        if ($validatedInput['accountsId'] !== $dsAuthenticated->getId()) {
            $privileges = DSUser::getPrivileges();
            if ($privileges['accountsRead'] === false) {
                return response(trans_choice('auth.User-Not-Authorized', 0), 403);
            }
        }

        if (Storage::disk('local')->exists('images/avaters/' . strval($validatedInput['accountsId']))) {
            return Storage::disk('local')->get('images/avaters/' . strval($validatedInput['accountsId']));
        }

        throw new \RuntimeException('The user\'s avatar file doesn\'t exisit.', 404);
    }

    public function setAvatar(SetAvatarRequest $request)
    {
        $validatedInput = $request->safe()->all();
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        if ($validatedInput['accountsId'] !== $dsAuthenticated->getId()) {
            $privileges = DSUser::getPrivileges();
            if ($privileges['accountsUpdate'] === false) {
                throw new \RuntimeException('You are not authorized for this action.', 403);
            }
        }

        $this->makeAvatar($validatedInput['avatar'], $validatedInput['accountId'], 'private');

        return response(trans_choice('auth.set-avatar-successfully', 0));
    }

    public function makeAvatar(File|UploadedFile|string $file, string $name, string|array $options = []): void
    {
        if (Storage::putFileAs('images/avatars/', $file, $name, $options) === false) {
            throw new \RuntimeException('Failed to create the avatar!!!', 500);
        }
    }
}
