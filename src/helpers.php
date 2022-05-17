<?php


use ByteUnits\Metric;
use Illuminate\Support\Collection;
use Intervention\Image\ImageManagerStatic as Image;

if (!function_exists('format_bytes')) {
    /**
     * Convert megabytes to bytes
     *
     * @param $megabytes
     * @return int|string
     */
    function format_bytes($megabytes): int|string
    {
        return Metric::megabytes($megabytes)->numberOfBytes();
    }
}


if (! function_exists('isStorageDriver')) {
    /**
     * Check if is running AWS s3 as storage
     *
     * @param $driver
     * @return bool
     */
    function isStorageDriver($driver): bool
    {
        if (is_array($driver)) {
            return in_array(config('filesystems.default'), $driver);
        }

        return config('filesystems.default') === $driver;
    }
}

if (! function_exists('get_file_type')) {
    /**
     * Get file type from mimetype
     */
    function get_file_type(string $fileMimetype): string
    {
        // Get mimetype from file
        $mimetype = explode('/', $fileMimetype);

        // Check image
        if ($mimetype[0] === 'image' && in_array(strtolower($mimetype[1]), ['jpg', 'jpeg', 'bmp', 'png', 'gif', 'svg', 'svg+xml'])) {
            return 'image';
        }

        // Check video or audio
        if (in_array($mimetype[0], ['video', 'audio'])) {
            return $mimetype[0];
        }

        return 'file';
    }
}

if (! function_exists('get_file_type_from_mimetype')) {
    /**
     * Get file type from mimetype
     *
     * @param $mimetype
     * @return mixed
     */
    function get_file_type_from_mimetype($mimetype): mixed
    {
        return explode('/', $mimetype)[1];
    }
}


if (! function_exists('get_image_meta_data')) {
    /**
     * Get exif data from jpeg image
     *
     * @param $file
     * @return array|null
     */
    function get_image_meta_data($file): ?array
    {
        if (get_file_type_from_mimetype($file->getMimeType()) === 'jpeg') {
            try {
                // Try to get the exif data
                return mb_convert_encoding(Image::make($file->getRealPath())->exif(), 'UTF8', 'UTF8');
            } catch (\Exception $e) {
                return null;
            }
        }
    }
}

if (! function_exists('format_gigabytes')) {
    /**
     * Format integer to gigabytes
     *
     * @param $gigabytes
     * @return string
     */
    function format_gigabytes($gigabytes)
    {
        if ($gigabytes >= 1000) {
            return Metric::gigabytes($gigabytes)->format('Tb/');
        }

        return Metric::gigabytes($gigabytes)->format('GB/');
    }
}

if (! function_exists('format_megabytes')) {
    /**
     * Format string to formated megabytes string
     *
     * @param $megabytes
     * @return string
     */
    function format_megabytes($megabytes)
    {
        if ($megabytes >= 1000) {
            return $megabytes / 1000 . 'GB';
        }

        if ($megabytes >= 1000000) {
            return $megabytes / 1000000 . 'TB';
        }

        return $megabytes . 'MB';
    }
}

if (! function_exists('format_bytes')) {
    /**
     * Convert megabytes to bytes
     *
     * @param $megabytes
     * @return int|string
     */
    function format_bytes($megabytes)
    {
        return Metric::megabytes($megabytes)->numberOfBytes();
    }
}

if (! function_exists('getThumbnailFileList')) {
    /**
     * Get list of image thumbnails
     */
    function getThumbnailFileList(string $basename): Collection
    {
        return collect([
            config('asset-manager.image_sizes.later'),
            config('asset-manager.image_sizes.immediately'),
        ])->collapse()
            ->map(fn ($item) => $item['name'] . '-' . $basename);
    }
}

if (! function_exists('get_file_type_from_mimetype')) {
    /**
     * Get file type from mimetype
     *
     * @param $mimetype
     * @return mixed
     */
    function get_file_type_from_mimetype($mimetype)
    {
        return explode('/', $mimetype)[1];
    }
}

if (! function_exists('getPrettyName')) {
    /**
     * Format pretty name file
     *
     * @param $basename
     * @param $name
     * @param $mimetype
     * @return string
     */
    function getPrettyName($basename, $name, $mimetype): string
    {
        $file_extension = substr(strrchr($basename, '.'), 1);

        if (str_contains($name, $file_extension)) {
            return $name;
        }

        if ($file_extension) {
            return $name . '.' . $file_extension;
        }

        return $name . '.' . $mimetype;
    }
}