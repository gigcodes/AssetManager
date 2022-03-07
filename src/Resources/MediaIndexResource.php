<?php

namespace Gigcodes\AssetManager\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class MediaIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    protected $storageUrl;
    protected $publicUrl;
    protected $file;

    public function __construct($resource)
    {
        parent::__construct($resource);
        $path = $this->full_path;
        $this->storageUrl = Storage::disk($this->disk)->path($path);
        $this->publicUrl = Storage::disk($this->disk)->url($path);
    }

    public function toArray($request)
    {
        $array = [
            'item_id' => $this->id,
            'id' => 'main::' . $this->file_name,
            'title' => null,
            'alt_text' => null,
            'url' => $this->full_path,
            'permalink' => $this->publicUrl,
            'preview' => $this->publicUrl,
            'path' => $this->full_path,
            'filename' => $this->filename(),
            'basename' => $this->basename(),
            'extension' => $this->extension(),
            'is_asset' => true,
            'is_audio' => $this->isAudio(),
            'is_previewable' => $this->isPreviewable(),
            'is_image' => $this->isImage(),
            'is_video' => $this->isVideo(),
            'edit_url' => route('admin.media.getFile', $this->full_path),
            'container' => 'main',
            'folder' => $this->folder(),
            'thumbnail' => $this->getManipulation('thumb')->getPublicUrl(),
            'toenail' => $this->getManipulation('toenail')->getPublicUrl(),
            'download_url' => route('admin.media.download', [
                'file' => $this->id
            ]),
        ];


        $size = $this->size;
        $kb = number_format($size / 1024, 2);
        $mb = number_format($size / 1048576, 2);
        $gb = number_format($size / 1073741824, 2);
        $properties = $this->getProperties();
        $array = array_merge($array, [
            'width' => $properties['width'],
            'height' => $properties['height'],
            'size' => $formatted = $this->bytesToHuman($size),
            'size_formatted' => $formatted,
            'size_bytes' => $size,
            'size_kilobytes' => $kb,
            'size_megabytes' => $mb,
            'size_gigabytes' => $gb,
            'size_b' => $size,
            'size_kb' => $kb,
            'size_mb' => $mb,
            'size_gb' => $gb,
            'last_modified' => (string)$this->lastModified(),
            'last_modified_timestamp' => $this->lastModified()->timestamp,
            'last_modified_instance' => $this->lastModified(),
            'last_modified_formatted' => $this->lastModified()->format('d/m/Y'),
            'last_modified_relative' => $this->lastModified()->diffForHumans(),
            'focus' => '',
            'focus_css' => '',
        ]);


        return $array;
    }

    public function bytesToHuman($bytes)
    {
        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the filename of the asset
     *
     * Eg. For a path of foo/bar/baz.jpg, the filename will be "baz"
     *
     * @return string
     */
    public function filename()
    {
        return pathinfo($this->storageUrl)['filename'];
    }

    public function getProperties()
    {
        try {
            $image = Image::make($this->storageUrl);
            return [
                'height' => $image->getHeight(),
                'width' => $image->getWidth(),
            ];
        } catch (\Exception $exception) {
            return [
                'height' => null,
                'width' => null,
            ];
        }
    }

    public function width()
    {

    }

    /**
     * Get the basename of the asset
     *
     * Eg. for a path of foo/bar/baz.jpg, the basename will be "baz.jpg"
     *
     * @return string
     */
    public function basename()
    {
        return pathinfo($this->storageUrl)['basename'];
    }

    /**
     * Get the folder (or directory) of the asset
     *
     * Eg. for a path of foo/bar/baz.jpg, the folder will be "foo/bar"
     *
     * @return mixed
     */
    public function folder()
    {
        return pathinfo($this->storageUrl)['dirname'];
    }

    public function extension()
    {
        return pathinfo($this->storageUrl)['extension'];
    }

    public function url()
    {
        return $this->uri();
    }

    public function extensionIsOneOf($filetypes = [])
    {
        return (in_array(strtolower($this->extension()), $filetypes));
    }


    public function uri()
    {
        return true;
    }

    public function absoluteUrl()
    {
        return true;
    }

    public function isAudio()
    {
        return $this->extensionIsOneOf(['aac', 'flac', 'm4a', 'mp3', 'ogg', 'wav']);
    }

    public function isPreviewable()
    {
        return $this->extensionIsOneOf([
            'doc', 'docx', 'pages', 'txt',
            'ai', 'psd', 'eps', 'ps',
            'css', 'html', 'php', 'c', 'cpp', 'h', 'hpp', 'js',
            'ppt', 'pptx',
            'flv',
            'tiff',
            'ttf',
            'dxf', 'xps',
            'zip', 'rar',
            'xls', 'xlsx'
        ]);
    }

    /**
     * Is this asset an image?
     *
     * @return bool
     */
    public function isImage()
    {
        return $this->extensionIsOneOf(['jpg', 'jpeg', 'png', 'gif']);
    }

    /**
     * Is this asset a video file?
     *
     * @return bool
     */
    public function isVideo()
    {
        return $this->extensionIsOneOf(['h264', 'mp4', 'm4v', 'ogv', 'webm']);
    }

    /**
     * Get the file extension of the asset
     *
     * @return string
     */

    /**
     * Get the last modified time of the asset
     *
     * @return \Carbon\Carbon
     */
    public function lastModified()
    {
        return Carbon::parse($this->updated_at);
    }
}