<?php

namespace BuildEmpire\Apartment;

use BuildEmpire\Apartment\Helpers\ApartmentHelpers;
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

        $schema = app()->make('BuildEmpire\Apartment\Schema');

        $this->setTable(
            ApartmentHelpers::getSchemaTableFormat($schema->tryGetSchemaName(), $this->getTable())
        );
    }
}