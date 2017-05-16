<?php

namespace BuildEmpire\Apartment;

use BuildEmpire\Apartment\Exceptions\NoSchemaSet;
use BuildEmpire\Apartment\Exceptions\NoSchemaFound;
use BuildEmpire\Apartment\Exceptions\SchemaAlreadyExists;
use BuildEmpire\Apartment\Exceptions\SchemaDoesntExist;
use Illuminate\Support\Facades\Schema as LumenSchema;
use Carbon\Carbon;

class Schema
{
    protected $schemaName = false;

    public function trySetSchemaName($schemaName) {
        $safeSchemaName = $this->getSchemaNameFromModel($schemaName);
        if ($this->isSchemaEmptyValue($safeSchemaName)) {
            throw new NoSchemaFound('No schema found.');
        }
        $this->schemaName = $safeSchemaName;
    }

    public function tryGetSchemaName() {
        if ($this->schemaName !== false) {
            return $this->schemaName;
        }

        throw new NoSchemaSet('No schema is set. If you want to check if a schema exists use doesSchemaExist method.');
    }

    public function isSchemaSet() {
        return (!$this->schemaName === false);
    }

    public function doesSchemaExist($schemaName) {
        return (boolean) $this->getSchemaNameFromModel($schemaName);
    }

    protected function getSchemaNameFromModel($schemaName) {
        return app('db')
            ->table('apartments')
            ->where('schema_name', $schemaName)
            ->value('schema_name');
    }

    protected function isSchemaEmptyValue($schemaName) {
        return ((boolean) $schemaName == false);
    }

    public function tryMakeSchema($schemaName) {

        $existingSchemaName = $this->getSchemaNameFromModel($schemaName);

        if (!$this->isSchemaEmptyValue($existingSchemaName)) {
            throw new SchemaAlreadyExists('The schema ' . $existingSchemaName . ' already exists in the apartments table.');
        }

        app('db')->transaction(function() use ($schemaName) {
            app('db')->table('apartments')->insert([
                'schema_name' => $schemaName,
                'created_at' => Carbon::now(),
            ]);

            app('db')->statement('CREATE SCHEMA ' . $schemaName);

            LumenSchema::create($schemaName . '.migrations', function($table)
            {
                $table->increments('id');
                $table->string('migration');
            });

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

    public function listAllSchemas() {
        return app('db')->table('apartments')->get();
    }



}