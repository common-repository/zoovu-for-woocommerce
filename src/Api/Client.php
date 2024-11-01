<?php

namespace Progressus\Zoovu\Api;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Progressus\Zoovu\Api\Logger\WC_Logger;
use Progressus\Zoovu\Feeds\Feed;
use Progressus\Zoovu\Options;

/**
 * Class Zoovu Client
 *
 * @package Progressus\Zoovu\Api
 */
class Client
{
    private $client;

    /**
     * Client constructor.
     */
    public function __construct()
    {
        if (! self::hasToken()) {
            throw new \Exception('No token provided!');
        }

        $clientParams = [
            'base_uri' => $this->getClientUrl(),
            'headers' => [
                'Connector-Token' => Options::getApiKey(),
                'Platform-Name' => self::getPlatformName(),
                'Platform-Version' => self::getPlatformVersion(),
                'Extension-Version' => \ZoovuFeeds::VERSION
            ]
        ];

        // If logging enabled et WC Logger
        if (Options::isLoggingEnabled()) {
            $clientParams['handler'] = $this->getErrorHandler();
        }

        $this->client = new \GuzzleHttp\Client($clientParams);
    }

    /**
     * @return HandlerStack
     */
    private function getErrorHandler()
    {
        $stack = HandlerStack::create();

        $stack->push(
            Middleware::log(
                new WC_Logger(),
                new MessageFormatter('{request} - {req_body} - {res_body}')
            )
        );

        return $stack;
    }

    /**
     * API Route base url
     *
     * @return string
     */
    private function getClientUrl()
    {
        return 'https://extensions-api.zoovu.com/';
    }

    /**
     * Upload feed route
     *
     * @param $csv_contents
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function uploadFeed(Feed $feed)
    {
        $request = $this->client->request('POST', 'products/storage/upload', [
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => $feed->getCSVContents(),
                    'filename' => $feed->getGeneratedFileName()
                ]
            ]
        ]);

        return $request;
    }

    /**
     * @param $from
     * @param $to
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateFileName($from, $to)
    {
        return $this->client->request('POST', sprintf(
            'products/storage/%s/rename/%s',
            $from,
            $to
        ));
    }

    /**
     * @param Feed $feed
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function feedUsage(Feed $feed)
    {
        $results = $this->client->request('GET', sprintf(
            'products/storage/%s/overview',
            $feed->getGeneratedFileName()
        ));

        if ($results->getStatusCode() === 200) {
            return json_decode($results->getBody());
        }
    }

    /**
     * Get session token
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getSessionToken()
    {
        $request = $this->client->request('POST', 'users/connectors/sessions');

        if ($request->getStatusCode() === 200) {
            return $request->getBody();
        }
    }

    public function getTrackingScriptTag()
    {
        $request = $this->client->request('GET', 'advisors/sales-tracking-integration-code');

        if ($request->getStatusCode() === 200) {
            return (string) $request->getBody();
        }
    }

    /**
     * Autologin
     *
     * @param $sessionCode
     * @return \Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function autologin($sessionCode)
    {
        $request = $this->client->request('POST', 'users/autologin', [
            'headers' => [
                'Session-Connector-Token' => $sessionCode,
                'Platform-Name' => self::getPlatformName(),
                'Platform-Version' => self::getPlatformVersion(),
                'Extension-Version' => \ZoovuFeeds::VERSION
            ]
        ]);

        if ($request->getStatusCode() === 200) {
            return $request->getBody();
        }
    }

    /**
     * Get Platform version
     *
     * @return string
     */
    public static function getPlatformVersion()
    {
        global $woocommerce;

        return $woocommerce->version;
    }

    /**
     * Get Platform name
     *
     * @return string
     */
    public static function getPlatformName()
    {
        return 'WooCommerce';
    }

    /**
     * Check if token exists
     *
     * @return bool
     */
    public static function hasToken()
    {
        return (bool) Options::getApiKey();
    }

}
