<?php

namespace Gigcodes\AssetManager\Http\Controllers\Traits;

use Illuminate\Http\Request;

trait Folder
{
    public function createFolder(Request $request)
    {
        if ($this->folder::where('collection_name', $request->get('container')['id'])
            ->where('parent_id', $request->get('parent_id'))
            ->where('name', $request->get('basename'))->first()) {
            return response([
                'success' => false,
                'message' => 'Please choose a different name'
            ], 422);
        }
        $folder = $this->folder::create([
            'media_collection_id' => $request->get('container')['item_id'],
            'collection_name' => $request->get('container')['id'],
            'parent_id' => $request->get('parent_id'),
            'name' => $request->get('basename'),
        ]);

        $folder->load(['deepParent']);

        return response([
            'path' => $this->generateReverseParent($folder) . '/' . $folder->name,
            'uuid' => $folder->uuid
        ]);
    }

    public function updateFolder(Request $request, $uuid)
    {
        if ($this->folder::where('collection_name', $request->get('container')['id'])
            ->where('parent_id', $request->get('parent_id'))
            ->where('name', $request->get('basename'))->first()) {
            return response([
                'success' => false,
                'message' => 'Please choose a different name'
            ], 422);
        } else {
            $this->folder::where('uuid', $request->get('path')['uuid'])
                ->update(['name' => $request->get('basename')]);

            $folder = $this->folder::where('uuid', $request->get('path')['uuid'])->first();

            $folder->load(['deepParent']);

            return response([
                'path' => $this->generateReverseParent($folder) . '/' . $folder->name,
                'uuid' => $folder->uuid
            ]);
        }
    }

    public function deleteFolder($uuid)
    {
        $folder = $this->folder::where('uuid', $uuid)->with('childrenFolders')->first();
        $this->deleteFolderChildren($folder);
        $folder->delete();
        return response(['message' => "Folder deleted successfully"]);
    }

    protected function deleteFolderChildren($folder): void
    {
        if ($folder instanceof \Illuminate\Database\Eloquent\Collection) {
            foreach ($folder as $item) {
                $this->deleteFolderChildren($item);
            }
        } else {
            if ($folder->childrenFolders && count($folder->childrenFolders)) {
                $this->deleteFolderChildren($folder->childrenFolders);
            } elseif ($folder->children && count($folder->children)) {
                $this->deleteFolderChildren($folder->children);
            } else {
                $this->files::where('media_folder_id', $folder->id)->delete();
                $folder->delete();
            }
        }

    }
}