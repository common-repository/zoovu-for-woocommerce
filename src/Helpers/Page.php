<?php

namespace Progressus\Zoovu\Helpers;

interface Page
{
    /**
     * Add menu item under Woocommerce
     */
    public static function add();

    public function addMenuItem();
}