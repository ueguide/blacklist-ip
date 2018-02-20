<?php

namespace TheLHC\BlacklistIp\Console;

use Illuminate\Console\Command;

class MigrationCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */

    protected $name = 'blacklist_ip:migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a migration following the Blacklist Ip specifications.';

    /**
     * Handle command execution for laravel 5.1
     */
    public function fire()
    {
        $this->handle();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = database_path('migrations');
        $this->line('');
        $this->comment("The migrations that create \"Blacklist IP\" tables will be created in: {$path}");
        $this->line('');
        $this->createMigrations();
    }

    /**
     * Create the migration.
     *
     * @return bool
     */
    protected function createMigrations()
    {
        foreach(glob(__DIR__.'/../../migrations/*.php') as $file) {
            $pieces = explode('/', $file);
            $filename = array_pop($pieces);
            $migrationName = date('Y_m_d_His').'_'.$filename;
            $migration = database_path('migrations').'/'.$migrationName;
            if (!file_exists($migration) && $fs = fopen($migration, 'x')) {
                $buffer = file_get_contents($file);
                fwrite($fs, $buffer);
                fclose($fs);
                $this->info("Successfully created {$migrationName}!");
            }
        }
    }
}
