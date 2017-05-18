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
     * Overrides the default Eloquent model by prefixing the schema to the table's property.
     *
     * By default the model will attach itself to the current schema. You may override this by passing the array
     * ['apartment' => 'nameofschema']
     *
     * The method:
     * 1. Extracts any apartment that has been set in the constructor and then passes the rest onto the Model class.
     * 2. If no apartment has been provided the model will automatically use the schema set through the singleton Schema
     *    class
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
        $this->apartment = ($this->apartment !== false ? $this->apartment : $schema->getSchemaName());

        $this->setTable(
            ApartmentHelpers::getSchemaTableFormat($this->apartment, $this->getTable())
        );
    }

    /**
     * Set the schemaName in this model. This will ONLY override this model's schema.
     *
     * @param $schemaName
     */
    public function setApartment($schemaName)
    {
        $this->apartment = $schemaName;

        $this->setTable(
            ApartmentHelpers::getSchemaTableFormat($this->apartment, $this->getTable())
        );
    }

    /**
     * Get apartment name used in the model.
     *
     * @return bool|string
     */
    public function getApartment()
    {
        return $this->apartment;
    }
}