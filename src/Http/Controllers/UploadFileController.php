<?php

namespace Gigcodes\AssetManager\Http\Controllers;

use Gigcodes\AssetManager\Exceptions\InvalidUserActionException;
use Gigcodes\AssetManager\Http\Controllers\Actions\FileUploadAction;
use Gigcodes\AssetManager\Http\Requests\UploadFileRequest;
use Gigcodes\AssetManager\Http\Resources\FileResource;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;

class UploadFileController extends Controller
{
    public function __construct(public FileUploadAction $fileUploadAction)
    {

    }

    public function __invoke(UploadFileRequest $uploadFileRequest): Response|Application|ResponseFactory
    {
        try {
            // Upload and store file record
            $file = ($this->fileUploadAction)($uploadFileRequest);
            return response([
                'item' => new FileResource($file),
                'success' => true,
                'message' => 'File Uploaded successfully'
            ], 201);
        } catch (InvalidUserActionException|FileNotFoundException $e) {
            return response([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
        }
    }
}
