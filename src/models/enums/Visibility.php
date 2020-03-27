<?php

namespace models\enums;

use ReflectionClass;
use ReflectionException;

class Visibility
{
    public static $INVITE_ONLY = "invite-only";
    public static $PUBLIC = "public";

    /**
     * Get all static properties of this class
     * @return array * Array of static properties
     * @throws ReflectionException
     */
    public static function staticProperties()
    {
        $class = new ReflectionClass('\models\enums\Visibility');
        $properties = [];
        foreach ($class->getStaticProperties() as $key => $value) {
            array_push($properties, $value);
        }
        return $properties;
    }
}
