<?php

namespace models\enums;

use SplEnum;

class Visibility extends SplEnum
{
    public const DEFAULT = self::PUBLIC;

    public const PUBLIC = 0;
    public const INVITE_ONLY = 1;
}
