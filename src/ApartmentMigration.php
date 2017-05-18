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
     */
    public function up($ranViaArtisan = true) {

        if ($ranViaArtisan) {
            $this->throwExceptionIfNoTable();
        }

        foreach($this->schemas as $schema) {

            if (!$ranViaArtisan && !$this->hasPublicMigrationRan()) {
                 continue;
            }

            if ($this->hasSchemaMigrationRan($schema->name)) {
                continue;
            }

            $this->setSchemaTable($schema->name);
            $this->updateSchemaMigrateUp($schema->name);
            $this->apartmentUp();

        }
    }

    /**
     * Run/Reset all apartment migrations.
     *
     * This will only be run via the artisan command.
     *
     */
    public function down() {
        foreach($this->schemas as $schema) {

            $this->throwExceptionIfNoTable();

            if (!$this->hasSchemaMigrationRan($schema->name)) {
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
    protected function throwExceptionIfNoTable() {
        if (empty($this->table)) {
            throw new MissingTableNameInMigrationException('The migration '.$this->fileName.' has not specified a table.');
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
    protected function hasMigrationRan($schemaName = 'public') {

        $migration = app('db')
            ->table(ApartmentHelpers::getSchemaTableFormat($schemaName, 'migrations'))
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
        $this->table = ApartmentHelpers::getSchemaTableFormat($schemaName, $this->originalTable);
    }

    /**
     * Update schema's migration file. (add migration)
     */
    protected function updateSchemaMigrateUp($schemaName) {
        app('db')
            ->table(ApartmentHelpers::getSchemaTableFormat($schemaName , 'migrations'))
            ->insert([
                'migration' => $this->fileName
        ]);
    }

    /**
     * Update schema's migration file. (remove migration)
     */
    protected function updateSchemaMigrateDown($schemaName) {
        app('db')
            ->table(ApartmentHelpers::getSchemaTableFormat($schemaName , 'migrations'))
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

}