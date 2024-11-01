<?php

namespace Progressus\Zoovu\Api\Logger;

use Psr\Log\LoggerInterface;

/**
 * Class WC_Logger compilable with PSR7
 *
 * @package Progressus\Zoovu\Api
 */
class WC_Logger extends \WC_Logger implements LoggerInterface
{
    /**
     * Adds a warning level message.
     *
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things that are not
     * necessarily wrong.
     *
     * @see WC_Logger::log
     *
     * @param string $message Message to log.
     * @param array  $context Log context.
     */
    public function warning( $message, $context = array() ) {
        $this->log( \WC_Log_Levels::WARNING, $message );
    }

    /**
     * Add a log entry.
     *
     * @param string $level One of the following:
     *     'emergency': System is unusable.
     *     'alert': Action must be taken immediately.
     *     'critical': Critical conditions.
     *     'error': Error conditions.
     *     'warning': Warning conditions.
     *     'notice': Normal but significant condition.
     *     'info': Informational messages.
     *     'debug': Debug-level messages.
     * @param string $message Log message.
     * @param array  $context Optional. Additional information for log handlers.
     */
    public function log( $level, $message, $context = array('source' => 'zoovu-for-woocommerce') ) {
        parent::log($level, $message, $context);
    }

    /**
     * Adds an error level message.
     *
     * Runtime errors that do not require immediate action but should typically be logged
     * and monitored.
     *
     * @see WC_Logger::log
     *
     * @param string $message Message to log.
     * @param array  $context Log context.
     */
    public function error( $message, $context = array('source' => 'zoovu-for-woocommerce') ) {
        parent::log(\WC_Log_Levels::ERROR, $message, $context );
    }
}
