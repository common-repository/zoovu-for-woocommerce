<?php

namespace Progressus\Zoovu;

use Progressus\Zoovu\WC\Integration;

class Options
{
    const OPTION_API_KEY_KEY = 'api_key';
    const OPTION_LOGGING_KEY = 'logging';
    const OPTION_TRACK_SCRIPT = 'track_script';
    const OPTION_TRACK_ENABLED = 'track_enabled';

    /**
     * @var Options
     */
    private static $instance;

    /**
     * @var array
     */
    private static $options;

    /**
     * Options constructor.
     */
    private function __construct()
    {
        self::$options = get_option(Integration::INTEGRATION_OPTION_KEY, [
            self::OPTION_API_KEY_KEY => null,
            self::OPTION_LOGGING_KEY => 'yes',
            self::OPTION_TRACK_SCRIPT => null,
            self::OPTION_TRACK_ENABLED => 'yes',
        ]);
    }

    /**
     * @return string|null
     */
    public static function getApiKey()
    {
        return self::$options[self::OPTION_API_KEY_KEY];
    }

    /**
     * @return bool
     */
    public static function isLoggingEnabled()
    {
        return self::$options[self::OPTION_LOGGING_KEY] == 'yes' ? true : false;
    }

    /**
     * @return string|null
     */
    public static function getTrackScript()
    {
        return self::$options[self::OPTION_TRACK_SCRIPT];
    }

    /**
     * @return bool
     */
    public static function isTrackingEnabled()
    {
        return self::$options[self::OPTION_TRACK_ENABLED] == 'yes' ? true : false;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public static function set(string $key, $value)
    {
        self::$options[$key] = $value;

        update_option(Integration::INTEGRATION_OPTION_KEY, self::$options);
    }

    /**
     * @return Options
     */
    public static function load()
    {
        if (! isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
