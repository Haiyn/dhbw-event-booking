<?php

namespace models\enums;

use ReflectionClass;
use ReflectionException;

class Status
{
    public static $INVITED = "invited";
    public static $ACCEPTED = "accepted";

    /**
     * Get all static properties of this class
     * @return array * Array of static properties
     */
    public static function staticProperties()
    {
        try {
            $class = new ReflectionClass('\models\enums\Status');
            $properties = [];
            foreach ($class->getStaticProperties() as $key => $value) {
                array_push($properties, $value);
            }
            return $properties;
        } catch (ReflectionException $exception) {
            header("Location: /internal-error");
            return [];
        }
    }
}
