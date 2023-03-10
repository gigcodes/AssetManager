<?php

namespace Gigcodes\AssetManager\Http\Controllers\Actions;

use Aws\Exception\MultipartUploadException;
use Aws\S3\MultipartUploader;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MoveFileToExternalStorageAction
{
    public function __invoke(string $file,string $collectionUuid): void
    {
        $disk_local = Storage::disk('local');
        $filesize = $disk_local->size("files/$collectionUuid/$file");

        // If file is bigger than 5.2 MB then run multipart upload
        if ($filesize > 5242880) {
            // Get client
            $client = Storage::disk('s3')->getClient();

            // Prepare the upload parameters.
            // TODO: replace local files with temp folder
            $uploader = new MultipartUploader($client, config('filesystems.disks.local.root') . "/files/$collectionUuid/$file", [
                'bucket' => config('filesystems.disks.s3.bucket'),
                'key'    => "files/$collectionUuid/$file",
            ]);

            try {
                // Upload content
                $uploader->upload();
            } catch (MultipartUploadException $e) {
                // Write error log
                Log::error($e->getMessage());

                // Delete file after error
                $disk_local->delete("files/$collectionUuid/$file");

                throw new HttpException(409, $e->getMessage());
            }
        } else {
            // Stream file object to s3
            // TODO: replace local files with temp folder
            Storage::putFileAs("files/$collectionUuid", config('filesystems.disks.local.root') . "/files/$collectionUuid/$file", $file, 'private');
        }

        // Delete file after upload
        $disk_local->delete("files/$collectionUuid/$file");

    }
}