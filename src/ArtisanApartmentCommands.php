<?php

namespace BuildEmpire\Apartment;

use BuildEmpire\Apartment\Exceptions\SchemaAlreadyExists;
use BuildEmpire\Apartment\Exceptions\SchemaCannotContainUppsercaseCharacters;
use BuildEmpire\Apartment\Exceptions\SchemaCannotBePublic;
use BuildEmpire\Apartment\Exceptions\SchemaDoesntExist;
use BuildEmpire\Apartment\Schema;
use Illuminate\Support\Facades\Schema as LumenSchema;
use Carbon\Carbon;

class ArtisanApartmentCommands
{
    const ARTISAN = 1;
    const WEB = 2;
    const PUBLIC = 'public';

    protected $schema = false;

    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
    }

    public function tryMakeSchema($schemaName) {

        if (ctype_upper($schemaName)) {
            throw new SchemaCannotContainUppsercaseCharacters('The schema cannot contain any uppercase characters.');
        }

        if ($this->schema->doesSchemaExist($schemaName)) {
            throw new SchemaAlreadyExists('The schema ' . $schemaName . ' already exists in the apartments table.');
        }

        if ($this->isSchemaPublic($schemaName)) {
            throw new SchemaCannotBePublic('The schema name cannot be ' . self::PUBLIC);
        }

        app('db')->transaction(function() use ($schemaName) {

            $this->createSchema($schemaName);

            $this->createSchemaMigrationTable($schemaName);

            $this->createSchemaMetadata($schemaName);

            $this->updateSchemaMetadata($schemaName, self::ARTISAN, Carbon::now()->getTimestamp());

            Migrator::runMigrations();
        });
    }

    public function tryDropSchema($schemaName) {
        $existingSchemaName = $this->getSchemaNameFromModel($schemaName);

        if ($this->isSchemaEmptyValue($existingSchemaName)) {
            throw new SchemaDoesntExist('The schema ' . $existingSchemaName . ' does not exists in the apartments table.');
        }

        app('db')->transaction(function() use ($schemaName) {
            app('db')->table('apartments')->where('schema_name', '=', $schemaName)->delete();
            app('db')->statement('DROP SCHEMA ' . $schemaName . ' CASCADE');
        });
    }

    /**
     * Create the schema.
     *
     * @param $schemaName
     */
    protected function createSchema($schemaName) {
        app('db')->statement('CREATE SCHEMA ' . $schemaName);
    }

    /**
     * Create the schema's migration table which will mimic the public migration table.
     *
     * @param $schemaName
     */
    protected function createSchemaMigrationTable($schemaName) {
        LumenSchema::create($schemaName . '.migrations', function($table)
        {
            $table->increments('id');
            $table->string('migration');
        });
    }

    /**
     * Create the schema's metadata table.
     *
     * @param $schemaName
     */
    protected function createSchemaMetadata($schemaName) {
        LumenSchema::create($schemaName . '.apartment_metadata', function($table)
        {
            $table->string('name');
            $table->integer('created_by');
            $table->integer('created_at');
        });
    }

    /**
     * Update schema's metadata.
     *
     * @param $schemaName
     * @param $createdBy
     * @param $createdAt
     */
    protected function updateSchemaMetadata($schemaName, $createdBy, $createdAt) {
        app('db')->table($schemaName . '.apartment_metadata')->insert([
            'name' => $schemaName,
            'created_by' => $createdBy,
            'created_at' => $createdAt,
        ]);
    }

    /**
     * Check if the schemaName is public.
     *
     * @param $schemaName
     * @return bool
     */
    protected function isSchemaPublic($schemaName) {
        if ($schemaName != self::PUBLIC) {
            return false;
        }

        return true;
    }
}