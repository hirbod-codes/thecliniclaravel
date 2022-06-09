<?php

namespace App\Http\Controllers;

use App\Auth\CheckAuthentication;
use App\Http\Requests\AccountDocuments\GetAvatarRequest;
use App\Http\Requests\AccountDocuments\SetAvatarRequest;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AccountDocumentsController extends Controller
{
    private CheckAuthentication $checkAuthentication;

    public function __construct(CheckAuthentication|null $checkAuthentication = null)
    {
        $this->checkAuthentication = $checkAuthentication ?: new CheckAuthentication;
    }

    public function getAvatar(GetAvatarRequest $request)
    {
        $validatedInput = $request->safe()->all();
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();
        $validatedInput['accountId'] = intval($validatedInput['accountId']);

        if ($validatedInput['accountId'] !== $dsAuthenticated->getId()) {
            $privileges = $dsAuthenticated->getUserPrivileges();

            if ($privileges['accountsRead'] === false) {
                if ($request->header('content-type', false) === 'application/json') {
                    return response()->json(['message' => trans_choice('auth.User-Not-Authorized', 0)], 403);
                } else {
                    return response(trans_choice('auth.User-Not-Authorized', 0), 403);
                }
            }
        }

        $extension = 'jpg';
        $fileName = strval($validatedInput['accountId']) . '.' . $extension;

        $filePathRelative = 'images' . DIRECTORY_SEPARATOR . 'avatars' . DIRECTORY_SEPARATOR . $fileName;
        $filePathAbsolute = storage_path('app' . DIRECTORY_SEPARATOR . ($filePathRelative));

        if (Storage::disk('local')->exists($filePathRelative)) {
            return response(base64_encode(file_get_contents($filePathAbsolute)));
        }

        if ($request->header('content-type', false) === 'application/json') {
            return response()->json(['message' => 'The user\'s avatar file doesn\'t exisit.'], 404);
        } else {
            return response('The user\'s avatar file doesn\'t exisit.', 404);
        }
    }

    public function setAvatar(SetAvatarRequest $request)
    {
        $validatedInput = $request->safe()->all();
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        if ($validatedInput['accountId'] !== $dsAuthenticated->getId()) {
            $privileges = $dsAuthenticated->getUserPrivileges();
            if ($privileges['accountUpdate'] === false) {
                return response()->json(['message' => 'You are not authorized for this action.'], 403);
            }
        }

        $this->makeAvatar($validatedInput['avatar'], $validatedInput['accountId'] . '.jpg', 'private');

        return response(trans_choice('auth.set-avatar-successfully', 0));
    }

    public function makeAvatar(File|UploadedFile|string $file, string $name, string|array $options = []): void
    {
        if (Storage::putFileAs('images/avatars/', $file, $name, $options) === false) {
            throw new \RuntimeException('Failed to create the avatar!!!', 500);
        }
    }
}
