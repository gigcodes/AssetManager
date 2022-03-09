<?php

use Gigcodes\AssetManager\Media\MediaController;
use Illuminate\Support\Facades\Route;


$route_condtions = [];
if (config('asset_manager.route.prefix')) {
    $route_condtions['prefix'] = config('asset_manager.route.prefix');
}
if (config('asset_manager.route.middleware')) {
    $route_condtions['middleware'] = config('asset_manager.route.middleware');
}

Route::group($route_condtions, function () {
    Route::get('media', [MediaController::class, 'index'])
        ->name('gigcodes.media.index')
        ->where('any', '.*');;
    Route::get('media/browse/{container?}/{any?}', [MediaController::class, 'browse'])
        ->name('gigcodes.media.browse')
        ->where('any', '.*');
    Route::post('media/folder', [MediaController::class, 'newFolder'])->name('gigcodes.media.newFolder');
    Route::get('media/folders/main/{folder}', [MediaController::class, 'getFolder'])->name('gigcodes.media.getFolder');
    Route::post('media/get-files', [MediaController::class, 'getContents'])->name('gigcodes.media.getFiles');
    Route::delete('media/folders', [MediaController::class, 'deleteFolder'])->name('gigcodes.media.deleteFolder');
    Route::post('media/upload', 'Gigcodes\AssetManager\Media\MediaController@uploadFile')->name('gigcodes.media.upload');
    Route::get('media/get-file', [MediaController::class, 'getFile'])->name('gigcodes.media.getFile');
    Route::delete('media/delete', [MediaController::class, 'deleteFile'])->name('gigcodes.media.deleteFile');
    Route::get('media/download/main/{file}', [MediaController::class, 'downloadFile'])->name('gigcodes.media.download');
});
