<?php

namespace Gigcodes\AssetManager\Http\Controllers;

use Gigcodes\AssetManager\Http\Controllers\Traits\Collection;
use Gigcodes\AssetManager\Http\Controllers\Traits\File;
use Gigcodes\AssetManager\Http\Controllers\Traits\Folder;
use Gigcodes\AssetManager\Http\Resources\MediaItemsResource;
use Gigcodes\AssetManager\Models\MediaFolder;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    use Collection, Folder, File;

    protected $collection;
    protected $files;
    protected $folder;

    public function __construct()
    {
        $this->collection = config('asset-manager.collection_class');
        $this->files = config('asset-manager.file_class');
        $this->folder = config('asset-manager.folder_class');
    }

    public function getItems(Request $request): MediaItemsResource
    {
        $container = $request->get('container');
        $dir = $request->get('dir');
        $path_uuid = $request->get('path_uuid');
        $path = $request->get('path');
        $sort = $request->get('sort');

        $root_path = true;


        if ($path_uuid) {
            $folders_db = $this->folder::where('parent_id', $path_uuid)
                ->where('collection_name', $container)
                ->select(["name as title", "parent_id", "uuid", "name as path"])
                ->get(['title', 'parent_id', 'uuid', 'path']);

            $current_folder = $this->folder::where('uuid', $path_uuid)
                ->where('collection_name', $container)
                ->with(['parent', 'deepParent'])->first();
            $root_path = false;

        } else {
            $folders_db = $this->folder::where('collection_name', $container)
                ->whereNull("parent_id")
                ->select(["name as title", "parent_id", "uuid", "name as path"])
                ->get(['name', 'parent_id', 'uuid', 'path']);
            $current_folder = null;
        }


        $sortType = match ($sort) {
            "size" => "filesize",
            "lastModified" => "updated_at",
            default => "name",
        };

        if ($current_folder) {
            $files_db = $this->files::where('media_folder_id', $current_folder->id)
                ->where('collection_name', $container)
                ->orderBy($sortType, $dir)
                ->paginate();
        } else {
            $files_db = $this->files::where('collection_name', $container)
                ->where('media_folder_id', null)
                ->with('exif')
                ->orderBy($sortType, $dir)
                ->paginate();
        }


        $containers = $this->collection::get(['name', 'title', 'uuid']);

        if ($path && $current_folder) {
            $path_generated = $this->generateReverseParent($current_folder);
            if ($path_generated == "/") {
                $path_generated = "/" . $current_folder->name;
            } else {
                $path_generated = $path_generated . "/" . $current_folder->name;
            }
        } else {
            $path_generated = "/";
        }

        $parent_folder = $path && $current_folder ? $current_folder->parent : null;
        $parent_path = $parent_folder ? $parent_folder->name : "/";
        $parent_path_uuid = $parent_folder ? $current_folder->parent->uuid : null;

        return new MediaItemsResource($files_db, [
            'containers' => $containers,
            'folder' => [
                'parent_path' => $root_path ? null : $parent_path,
                'path' => $path_generated,
                'title' => $path && $current_folder ? $current_folder->name : null,
                'uuid' => $path && $current_folder ? $current_folder->uuid : null,
                'parent' => [
                    'path' => $root_path ? null : $parent_path,
                    'title' => $parent_path,
                    'uuid' => $parent_path_uuid,
                ]
            ],
            'folders' => $current_folder && $current_folder->children ? $current_folder->children()->select(["name as title", "parent_id", "uuid", "name as path"])
                ->get(['title', 'parent_id', 'uuid', 'path']) : $folders_db,
        ]);
    }

    public function generatePath(MediaFolder $folder, $path = "/")
    {
        if ($folder->deepParent) {
            return $this->generatePath($folder->deepParent, $path . $folder->deepParent->name);
        } else if ($folder->parent) {
            echo $folder->parent->name;
            return $this->generatePath($folder->parent, $path . $folder->parent->name);
        }
        return $path;
    }

    public function generateReverseParent(MediaFolder $folder, $path_array = []): string
    {
        if ($folder->deepParent) {
            $path_array[] = $folder->deepParent->name;
            return $this->generateReverseParent($folder->deepParent, $path_array);
        } else if ($folder->parent) {
            $path_array[] = $folder->parent->name;
            return $this->generateReverseParent($folder->parent, $path_array);
        }
        return '/' . implode('/', array_reverse($path_array));
    }
}
