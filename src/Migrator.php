<?php

namespace BuildEmpire\Apartment;

use Illuminate\Support\Str;

class Migrator
{
    /**
     * Manually run the migrations for all apartments.
     *
     * Note: If a migration has not been ran via artisan it will not be applied until is has!
     */
    public static function runMigrations() {
        $path = join(DIRECTORY_SEPARATOR, [base_path() , 'database', 'migrations']);

        foreach (glob($path . '/*_*.php') as $filename) {
            $migration = static::resolve($filename);
            if (!$migration instanceof ApartmentMigration) {
                continue;
            }

            $migration->up(false);
        }
    }

    /**
     * Resolve a migration instance from a file.
     *
     * @param  string  $file
     * @return object
     */
    protected static function resolve($fileName)
    {
        $migrationName = static::getMigrationName($fileName);
        $class = Str::studly(implode('_', array_slice(explode('_', $migrationName), 4)));

        return new $class;
    }

    /**
     * Get the name of the migration.
     *
     * @param  string  $path
     * @return string
     */
    protected static function getMigrationName($path)
    {
        return str_replace('.php', '', basename($path));
    }
}