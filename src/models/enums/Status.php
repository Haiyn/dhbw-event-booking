<?php

namespace models\enums;

use ReflectionClass;

class Status
{
    public static $INVITED = "invited";
    public static $ACCEPTED = "accepted";

    public static function staticProperties()
    {
        $class = new ReflectionClass('\models\enums\Status');
        $properties = [];
        foreach ($class->getStaticProperties() as $key => $value) {
            array_push($properties, $value);
        }
        return $properties;
    }
}
