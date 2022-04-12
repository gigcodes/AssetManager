<?php

namespace Gigcodes\AssetManager\Media\Modules;

use Illuminate\Http\Request;

trait GetContent
{
    /**
     * get files in path.
     *
     * @param Request $request [description]
     *
     * @return array|\Illuminate\Http\JsonResponse [type] [description]
     */
    public function getFiles(Request $request)
    {
        $path = $request->path == '/' ? '' : $request->path;

        if ($path && !$this->storageDisk->exists($path)) {
            return response()->json([
                'error' => trans('messages.error.doesnt_exist', ['attr' => $path]),
            ]);
        }

        return [
            'path' => $path,
            'items' => $this->getData($path),
        ];
    }

    /**
     * get files list.
     *
     * @param mixed $dir
     */
    protected function getData($dir)
    {
        $list = [];
        $dirList = $this->getFolderContent($dir);
        $storageFolders = $this->getFolderListByType($dirList, 'dir');
        $folders = [];
        // folders
        foreach ($storageFolders as $folder) {
            $path = $folder['path'];
            $folders[] = [
                'title' => $folder['basename'],
                'path' => $path,
            ];
        }

        return [
            'folders' => $folders,
        ];
    }

    /**
     * get directory data.
     *
     * @param mixed $folder
     * @param mixed $rec
     */
    protected function getFolderContent($folder, $rec = false)
    {
        $pattern = $this->ignoreFiles;

        return $this->storageDisk->createIterator(
            [
                'list-with' => ['mimetype', 'visibility', 'timestamp', 'size'],
                'recursive' => $rec,
                'filter' => function ($item) use ($pattern) {
                    return !preg_grep($pattern, [$item['basename']]);
                },
            ],
            $folder ?: '/'
        );
    }

    /**
     * filter directory data by type.
     *
     * @param [type] $list
     * @param [type] $type
     */
    protected function getFolderListByType($list, $type)
    {
        $list = collect($list)->where('type', $type);
        $sortBy = $list->pluck('basename')->values()->all();
        $items = $list->values()->all();

        array_multisort($sortBy, SORT_NATURAL, $items);

        return $items;
    }

    /**
     * get folder size.
     *
     * @param [type] $list
     */
    protected function getFolderInfoFromList($list)
    {
        $list = collect($list)->where('type', 'file');

        return [
            'count' => $list->count(),
            'size' => $list->pluck('size')->sum(),
        ];
    }
}