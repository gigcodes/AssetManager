<?php

return [
    'route' => [
        'name' => 'media',
        'prefix' => '',
    ],
    'max_upload_limit' => 5, //int: mention in megabytes
    'collection_class' => \Gigcodes\AssetManager\Models\MediaCollection::class,
    'folder_class' => \Gigcodes\AssetManager\Models\MediaFolder::class,
    'file_class' => \Gigcodes\AssetManager\Models\MediaFile::class,
    'storage_disk' => env('FILESYSTEM_DISK', 'local'),
    'mimes' => [
        'blacklisted' => "" //string: mimetypes seperated by comma (,)
    ],
    'image_sizes' => [
        'immediately' => [
            [
                'size' => 120,
                'name' => 'xs',
            ],
            [
                'size' => 240,
                'name' => 'sm',
            ],
        ],

        'later' => [
            [
                'size' => 960,
                'name' => 'lg',
            ],
            [
                'size' => 1440,
                'name' => 'xl',
            ],
        ],
    ],
];