<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountDocuments\GetAvatarRequest;
use App\Http\Requests\AccountDocuments\SetAvatarRequest;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AccountDocumentsController extends Controller
{
    public function getAvatar(GetAvatarRequest $request)
    {
        $validatedInput = $request->safe()->all();
        $validatedInput['accountId'] = intval($validatedInput['accountId']);

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
