<?php

namespace BuildEmpire\Apartment;

use Illuminate\Database\Migrations\Migration;

class ApartmentMigration extends Migration
{
    protected $schemas = [];
    protected $fileName = false;
    protected $originalTable;

    /**
     * ApartmentMigration constructor.
     */
    public function __construct()
    {
        $this->schemas = $this->getSchemas();
        $this->fileName = $this->getMigrationFileName();
        $this->originalTable = $this->table;
    }

    /**
     * Run/Up all apartment migrations.
     *
     * @param bool $ranViaArtisan
     */
    public function up($ranViaArtisan = true) {
        foreach($this->schemas as $schema) {

            $schemaTablePrefix = $schema->name . '.';

            if (!$ranViaArtisan && !$this->hasPublicMigrationRan()) {
                 continue;
            }

            if ($this->hasSchemaMigrationRan($schemaTablePrefix)) {
                continue;
            }

            $this->setSchemaTable($schemaTablePrefix);
            $this->updateSchemaMigrateUp($schemaTablePrefix);
            $this->apartmentUp();

        }
    }

    /**
     * Run/Reset all apartment migrations.
     */
    public function down() {
        foreach($this->schemas as $schema) {

            $schemaTablePrefix = $schema->name . '.';

            if (!$this->hasSchemaMigrationRan($schemaTablePrefix)) {
                continue;
            }

            $this->setSchemaTable($schemaTablePrefix);
            $this->updateSchemaMigrateDown($schemaTablePrefix);
            $this->apartmentDown();
        }
    }

    /**
     * Does the public migration have this migration file.
     *
     * @return bool
     */
    protected function hasPublicMigrationRan() {
        return $this->hasMigrationRan();
    }

    /**
     * Does the $schemaName migration have this migration file.
     *
     * @param $schemaName
     * @return bool
     */
    protected function hasSchemaMigrationRan($schemaName) {
        return $this->hasMigrationRan($schemaName);
    }

    /**
     * Check if migration has ran on a particular schema.
     *
     * @param string $schemaName
     * @return bool
     */
    protected function hasMigrationRan($schemaName = 'public.') {
        $migration = app('db')
            ->table($schemaName . 'migrations')
            ->where('migration', '=', $this->fileName)
            ->count();

        return (boolean) $migration;
    }

    /**
     * Prefix the schema to the table of this current migration.
     *
     * @param $schemaName
     */
    private function setSchemaTable($schemaName) {
        $this->table = $schemaName . $this->originalTable;
    }

    /**
     * Update schema's migration file. (add migration)
     */
    protected function updateSchemaMigrateUp($schemaName) {
        app('db')
            ->table($schemaName . 'migrations')
            ->insert([
                'migration' => $this->fileName
        ]);
    }

    /**
     * Update schema's migration file. (remove migration)
     */
    protected function updateSchemaMigrateDown($schemaName) {
        app('db')
            ->table($schemaName . 'migrations')
            ->where('migration', '=', $this->fileName)
            ->delete();
    }

    /**
     * Get migration filename of the child class. The class that has extended this class.
     *
     * @return mixed
     */
    protected function getMigrationFileName() {
        $childClassName = get_called_class();
        $reflection = new \ReflectionClass($childClassName);
        $fileInfo = pathinfo($reflection->getFileName());

        return $fileInfo['filename'];
    }

    /**
     * Get all schemas excluding public from the database.
     *
     * @return mixed
     */
    protected function getSchemas() {
        return app('db')
            ->select("SELECT DISTINCT schemaname AS name FROM pg_tables WHERE schemaname NOT IN ('information_schema', 'pg_catalog') AND schemaname != 'public'");
    }
}