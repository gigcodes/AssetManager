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
        $this->mergeConfigFrom(__DIR__ . '/../config/asset-manager.php', 'asset-manager');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/asset-manager.php' => config_path('asset-manager.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../database/migrations/create_media_collections_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time() + 30) . '_create_media_collections_table.php'),
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/../database/migrations/create_media_folders_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time() + 60) . '_create_media_folders_table.php'),
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/../database/migrations/create_media_files_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time() + 90) . '_create_media_files_table.php'),
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/../database/migrations/create_exif_metas_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time() + 120) . '_create_exif_metas_table.php'),
        ], 'migrations');

        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }
}