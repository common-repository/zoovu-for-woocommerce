<?php

namespace Progressus\Zoovu;

class Pages
{
    private static $pages = [
        \Progressus\Zoovu\Feeds\FeedsListPage::class,
        \Progressus\Zoovu\Feeds\FeedsCreatePage::class
    ];

    public static function register()
    {
        foreach (self::$pages as $page) {
            $page::add();
        }
    }
}