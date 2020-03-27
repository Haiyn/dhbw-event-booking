<?php

namespace models\enums;

use ReflectionClass;

class Visibility
{
    public static $INVITE_ONLY = "invite-only";
    public static $PUBLIC = "public";

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
