<?php

namespace BuildEmpire\Apartment\Helpers;

class ApartmentHelpers
{
    /**
     * Does the schema only contain letters and/or numbers.
     *
     * @param $schemaName
     * @return bool
     */
    public static function isSchemaNameValid($schemaName)
    {
        return (preg_match('/^[a-z][a-z0-9_]*$/', $schemaName) && !ctype_upper($schemaName));
    }

    /**
     * This will remove any bad characters from the $schemaName variable.
     *
     * Note: Removes any non valid strings with #. You should be checking with isSchemaNameValid before reaching this
     * section. This is just an extra layer of protection.
     *
     * @param $schemaName
     * @return mixed
     */
    public static function getSchemaSafeString($schemaName) {
        return preg_replace('/^[^0-9a-z_]+$/', '#',$schemaName);
    }

    /**
     * Get the schema table.
     *
     * Note: Protection is only applied to the schema name. This is because you will not be adding tables from the user,
     * and only from the application, and applies outside the scope of apartment.
     *
     * @param $schemaName
     * @param $table
     * @return string
     */
    public static function getSchemaTableFormat($schemaName, $table = '') {
        return self::getSchemaSafeString($schemaName) . '.' . $table;
    }
}