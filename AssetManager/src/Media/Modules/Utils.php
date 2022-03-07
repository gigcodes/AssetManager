<?php

namespace Gigcodes\AssetManager\Media\Modules;

use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

trait Utils
{
    /**
     * helper to paginate array.
     *
     * @param [type] $items
     * @param int $perPage
     * @param [type] $page
     */
    public function paginate($items, $perPage = 10, $page = null)
    {
        $pageName = 'page';
        $page = $page ?: (Paginator::resolveCurrentPage($pageName) ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ]
        );
    }

    /**
     * sanitize input.
     *
     * @return false|mixed [type] [description]
     */
    protected function getRandomString()
    {
        return call_user_func($this->sanitizedText);
    }

    protected function cleanName($text, $folder = false)
    {
        $pattern = $this->filePattern($folder ? $this->folderChars : $this->fileChars);
        $text = preg_replace($pattern, '', $text);

        return $text ?: $this->getRandomString();
    }

    protected function filePattern($item)
    {
        return '/(script.*?\/script)|[^(' . $item . ')a-zA-Z0-9]+/ius';
    }

    protected function getItemTime($time)
    {
        return $time ? Carbon::createFromTimestamp($time)->{$this->LMF}() : null;
    }

    /**
     * resolve url for "file/dir path" instead of laravel builtIn.
     * which needs to make extra call just to resolve the url.
     *
     * @param [type] $path [description]
     *
     * @return array|string|string[] [type] [description]
     */
    protected function resolveUrl($path)
    {
        return $this->clearDblSlash("{$this->baseUrl}/{$path}");
    }

    protected function clearDblSlash($str)
    {
        $str = preg_replace('/\/+/', '/', $str);
        return str_replace(':/', '://', $str);
    }
}