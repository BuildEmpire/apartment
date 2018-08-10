<?php

namespace BuildEmpire\Apartment\Helpers;

use BuildEmpire\Apartment\Exceptions\SchemaNameNotValidException;

class ApartmentHelpers
{

    const PUBLIC_SCHEMA = 'public';

    /**
     * Is the schemaName a protected name.
     *
     * @param $schemaName
     * @return bool
     */
    protected static function isAProtectedSchemaName($schemaName) {
        if (strpos($schemaName, 'pg_') === 0) {
            return true;
        }

        return false;
    }

    /**
     * Is the schemaName public.
     *
     * @param $schemaName
     * @return bool
     */
    protected static function isPublicSchema($schemaName) {
        return $schemaName == static::PUBLIC_SCHEMA;
    }

    /**
     * Check if a valid schema
     * @param $schemaName
     * @return int
     */
    public static function isSchemaNameValid($schemaName)
    {
        return self::isAProtectedSchemaName($schemaName) == false && (preg_match('/^[a-z][a-z0-9_-]*$/', $schemaName));
    }

    /**
     * Is a valid apartment schema name.
     *
     * @param $schemaName
     * @return bool
     */
    public static function isApartmentSchemaNameValid($schemaName) {
        return !static::isPublicSchema($schemaName) && static::isSchemaNameValid($schemaName);
    }

    /**
     * Gets the schema and table back in the correct format, and will throw an exception if the schema name is not valid.
     *
     * @param $schemaName
     * @param $table
     * @return string
     * @throws SchemaNameNotValidException
     */
    public static function getSchemaTableFormat($schemaName, $table)
    {
        if (!self::isSchemaNameValid($schemaName)) {
            throw new SchemaNameNotValidException('The apartment ' . $schemaName . ' is not valid. It must be all lowercase and only contain letters, numbers, or underscores.');
        }

        return $schemaName . '.' . $table;
    }
}