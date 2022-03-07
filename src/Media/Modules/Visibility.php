<?php

namespace Gigcodes\AssetManager\Media\Modules;

use Gigcodes\AssetManager\Events\MediaFileOpsNotifications;
use Illuminate\Http\Request;

trait Visibility
{
    /**
     * change file visibility.
     *
     * @param Request $request [description]
     *
     * @return \Illuminate\Http\JsonResponse [type] [description]
     */
    public function changeItemVisibility(Request $request)
    {
        $path = $request->path;
        $result = [];
        $toBroadCast = [];

        foreach ($request->list as $file) {
            $name = $file['name'];
            $type = $file['visibility'] == 'public' ? 'private' : 'public';
            $file_path = $file['storage_path'];

            if ($this->storageDisk->setVisibility($file_path, $type)) {
                $result[] = [
                    'success' => true,
                    'name' => $name,
                    'visibility' => $type,
                    'message' => trans('messages.visibility.success', ['attr' => $name]),
                ];

                $toBroadCast[] = [
                    'name' => $name,
                    'visibility' => $type,
                ];
            } else {
                $result[] = [
                    'success' => false,
                    'message' => trans('messages.visibility.error', ['attr' => $name]),
                ];
            }
        }

        // broadcast
        broadcast(new MediaFileOpsNotifications([
            'op' => 'visibility',
            'path' => $path,
            'items' => $toBroadCast,
        ]))->toOthers();

        return response()->json($result);
    }
}