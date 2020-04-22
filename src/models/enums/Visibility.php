<?php

namespace models\enums;

use ReflectionClass;
use ReflectionException;

/**
 * Class Visibility
 * Enumerator for visibility settings of events.
 * @package models\enums
 */
class Visibility
{
    public static $INVITE_ONLY = "invite-only";
    public static $PUBLIC = "public";

    /**
     * Get all static properties of this class
     * @return array * Array of static properties
     */
    public static function staticProperties()
    {
        try {
            $class = new ReflectionClass('\models\enums\Visibility');
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
