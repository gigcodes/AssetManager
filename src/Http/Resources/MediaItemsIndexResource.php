<?php

namespace Gigcodes\AssetManager\Http\Resources;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use function ByteUnits\bytes;

class MediaItemsIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $array = [
            'item_id' => $this->id,
            'uuid' => $this->uuid,
            'id' => "$this->collection_name::$this->basename",
            'title' => $this->name,
            'alt_text' => $this->custom_properties['alt_text'] ?? null,
            'url' => $this->full_path,
            'permalink' => asset($this->publicUrl),
            'path' => $this->upload_path,
            'filename' => $this->basename,
            'basename' => $this->basename,
            'extension' => explode('.', $this->basename)[1],
            'is_asset' => true,
            'is_audio' => $this->isAudio(),
            'is_previewable' => $this->isPreviewable(),
            'is_image' => $this->isImage(),
            'is_video' => $this->isVideo(),
            'edit_url' => route(config('asset-manager.route.name') . ".media.edit", ['uuid' => $this->uuid]),
            'container' => $this->collection_name,
            'folder' => $this->upload_path,
            'thumbnail' => $this->getManipulation('xs')->getPublicUrl(),
            'toenail' => $this->getManipulation('xl')->getPublicUrl(),
            'download_url' => route(config('asset-manager.route.name') . ".media.download", ['uuid' => $this->uuid]),
        ];

        return array_merge($array, [
            'width' => $this->exif ? $this->exif->width : null,
            'height' => $this->exif ? $this->exif->height : null,
            'size' => $size = bytes($this->filesize)->format(),
            'size_formatted' => bytes($this->filesize)->format(),
            'size_bytes' => $this->filesize,
            'size_kilobytes' => $kb = bytes($this->filesize)->format('KB'),
            'size_megabytes' => $mb = bytes($this->filesize)->format('MB'),
            'size_gigabytes' => $gb = bytes($this->filesize)->format('GB'),
            'size_b' => $size,
            'size_kb' => $kb,
            'size_mb' => $mb,
            'size_gb' => $gb,
            'last_modified' => (string)$this->lastModified(),
            'last_modified_timestamp' => $this->lastModified()->timestamp,
            'last_modified_instance' => $this->lastModified(),
            'last_modified_formatted' => $this->lastModified()->format('d/m/Y'),
            'last_modified_relative' => $this->lastModified()->diffForHumans(),
            'focus' => '50-50',
            'focus_css' => '50% 50%',
        ]);
    }

    public function extension(): string
    {
        return explode('.', $this->basename)[1];
    }


    public function extensionIsOneOf($filetypes = []): bool
    {
        return (in_array(strtolower($this->extension()), $filetypes));
    }

    public function isAudio(): bool
    {
        return $this->extensionIsOneOf(['aac', 'flac', 'm4a', 'mp3', 'ogg', 'wav']);
    }

    public function isPreviewable(): bool
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
    public function isImage(): bool
    {
        return $this->extensionIsOneOf(['jpg', 'jpeg', 'png', 'gif']);
    }

    /**
     * Is this asset a video file?
     *
     * @return bool
     */
    public function isVideo(): bool
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
    public function lastModified(): Carbon
    {
        return Carbon::parse($this->updated_at);
    }
}
