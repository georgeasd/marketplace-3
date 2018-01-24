<?php

namespace App\Api\Request\DB;


use Illuminate\Support\Collection;

trait BasicDBRequest
{
    /**
     * @var string
     */
    protected $modelClass;

    /**
     * @var string
     */
    protected $resourceClass;

    /**
     * BasicSingleRequest constructor.
     * @param string $modelClass
     * @param string $resourceClass
     */
    public function __construct($modelClass, $resourceClass)
    {
        $this->modelClass = $modelClass;
        $this->resourceClass = $resourceClass;
    }

    /**
     * Returns name of a Model class to be used.
     * @return string
     */
    protected function modelClass()
    {
        return $this->modelClass;
    }

    /**
     * Returns name of a Resource class to be used. If false, no Resource class used
     * @return string|false
     */
    protected function resourceClass()
    {
        return $this->resourceClass;
    }

    /**
     * Get a Collection of parameters related to the database
     * @param Collection $parameters
     * @return Collection
     */
    protected function getDBParameters(Collection $parameters)
    {
        return $parameters->except(array_keys(static::_rules()));
    }
}