<?php

namespace Gigcodes\AssetManager\Http\Controllers\Traits;

use Gigcodes\AssetManager\Http\Resources\MediaItemsIndexResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait File
{
    public function deleteFile(Request $request)
    {
        $ids = $request->get('ids');
        foreach ($ids as $id) {
            $file_name = explode("::", $id);
            $this->files::where('collection_name', $file_name[0])
                ->where('basename', $file_name[1])
                ->delete();
        }

        return response([
            'success' => true,
            'message' => 'Media deleted successfully'
        ]);
    }

    public function getFile(Request $request): JsonResponse
    {
        $assets = [];
        $items = $request->get('items');
        foreach ($items as $item) {
            if (str_contains($item, '::')) {
                $f = explode("::", $item);
                $media = new MediaItemsIndexResource($this->files::where('basename', $f[1])->where('collection_name', $f[0])->first());
            } else {
                $media = new MediaItemsIndexResource($this->files::where('basename', $item)->first());
            }
            $assets[] = $media;
        }
        return response()->json(['assets' => $assets]);
    }

    public function downloadFile($uuid): BinaryFileResponse
    {
        $file = $this->files::where('uuid', $uuid)->first();
        $file = Storage::disk($file->disk)->path($file->full_path);
        return Response::download($file);
    }
}