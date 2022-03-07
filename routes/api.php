<?php

use Gigcodes\AssetManager\Media\MediaController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => config('asset_manager.route.prefix'), 'middleware' => config('asset_manager.route.middleware')], function () {
    Route::get('media', [MediaController::class, 'index'])
        ->name('media.index')
        ->where('any', '.*');;
    Route::get('media/browse/{container?}/{any?}', [MediaController::class, 'browse'])
        ->name('media.browse')
        ->where('any', '.*');
    Route::post('media/folder', [MediaController::class, 'newFolder'])->name('media.newFolder');
    Route::get('media/folders/main/{folder}', [MediaController::class, 'getFolder'])->name('media.getFolder');
    Route::post('media/get-files', [MediaController::class, 'getContents'])->name('media.getFiles');
    Route::delete('media/folders', [MediaController::class, 'deleteFolder'])->name('media.deleteFolder');
    Route::post('media/upload', [MediaController::class, 'uploadFile'])->name('media.upload');
    Route::get('media/{container?}/{file?}', [MediaController::class, 'getFile'])->name('media.getFile');
    Route::delete('media/delete', [MediaController::class, 'deleteFile'])->name('media.deleteFile');
    Route::get('media/download/main/{file}', [MediaController::class, 'downloadFile'])->name('media.download');
});
