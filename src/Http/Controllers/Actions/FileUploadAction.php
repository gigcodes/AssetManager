<?php

namespace Gigcodes\AssetManager\Http\Controllers\Actions;

use Gigcodes\AssetManager\Exceptions\InvalidUserActionException;
use Gigcodes\AssetManager\Http\Requests\UploadFileRequest;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadAction
{
    protected $folder;
    protected $file;
    protected $collection;

    public function __construct(
        public StoreExifMetaDataAction         $storeExifMetaDataAction,
        public GetFileParentAction             $getFileParentAction,
        public MoveFileToExternalStorageAction $moveFileToExternalStorageAction,
        public ProcessImageThumbnailAction     $processImageThumbnailAction,
    )
    {
        $this->folder = config('asset-manager.folder_class');
        $this->file = config('asset-manager.file_class');
        $this->collection = config('asset-manager.collection_class');
    }

    /**
     * @throws FileNotFoundException
     * @throws InvalidUserActionException
     */
    public function __invoke(UploadFileRequest $request)
    {
        $file = $request->file('file');
        $chunkName = $file->getClientOriginalName();

        // File Path
        $fileBasePath = Storage::disk('local')->path('chunks');

        $filePath = $fileBasePath . "/" . $chunkName;

        if (!File::isDirectory($fileBasePath)) {
            File::makeDirectory($fileBasePath);
        }

        // Generate file
        File::append($filePath, $file->get());

        // Size of file
        $fileSize = File::size($filePath);


        // Size of limit
        $uploadLimit = config('asset-manager.max_upload_limit');

        // File size handling
        if ($uploadLimit && $fileSize > format_bytes($uploadLimit)) {
            abort(413);
        }

        $disk_local = Storage::disk('local');

        // File name
        $fileName = Str::uuid() . '.' . File::extension($filePath);

        //Collection
        $collection = $this->collection::where('name', $request->get('collection_name'))->first();


        // File Info
        $fileSize = $disk_local->size("chunks/$chunkName");
        $fileMimetype = $disk_local->mimeType("chunks/$chunkName");


        // Move finished file from chunk to file-manager directory
        $disk_local->move("chunks/$chunkName", "files/$collection->uuid/$fileName");

        // Create multiple image thumbnails
        ($this->processImageThumbnailAction)($fileName, $file, $collection->uuid);

        // Move files to external storage
        if (!isStorageDriver('local')) {
            ($this->moveFileToExternalStorageAction)($fileName, $collection->uuid);
        }

        $manipulations = [];
        foreach (config('asset-manager.image_sizes') as $item) {
            foreach ($item as $sizes) {
                $manipulations[$sizes['name']] = $sizes['name'] . "-$fileName";
            }
        }

        if ($request->get('path') === '/' || $request->get('path') === null) {
            $folder = null;
        } else {
            $folder = $this->folder::where('uuid', ($this->getFileParentAction)($request, $collection))->first();
        }

        // Create new file
        $item = $this->file::create([
            'media_collection_id' => $collection->id,
            'collection_name' => $collection->name,
            'media_folder_id' => $folder ? $folder->id : null,
            'name' => $request->input('filename'),
            'basename' => $fileName,
            'mimetype' => $fileMimetype,
            'filesize' => $fileSize,
            'type' => get_file_type($fileMimetype),
            'full_path' => "files/$collection->uuid/$fileName",
            'upload_path' => "files/$collection->uuid",
            'disk' => config('asset-manager.storage_disk'),
            'manipulations' => $manipulations,
            'order_column' => null
        ]);

        // Store exif metadata for files
        ($this->storeExifMetaDataAction)($item, $file);

        // Return new file
        return $item;
    }
}