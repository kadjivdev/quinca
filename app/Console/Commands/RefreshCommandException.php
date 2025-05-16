<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshCommandException extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    // protected $signature = 'migrate:refresh-except {--tables= : The tables to exclude}';
    protected $signature = 'migrate:refresh-except';

    private $exceptsTables = [
        "users",
        "password_reset_tokens",
        "failed_jobs",
        "personal_access_tokens",
        "permissions",
        "permission",
        "roles",
        "model_has_permissions",
        "model_has_roles",
        "role_has_permissions",
        "unite_mesures",
        "point_de_ventes",
        "societes",
        "caisses",
        "famille_articles",
        "type_depots",
        "depots",
        "type_tarifs",
        "fournisseurs",
        "point_de_ventes",
        "vehicules",
        "chauffeurs",
        "clients",
        "departements",
        "agents",
    ];

    /** 
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh all migrations except specified tables';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tablesToExclude = $this->exceptsTables; // explode(',', $this->option('tables'));

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Get the migrator instance
        $migrator = app('migrator');

        // Get migration paths
        // $paths = $this->laravel->databasePath() . DIRECTORY_SEPARATOR . 'migrations';
        $paths =  'database' . DIRECTORY_SEPARATOR . 'migrations';

        // Get all migrations
        $migrations = $migrator->getMigrationFiles($paths);

        // Filter out migrations for excluded tables
        $migrations = array_filter($migrations, function ($migration) use ($tablesToExclude) {

            foreach ($tablesToExclude as $table) {
                if (str_contains($migration, $table)) {
                    return false;
                }
            }
            return true;
        });

        // Run the refresh on filtered migrations
        $this->call('migrate:refresh', [
            '--path' => array_values($migrations)
        ]);

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
