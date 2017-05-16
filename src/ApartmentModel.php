<?php

namespace BuildEmpire\Apartment;

use BuildEmpire\Apartment\Exceptions\NoSchemaSet;
use Illuminate\Database\Eloquent\Model;

class ApartmentModel extends Model
{
    /**
     * ApartmentModel constructor.
     *
     * Overrides the default Eloquent model by prefixing the table's schema.
     */
    public function __construct()
    {
        $args = func_get_args();
        call_user_func_array('parent::__construct', $args);

        $apartment = app()->make('BuildEmpire\Apartment\Schema');
        $schema = $apartment->getSchema();

        if (!$schema) {
            throw new NoSchemaSet('No schema is set to prefix to the Eloquent model.');
        }

        $this->setTable($schema . '.' . $this->getTable());
    }
}