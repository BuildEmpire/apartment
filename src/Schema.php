<?php

namespace BuildEmpire\Apartment;

use BuildEmpire\Apartment\Exceptions\NoSchemaSetException;
use BuildEmpire\Apartment\Exceptions\NoSchemaFoundException;
use BuildEmpire\Apartment\Helpers\ApartmentHelpers;

class Schema
{
    protected $schemaName = false;

    /**
     * Try set the schema name.
     *
     * @param $schemaName
     * @throws NoSchemaFoundException
     */
    public function setSchemaName($schemaName) {
        if (!ApartmentHelpers::isSchemaNameValid($schemaName)) {
            throw new SchemaNameNotValidException('The apartment ' . $schemaName . ' is not valid. It must be all lowercase and only contain letters, numbers, or underscores.');
        }
        $this->schemaName = $schemaName;
    }

    /**
     * Try to get the schema name.
     *
     * @return bool
     * @throws NoSchemaSetException
     */
    public function getSchemaName() {
        if ($this->schemaName !== false) {
            return $this->schemaName;
        }

        throw new NoSchemaSetException('No schema is set. If you want to check if a schema exists use doesSchemaExist method.');
    }

    /**
     * Is the schema currently set?
     *
     * @return bool
     */
    public function isSchemaSet() {
        return (!$this->schemaName === false);
    }

    /**
     * Does the schema exist?
     *
     * @param $schemaName
     * @return bool
     */
    public function doesSchemaExist($schemaName) {
        return (boolean) $this->getSchemaObjectSet()->where('schemaname', '=', $schemaName)->count();
    }

    /**
     * Get all schemas excluding public from the database.
     *
     * @return mixed
     */
    public function getAllSchemas() {
        return $this->getSchemaObjectSet()->get();
    }

    /**
     * Return a chain db object to allow you append additional parameters to object.
     *
     * Note: Postgresql does not use standard tables to store schema data, but you can still access it via a select.
     *
     * @return schema object
     */
    protected function getSchemaObjectSet() {
        return app('db')
            ->table('pg_catalog.pg_tables')
            ->select('schemaname as name')
            ->distinct('schemaname')
            ->where('schemaname', '!=', 'pg_catalog')
            ->where('schemaname', '!=', 'information_schema')
            ->where('schemaname', '!=', 'public');
    }

}