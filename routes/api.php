<?php

use Gigcodes\AssetManager\Http\Controllers\MediaController;
use Gigcodes\AssetManager\Http\Controllers\UploadFileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$options = [
    'prefix' => config('asset-manager.route.prefix'),
    'as' => config('asset-manager.route.name') . '.'
];

if (config('asset-manager.route.middleware')) {
    $options['middleware'] = config('asset-manager.route.middleware');
}

Route::group($options, function () {
    Route::post('media/browse', [MediaController::class, 'makeCollection'])->name('container.browse');
    Route::delete('media/browse', [MediaController::class, 'deleteCollection'])->name('container.delete');
    Route::get('media/browse/{collection?}/{folder?}', [MediaController::class, 'getCollection'])->name('container');
    Route::patch('media/browse/{collection?}/{folder?}', [MediaController::class, 'editContainer'])->name('container.edit');
    Route::get('media/get-files', [MediaController::class, 'getItems'])->name('media.getItems');

    //FileActions
    Route::post('media/upload', UploadFileController::class)->name('media.upload');
    Route::patch('media/{uuid}/edit', [MediaController::class, 'editFile'])->name('media.edit');
    Route::get('media/{uuid}/download', [MediaController::class, 'downloadFile'])->name('media.download');
    Route::delete('media/delete', [MediaController::class, 'deleteFile'])->name('media.delete');
    Route::get('media/get-file', [MediaController::class, 'getFile'])->name('media.item');

    //Folder Actions
    Route::post('media/folder', [MediaController::class, 'createFolder'])->name('media.folder.create');
    Route::delete('media/folder/{uuid}', [MediaController::class, 'deleteFolder'])->name('media.folder.delete');
    Route::patch('media/folder/{uuid}/edit', [MediaController::class, 'updateFolder'])->name('media.folder.update');
});
