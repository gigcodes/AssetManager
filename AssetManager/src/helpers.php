<?php
use Carbon\Carbon;
use Illuminate\Support\Arr;

function extension($path)
{
    return Arr::get(pathinfo($path), 'extension');
}

function extensionIsOneOf($path, $filetypes = [])
{
    return in_array(strtolower(extension($path)), $filetypes);
}

function isPreviewable($path)
{
    return extensionIsOneOf($path, [
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

function isAudio($path)
{
    return extensionIsOneOf($path, ['aac', 'flac', 'm4a', 'mp3', 'ogg', 'wav']);
}

function isImage($path)
{
    return extensionIsOneOf($path, ['jpg', 'jpeg', 'png', 'gif']);
}

function isVideo($path)
{
    return extensionIsOneOf($path, ['h264', 'mp4', 'm4v', 'ogv', 'webm']);
}

function lastModified($time)
{
    return Carbon::createFromTimestamp($time);
}

function fileSizeForHumans($bytes, $decimals = 2)
{
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, $decimals) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, $decimals) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, $decimals) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' B';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' B';
    } else {
        $bytes = '0 B';
    }
    return $bytes;
}

function estimated_reading_time($content) {

    $words = str_word_count(strip_tags($content));
    $minutes = floor( $words / 120 );

    if ( 1 <= $minutes ) {
        $estimated_time = $minutes+1 . ' minute' . ($minutes == 1 ? '' : 's');
    } else {
        $estimated_time = 1 . ' minute';
    }

    return $estimated_time;

}
