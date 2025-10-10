<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class SetupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $dbName = Config::get('database.connections.mysql.database');
        $tempConnection = Config::get('database.connections.mysql');
        $tempConnection['database'] = null;

        config(['database.connections.temp' => $tempConnection]);

        $query = "CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

        DB::connection('temp')->statement($query);

        $this->info("Database '{$dbName}' created or already exists.");

        $this->call('migrate');
    }
}
