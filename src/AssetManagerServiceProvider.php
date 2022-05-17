<?php

namespace Gigcodes\AssetManager;

use Illuminate\Support\ServiceProvider;

class AssetManagerServiceProvider extends ServiceProvider
{
    /**
     * Register the package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/asset_manager.php', 'asset_manager');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/asset_manager.php' => config_path('asset_manager.php'),
        ], 'config');

        if (!class_exists('CreateMediaTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_media_collections_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_media_collections_table.php'),
                __DIR__ . '/../database/migrations/create_exif_metas_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_exif_metas_table.php'),
                __DIR__ . '/../database/migrations/create_media_files_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_media_files_table.php'),
                __DIR__ . '/../database/migrations/create_media_folders_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_media_folders_table.php'),
            ], 'migrations');
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }
}