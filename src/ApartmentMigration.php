<?php

namespace BuildEmpire\Apartment;

use Illuminate\Database\Migrations\Migration;
use BuildEmpire\Apartment\Helpers\ApartmentHelpers;
use BuildEmpire\Apartment\Exceptions\MissingTableNameInMigrationException;

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
        $schema = new Schema();
        $this->schemas = $schema->getAllSchemas();
        $this->fileName = $this->getMigrationFileName();
        $this->originalTable = $this->table;
    }

    /**
     * Run/Up all apartment migrations.
     *
     * This command could be run via artisan or the ApartmentMigration code.
     *
     * ApartmentMigration
     * ==================
     * Ran when creating a schema from within the code eg: making a new schema for a user. You only want the  migrations
     * that have been added to the core migration to be applied.
     *
     * Artisan
     * =======
     * Ran from the command line and import/run all apartment migrations.
     *
     * @param bool $ranViaArtisan
     * @param string|null $schemaName - run for single schema
     * @return void
     */
    public function up($ranViaArtisan = true, string $schemaName = null): void
    {
        if ($ranViaArtisan) {
            $this->throwExceptionIfNoTable();
        }

        if ($schemaName) {
            $this->singleSchemaUp($ranViaArtisan, $schemaName);
            return;
        }

        // Run for all schemas if not schema is passed
        foreach ($this->schemas as $schema) {
            $this->singleSchemaUp($ranViaArtisan, $schema->name);
        }
    }

    /**
     * Run migrations for a single schema
     *
     * @param boolean $ranViaArtisan
     * @param string $schemaName
     * @return void
     */
    private function singleSchemaUp(bool $ranViaArtisan, string $schemaName): void
    {
        if (!$ranViaArtisan && !$this->hasPublicMigrationRan()) {
            return;
        }

        if ($this->hasMigrationRan($schemaName)) {
            return;
        }

        $this->setSchemaTable($schemaName);
        $this->updateSchemaMigrateUp($schemaName);
        $this->apartmentUp();
    }

    /**
     * Run/Reset all apartment migrations.
     *
     * This will only be run via the artisan command.
     */
    public function down()
    {
        foreach ($this->schemas as $schema) {

            $this->throwExceptionIfNoTable();

            if (!$this->hasMigrationRan($schema->name)) {
                continue;
            }

            $this->setSchemaTable($schema->name);
            $this->updateSchemaMigrateDown($schema->name);
            $this->apartmentDown();
        }
    }


    /**
     * Throw an exception if no table is specified and migration has been ran.
     *
     * We ONLY want to test this if the user is running the command via artisan. Otherwise, this migration might not have
     * ever been ran and could stop new schemas being created.
     *
     * @throws MissingTableNameInMigrationException
     */
    protected function throwExceptionIfNoTable()
    {
        if (empty($this->table)) {
            throw new MissingTableNameInMigrationException('The migration ' . $this->fileName . ' has not specified a table.');
        }
    }

    /**
     * Does the public migration have this migration file.
     *
     * @return bool
     */
    protected function hasPublicMigrationRan()
    {
        return $this->hasMigrationRan('public');
    }

    /**
     * Check if migration has ran on a particular schema.
     *
     * @param string $schemaName
     * @return bool
     */
    protected function hasMigrationRan($schemaName)
    {

        return (boolean)app('db')
            ->table(ApartmentHelpers::getSchemaTableFormat($schemaName, 'migrations'))
            ->where('migration', '=', $this->fileName)
            ->count();
    }

    /**
     * Prefix the schema to the table of this current migration.
     *
     * @param $schemaName
     */
    private function setSchemaTable($schemaName)
    {
        $this->table = ApartmentHelpers::getSchemaTableFormat($schemaName, $this->originalTable);
    }

    /**
     * Update schema's migration file. (add migration)
     */
    protected function updateSchemaMigrateUp($schemaName)
    {
        app('db')
            ->table(ApartmentHelpers::getSchemaTableFormat($schemaName, 'migrations'))
            ->insert([
                'migration' => $this->fileName
            ]);
    }

    /**
     * Update schema's migration file. (remove migration)
     */
    protected function updateSchemaMigrateDown($schemaName)
    {
        app('db')
            ->table(ApartmentHelpers::getSchemaTableFormat($schemaName, 'migrations'))
            ->where('migration', '=', $this->fileName)
            ->delete();
    }

    /**
     * Get migration filename of the child class. The class that has extended this class.
     *
     * @return string
     */
    protected function getMigrationFileName()
    {
        $childClassName = get_called_class();
        $reflection = new \ReflectionClass($childClassName);
        $fileInfo = pathinfo($reflection->getFileName());

        return $fileInfo['filename'];
    }

}