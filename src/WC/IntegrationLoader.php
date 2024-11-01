<?php

namespace Progressus\Zoovu\WC;

class IntegrationLoader
{
    /**
     * Initialize loader
     */
    public static function init()
    {
        add_action('plugins_loaded', [new self, 'load']);
    }

    /**
     * Load
     */
    public function load()
    {
        if ( ! class_exists('WC_Integration')) {
            return;
        }

        add_filter('woocommerce_integrations', [$this, 'add']);
    }

    /**
     * Add integration
     *
     * @filter woocommerce_integrations
     *
     * @param $integrations
     * @return mixed
     */
    public function add($integrations)
    {
        $integrations[] = Integration::class;

        return $integrations;
    }
}
