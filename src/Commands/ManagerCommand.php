<?php

namespace Gigcodes\AssetManager\Commands;

use Illuminate\Console\Command;

class ManagerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'am:setup {--migrations : Install AssetManager Scaffolding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup the application for Gigcodes Vue Asset Manager';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $migrations = $this->option('migrations');
        if($migrations){

        }else{
            $this->info('Please check the available options');
        }

    }
}
