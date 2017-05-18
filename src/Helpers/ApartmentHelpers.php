<?php

namespace BuildEmpire\Apartment\Helpers;

use BuildEmpire\Apartment\Exceptions\SchemaNameNotValidException;

class ApartmentHelpers
{
    /**
     * Does the schema only contain letters and/or numbers.
     *
     * @param $schemaName
     * @return int
     */
    public static function isSchemaNameValid($schemaName)
    {
        return (preg_match('/^[a-z][a-z0-9_]*$/', $schemaName));
    }

    /**
     * Gets the schema and table back in the correct format, and will throw an exception if the schema name is not valid.
     *
     * @param $schemaName
     * @param string $table
     * @return string
     * @throws SchemaNameNotValidException
     */
    public static function getSchemaTableFormat($schemaName, $table = '')
    {
        if (!self::isSchemaNameValid($schemaName)) {
            throw new SchemaNameNotValidException('The apartment ' . $schemaName . ' is not valid. It must be all lowercase and only contain letters, numbers, or underscores.');
        }

        return $schemaName . '.' . $table;
    }
}