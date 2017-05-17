<?php

namespace BuildEmpire\Apartment;

use BuildEmpire\Apartment\Exceptions\SchemaAlreadyExistsException;
use BuildEmpire\Apartment\Exceptions\SchemaCannotBePublicException;
use BuildEmpire\Apartment\Exceptions\SchemaDoesntExistException;
use BuildEmpire\Apartment\Exceptions\SchemaNameNotValidException;
use BuildEmpire\Apartment\Schema;
use Illuminate\Support\Facades\Schema as LumenSchema;
use Carbon\Carbon;
use BuildEmpire\Apartment\Helpers\ApartmentHelpers;

class ArtisanApartmentCommands
{
    const ARTISAN = 1;
    const WEB = 2;
    const PUBLIC_SCHEMA = 'public';

    protected $schema = false;

    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
    }

    public function tryMakeSchema($schemaName) {

        if (!ApartmentHelpers::isSchemaNameValid($schemaName)) {
            throw new SchemaNameNotValidException('The apartment ' . $schemaName . ' is not valid. It must be all lowercase and only contain letters, numbers, or underscores.');
        }

        if ($this->schema->doesSchemaExist($schemaName)) {
            throw new SchemaAlreadyExistsException('The apartment ' . $schemaName . ' already exists.');
        }

        if ($this->isSchemaPublic($schemaName)) {
            throw new SchemaCannotBePublicException('The apartment name cannot be ' . self::PUBLIC_SCHEMA);
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
        if (!ApartmentHelpers::isSchemaNameValid($schemaName)) {
            throw new SchemaNameNotValidException('The apartment ' . $schemaName . ' is not valid. It must be all lowercase and only contain letters, numbers, or underscores.');
        }

        if (!$this->schema->doesSchemaExist($schemaName)) {
            throw new SchemaDoesntExistException('The apartment ' . $schemaName . ' does not exist.');
        }

        app('db')->transaction(function() use ($schemaName) {
            $this->dropSchema($schemaName);
        });
    }

    /**
     * Create the schema.
     *
     * @param $schemaName
     */
    protected function createSchema($schemaName) {
        app('db')->statement('CREATE SCHEMA ' . ApartmentHelpers::getSchemaSafeString($schemaName));
    }

    /**
     * Drop the schema.
     *
     * @param $schemaName
     */
    protected function dropSchema($schemaName) {
        app('db')->statement('DROP SCHEMA ' . ApartmentHelpers::getSchemaSafeString($schemaName) . ' CASCADE');
    }

    /**
     * Create the schema's migration table which will mimic the public migration table.
     *
     * @param $schemaName
     */
    protected function createSchemaMigrationTable($schemaName) {
        LumenSchema::create(ApartmentHelpers::getSchemaTableFormat($schemaName, 'migrations'), function($table)
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
        LumenSchema::create(ApartmentHelpers::getSchemaTableFormat($schemaName, 'apartment_metadata'), function($table)
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
        app('db')->table(ApartmentHelpers::getSchemaTableFormat($schemaName, 'apartment_metadata'))->insert([
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
        if ($schemaName != self::PUBLIC_SCHEMA) {
            return false;
        }

        return true;
    }
}