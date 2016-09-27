<?php namespace API;

/* @author  Rachel L Carbone <hello@rachellcarbone.com> */

class ApiLogging {
    
    /* 
     * Path to the log file
     */
    private $defaultLogFile = false;
    
    /*
     * API Config Class Instance
     */
    private $ApiConfig;
    
    /**
     * Logging helper methods for use throughout the API.
     * 
     * $ApiLogging = new ApiLogging( new \API\ApiConfig(), 'api_log' );
     *
     * @param  \API\ApiConfig   $ApiConfig Api Config Helper Method
     * @param  String           $logName optional Name of the default Log File to print to
     */
    function __construct(\API\ApiConfig $ApiConfig, $logName = false) {
        // Get the API config
        $this->ApiConfig = new ApiConfig();
        
        // Name of the log file, either "yourname_log" or "logs"
        $name = (!$logName) ? 'logs' : $logName;
        
        // Example: C:/xampp/htdocs/project-diretory/api/system/logs/v1_16_11_error_log.txt
        $this->defaultLogFile = $this->buildLogFilePath($name);
    }

    /* Build the full system path to the log file with sent name.
     * Attempts to log errors if variables are not set.
     */
    private function buildLogFilePath($name) {
        $loggerSettings = array(
            // System path to project directory ending with '/'
            'systemPath' => '',
            // Path to log containing folder within the project directory ending with '/'
            'dirLogs' => '',
            // API Version to prefix on log file name
            'apiVersion' => ''
        );
        
        $success = true;
        foreach ($loggerSettings as $key => $value) {
            $var = $this->ApiConfig->get($key);

            if ($var) {
                $loggerSettings[$key] = $var;
            } else {
                $this->log("ERROR RETRIEVING LOGGING SETTING <{$key}>", LOG_EMERG);
                $success = false;
            }
        }
        
        return (!$success) ? false :
            "{$loggerSettings['systemPath']}{$loggerSettings['dirLogs']}{$loggerSettings['apiVersion']}_" . date('m_y') . "_{$name}.log";
    }
    
    /* Change a Exception to a string and write it to the log file
     * 
     * @param String $message Text to be logged
     * @param Int $priority Level of priority with which to write the message, based on syslog priority constants
     * @param String $alternateLog Log name to write instead of the default log file.
     */
    public function logException($e, $priority = LOG_EMERG, $alternateLog = 'api_exceptions') {
        // Get the exception as a string
        $text = $this->getExceptionString($e);
        // Write the exception to the log file
        $this->write("{$text}\r\n", $priority, $alternateLog);
    }
    
    private function getExceptionString($e) {
        // If the api is in debug mode return a more verbose message
        return "\r\n" . $e->getMessage() . "\r\n{$e}";
    }
    
    /* Alias for the write function
     * 
     * @param String $message Text to be logged
     * @param Int $priority Level of priority with which to write the message, based on syslog priority constants
     * @param String $alternateLog Log name to write instead of the default log file.
     */
    public function log($message, $priority = LOG_ERR, $alternateLog = false) {
        $this->write($message, $priority, $alternateLog);
    }
    
    /* Attempts to writ message to several log files.
     * 
     * @param String $message Text to be logged
     * @param Int $priority Level of priority with which to write the message, based on syslog priority constants
     * @param String $alternateLog Log name to write instead of the default log file.
     */
    public function write($message, $priority = LOG_ERR, $alternateLog = false) {
        // Make sure the log item is in String format
        $text = (is_array($message)) ? json_encode($message) : $message;

        // If the alternate log file is defined
        $logFile = false;
        if($alternateLog) {
            $logFile = $this->buildLogFilePath($alternateLog);
        }
        // If the alternate log file is valid
        $logFile = ($logFile) ? $logFile : $this->defaultLogFile;

        // Build a formatted error message
        $this->writeToLog($text, $priority, $logFile);
    }

    /* Prepend a timestamp to a line of text and write it to the log file.
     * 
     * SysLog Priority is a combination of the facility and the level. 
     * Possible values are:
     * 
     * LOG_EMERG	system is unusable
     * LOG_ALERT	action must be taken immediately
     * LOG_CRIT	    critical conditions
     * LOG_ERR	    error conditions
     * LOG_WARNING	warning conditions
     * LOG_NOTICE	normal, but significant, condition
     * LOG_INFO	    informational message
     * LOG_DEBUG	debug-level message
    */
    private function writeToLog($text, $priority, $logFile) {
        $version = $this->ApiConfig->get('apiVersion');
        openlog("api_{$version}", LOG_NDELAY, LOG_USER);

        $message = date("m d, Y, G:i:s T") . "    {$text}\r\n";

        // Send it to the System Log
        syslog(LOG_ERR, $message);

        if($logFile) {
            // Write to the log file
            try {
                // Append the log item (string) to the log file 
                return file_put_contents($logFile, $message, FILE_APPEND);
            } catch (\Exception $e) {
                syslog(LOG_ERR, $this->getExceptionString($e));
                return false;
            }
        } else {
            // Send it to the System Log
            syslog(LOG_ERR, "Invalid log file: {$logFile}");
            return false;
        }
    }

    private function log_toFile($text, $priority, $logFile) {
        return error_log($text, 0);
    }

    private function log_toEmail($text, $priority, $logFile) {
        return error_log($text, 1);
    }

    private function log_toSystem($text, $priority, $logFile) {
        return error_log($text, 3);
    }


}
