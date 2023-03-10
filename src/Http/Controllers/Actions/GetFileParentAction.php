<?php

namespace Gigcodes\AssetManager\Http\Controllers\Actions;

use Gigcodes\AssetManager\Http\Requests\UploadFileRequest;
use Gigcodes\AssetManager\Models\MediaCollection;
use Gigcodes\AssetManager\Models\MediaFolder;
use Illuminate\Support\Collection;

class GetFileParentAction
{
    protected $folderClass;

    public function __construct()
    {
        $this->folderClass = config('asset-manager.folder_class');
    }

    public function __invoke(UploadFileRequest $request, MediaCollection $collection): ?string
    {
        $string =  $request->input('path');
        $path_array = explode('/', $string);

        $paths = explode('/', $string);
        if ($paths[0] === "") {
            $string = ltrim($string, '/');
            $path_array = explode('/', $string);
        }

        // extract file path
        $directoryPath = collect($path_array);

        // If there isn't directory path, return parent_id
        if ($directoryPath->isEmpty()) {
            return $request->input('parent_id');
        }

        return $this->getOrCreateParentFolders($directoryPath, $collection, $request->input('parent_id'));
    }

    private function getOrCreateParentFolders(Collection $directoryPath, MediaCollection $collection, ?string $parentId): ?string
    {
        // Break the end of recursive
        if ($directoryPath->isEmpty()) {
            return $parentId;
        }

        // Get requested directory name
        $directoryName = $directoryPath->shift();

        // Get requested directory
        $requestedDirectory = $this->folderClass::where('name', $directoryName);

        // Check if root exists, if not, create him
        if ($requestedDirectory->exists()) {
            // Get parent folder
            $parentCheck = $this->folderClass::where('name', $directoryName)
                ->where('parent_id', $parentId);

            // Check if parent folder of requested directory name exists, if not, create it
            if ($parentCheck->exists()) {
                $folder = $parentCheck->first();
            } else {
                $folder = $this->createFolder($directoryName, $collection, $parentId);
            }
        }

        // Create directory if not exists
        if ($requestedDirectory->doesntExist()) {
            $folder = $this->createFolder($directoryName, $collection, $parentId);
        }

        // Repeat yourself
        return $this->getOrCreateParentFolders($directoryPath, $collection, $folder->uuid);
    }


    private function createFolder($directoryName, $collection, $parentUuid): MediaFolder
    {
        /*
         * Check if exist parent team folder, if yes,
         * then get the latest parent folder to detect whether it is team_folder
        */

        return $this->folderClass::create([
            'name' => $directoryName,
            'parent_id' => $parentUuid,
            'media_collection_id' => $collection->id,
            'collection_name' => $collection->name,
        ]);
    }

}