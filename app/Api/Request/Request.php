<?php

namespace App\Api\Request;


use App\Api\Response\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * API request class
 */
abstract class Request
{

    /**
     * Decides whether the request will be resolved.
     * If the request should not run, returns the error message. Otherwise returns true.
     * @return true
     */
    protected function shouldResolve()
    {
        return true;
    }

    /**
     * Returns validation rules for the request parameters
     * @return array
     */
    protected function rules()
    {
        return [];
    }

    /**
     * This function is called only when all validation passed.
     * Should return a Response.
     *
     * @param string $name
     * @param array $parameters
     * @throws ValidationException
     * @return Response
     */
    protected abstract function doResolve($name, $parameters);

    /**
     * Call this to resolve the request and get a Response instance
     *
     * @param string $name
     * @param array $parameters
     * @return Response
     */
    public final function resolve($name, $parameters)
    {
        try {
            if (($errorMsg = $this->shouldResolve()) !== true) {
                return new Response($name, false, $errorMsg);
            }

            $validator = Validator::make($parameters, $this->rules());
            $validator->validate();

            return $this->doResolve($name, $parameters);
        } catch (ValidationException $e) {
            return new Response($name, false, [
                'validation' => $e->errors()
            ]);
        }
    }
}