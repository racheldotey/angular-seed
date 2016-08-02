<?php

$debugMode = true;

// Only display errors when debug mode is on
ini_set('display_errors', $debugMode);
ini_set('display_startup_errors', $debugMode);

// Require dependencies after setting errors display incase 
// there are errors with any dependencies.
require_once dirname(dirname(__FILE__)) . '/services/ApiConfig.php';
require_once dirname(dirname(__FILE__)) . '/services/ApiLogging.php';


class PhpErrorHandling {

    /*
     * System Logger Instance
     */
    private $ApiLogging;
    
    /**
     * Logging helper methods for use throughout the API.
     * 
     * $ApiLogging = new ApiLogging( new \API\ApiConfig(), 'api_log' );
     *
     * @param  \API\ApiLogging  $ApiApiLogging Api Logging Helper Method
     * @param  String           $logName optional Name of the default Log File to print to
     */
    function __construct(\API\ApiLogging $ApiLogging) {
        $this->ApiLogging = $ApiLogging;
    }
    
    /* 
     * PHP Exception Handeling
     * http://php.net/manual/en/function.set-exception-handler.php
     */
    public function apiExceptionHandler($e) {
        $this->ApiLogging->logException($e, LOG_CRIT, 'php_exception');
    }

    /* 
     * PHP Error Handeling
     * http://php.net/manual/en/function.set-error-handler.php
     */
    public function apiErrorHandler($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            // This error code is not included in error_reporting
            return;
        }

        /* Log the message instead of printing */
        switch ($errno) {
            case E_ERROR:
            case E_USER_ERROR:
                $this->ApiLogging->write("PHP ERROR: [{$errno}] {$errstr}\r\n"
                    . "    Line {$errline} in file {$errfile},\r\n"
                    . "    PHP " . PHP_VERSION . " (" . PHP_OS . ")\r\n"
                    . "    Aborting...\r\n", LOG_ERR);
                exit(1);
                break;

            case E_WARNING:
            case E_USER_WARNING:
                $this->ApiLogging->write("PHP WARNING: [{$errno}] {$errstr}\r\n"
                    . "    Line {$errline} in file {$errfile},\r\n", LOG_WARNING);
                break;

            case E_NOTICE:
            case E_USER_NOTICE:
                $this->ApiLogging->write("PHP NOTICE: [{$errno}] {$errstr}\r\n"
                    . "    Line {$errline} in file {$errfile},\r\n", LOG_NOTICE);
                break;

            default:
                $this->ApiLogging->write("UNKNOWN PHP ERROR: [{$errno}] {$errstr}\r\n"
                    . "    Line {$errline} in file {$errfile},\r\n", LOG_INFO);
                break;
        }

        /* If the function returns FALSE then the normal error handler continues.
         * TRUE - If we are not in debug mode
         * FALSE - If we are in debug mode */
        return false;// (!$this->debugMode);
    }
}

$PhpErrorHandling = new PhpErrorHandling(new \API\ApiLogging(new \API\ApiConfig(), 'php_error'));

// http://php.net/manual/en/function.set-error-handler.php
set_error_handler(array($PhpErrorHandling, 'apiErrorHandler'));

// http://php.net/manual/en/function.set-exception-handler.php
set_exception_handler(array($PhpErrorHandling, 'apiExceptionHandler'));
