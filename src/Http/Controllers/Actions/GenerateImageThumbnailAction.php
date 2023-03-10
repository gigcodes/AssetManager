<?php

namespace Gigcodes\AssetManager\Http\Controllers\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Spatie\QueueableAction\QueueableAction;

class GenerateImageThumbnailAction
{
    use QueueableAction;

    public function __invoke($fileName, $collectionUuid, $execution): Collection
    {
        // Get image width
        $imageWidth = getimagesize(
            Storage::disk('local')->path("temp/$collectionUuid/$fileName")
        )[0];

        return collect(config("asset-manager.image_sizes.$execution"))
            ->each(function ($size) use ($collectionUuid, $fileName, $imageWidth) {
                if ($imageWidth > $size['size']) {
                    // Create intervention image
                    $intervention = Image::make(
                        Storage::disk('local')->path("temp/$collectionUuid/$fileName")
                    )
                        ->orientate();

                    // Generate thumbnail
                    $intervention
                        ->resize($size['size'], null, fn ($constraint) => $constraint->aspectRatio())
                        ->stream();

                    // Store thumbnail to disk
                    Storage::put("files/$collectionUuid/{$size['name']}-$fileName", $intervention);
                }
            });
    }
}