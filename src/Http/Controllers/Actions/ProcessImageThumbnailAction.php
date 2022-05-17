<?php

namespace Gigcodes\AssetManager\Http\Controllers\Actions;

use Illuminate\Support\Facades\Storage;

class ProcessImageThumbnailAction
{
    public function __construct(public GenerateImageThumbnailAction $generateImageThumbnail)
    {
    }

    private array $availableFormats = [
        'image/gif',
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
    ];

    public function __invoke($fileName, $file, $collectionUuid): void
    {
        if (in_array($file->getClientMimeType(), $this->availableFormats)) {
            // Make copy of file for the thumbnail generation
            Storage::disk('local')->copy("files/$collectionUuid/$fileName", "temp/$collectionUuid/$fileName");

            // Create thumbnails instantly
            ($this->generateImageThumbnail)(
                fileName: $fileName,
                collectionUuid: $collectionUuid,
                execution: 'immediately'
            );

            // Create thumbnails later
           ($this->generateImageThumbnail)->onQueue('high')->execute(
                fileName: $fileName,
                collectionUuid: $collectionUuid,
                execution: 'later'
            );

        }
    }
}