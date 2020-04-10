<?php

namespace components\core;

class ControllerException extends \Exception
{
    private $params;

    public function __construct($message, $params = [])
    {
        $this->params = $params;
        parent::__construct($message);
    }

    public function getParams()
    {
        return $this->params;
    }
}
