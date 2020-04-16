<?php

namespace components\core;

class ValidatorException extends \Exception
{
    private $params;

    public function __construct($message, $params = [])
    {
        $this->params = $params;
        parent::__construct($message);
    }

    /**
     * Return array of params. Needed to pass arguments to redirect url if needed
     * @return array * Array of params
     */
    public function getParams()
    {
        return $this->params;
    }
}
