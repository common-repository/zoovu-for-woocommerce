<?php

namespace Progressus\Zoovu;

use Progressus\Zoovu\Helpers\PostType;

class Types
{
    /**
     * @var PostType[]
     */
    private static $types = [
        \Progressus\Zoovu\Feeds\FeedsType::class
    ];

    public static function register()
    {
        $types = self::$types;

        foreach ($types as $type_name) {
            (new $type_name)->register();
        }
    }
}