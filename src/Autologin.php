<?php

namespace Progressus\Zoovu;

use Progressus\Zoovu\Api\Client;
use Progressus\Zoovu\Helpers\Notice;

class Autologin
{
    /**
     * Session token
     *
     * @var string
     */
    private $sessionToken;

    /**
     * Host
     *
     * @var string
     */
    private $host;

    /**
     * Register Autologin functionality
     */
    public static function register()
    {
        if (! isset($_REQUEST['autologin']) || ! $_REQUEST['autologin']) {
            return;
        }

        (new self())
            ->setSessionToken()
            ->setTrackingCode()
            ->login();
    }

    /**
     * Get Session token and set it to Autologin class
     *
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function setSessionToken()
    {
        try {
            $client = new Client();

            $results = json_decode($client->getSessionToken());

            // Set session token
            $this->sessionToken = isset($results->sessionToken) ? $results->sessionToken : null;
            $this->host = isset($results->host) ? $results->host : null;

        } catch (\Exception $e) {
            error_log('There was an error during the session token retrieval: ' . $e->getMessage());
        }

        return $this;
    }

    /**
     * Set Ext Tracking code
     *
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function setTrackingCode()
    {
        try {
            $client = new Client();

            $results = $client->getTrackingScriptTag();

            Options::set(Options::OPTION_TRACK_SCRIPT, $results);
        } catch (\Exception $e) {
            error_log('There was an error during the tracking code retrieval: ' . $e->getMessage());
        }

        return $this;
    }

    private function login()
    {
        if (! $this->sessionToken || ! $this->host) {
            Notice::error(__('Invalid session token, please contact site administration!', \ZoovuFeeds::LOCALE_SLUG));

            return;
        }

        wp_redirect(sprintf(
            '%s/autosignin/%s',
            $this->host,
            $this->sessionToken
        ));
    }

    public static function getUrl()
    {
        $params = [
            'page' => 'zoovo-feeds',
            'autologin' => true
        ];

        return add_query_arg($params, admin_url('admin.php'));
    }
}
