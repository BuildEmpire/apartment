<?php

namespace BuildEmpire\Apartment;

use BuildEmpire\Apartment\Helpers\ApartmentHelpers;
use Illuminate\Database\Eloquent\Model;

class ApartmentModel extends Model
{
    protected $apartment = false;

    /**
     * ApartmentModel constructor.
     *
     * Overrides the default Eloquent model by prefixing the table's schema.
     */
    public function __construct()
    {
        $args = func_get_args();

        if (isset($args[0]['apartment'])) {
            $this->apartment = $args[0]['apartment'];
            unset($args[0]['apartment']);


        }

        call_user_func_array('parent::__construct', $args);

        $schema = app()->make('BuildEmpire\Apartment\Schema');

        $this->apartment = ($this->apartment !== false ? $this->apartment : $schema->tryGetSchemaName());

        $this->setTable(
            ApartmentHelpers::getSchemaTableFormat($this->apartment, $this->getTable())
        );
    }

    /**
     * Set the schemaName.
     *
     * @param $schemaName
     */
    public function setApartment($schemaName) {
        $this->apartment = $schemaName;

        $this->setTable(
            ApartmentHelpers::getSchemaTableFormat($this->apartment, $this->getTable())
        );
    }

    /**
     * Get apartment name used in the model.
     *
     * @return bool
     */
    public function getApartment() {
        return $this->apartment;
    }
}