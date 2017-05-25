<?php

namespace BuildEmpire\Apartment;

use BuildEmpire\Apartment\Helpers\ApartmentHelpers;
use Illuminate\Database\Eloquent\Model;
use BuildEmpire\Apartment\Exceptions\NoSchemaFoundException;

class ApartmentModel extends Model
{
    protected $apartment = false;
    protected $apartmentSchema = false;

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
     *
     * @param \BuildEmpire\Apartment\Schema $schema
     * @throws NoSchemaFoundException
     */
    public function __construct()
    {
        $args = func_get_args();
        $apartment = false;
        $this->apartmentSchema = app()->make('BuildEmpire\Apartment\Schema');

        if (isset($args[0]['apartment'])) {
            $apartment = $args[0]['apartment'];
            unset($args[0]['apartment']);
        }

        call_user_func_array('parent::__construct', $args);
        $apartment = ($apartment !== false ? $apartment : $this->apartmentSchema->getSchemaName());

        if (!$this->apartmentSchema->doesSchemaExist($apartment)) {
            throw new NoSchemaFoundException('Schema ' . $apartment . ' cannot be found.');
        }

        $this->setTable(
            ApartmentHelpers::getSchemaTableFormat($apartment, $this->getTable())
        );

        $this->apartment = $apartment;
    }

    /**
     * Set the schemaName in this model. This will ONLY override this model's schema.
     *
     * @param $schemaName
     */
    public function setApartment($schemaName)
    {
        if (!$this->apartmentSchema->doesSchemaExist($schemaName)) {
            throw new NoSchemaFoundException('Schema ' . $schemaName . ' cannot be found.');
        }

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