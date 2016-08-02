<?php namespace API;

/* @author  Rachel L Carbone <hello@rachellcarbone.com> */

class ApiConfig {

    /* 
     * Array of key => value pairs
     */
    private $apiConfig = false;

    /**
     * Api Config variables Handler to manage the use of critical system variables.
     */
    public function __construct() {
        /* Select and set the api config variables */
        $this->setApiConfig();
    }

    /**
     * This method works two ways:
     * 
     * One - Select a api config variable by its name, if the variable exists its value (String)
     *      will be returned, or if the variable doesn't exist false (bool) will be returned. 
     *
     * Two - Return the api config variable key => value pairs (array), or if there was an 
     *      error selecting the variables on init, return false (bool).
     *
     * $allVariablesArray = $SystemVariables->get();
     * $oneVariableValue = $SystemVariables->get('UNIQUE_VARIABLE_IDENTIFIER');
     *
     * @param  String   $variableName optional Name of the api config variable requested.
     *
     * @return Mixed
     */
    public function get($variableName = false) {        
        /* If a variable name was sent to the get function, 
         * try to select just that variable. */
        if ($this->apiConfig && !$variableName) {
            return $this->apiConfig;
        } else if ($this->apiConfig && $variableName && isset($this->apiConfig[$variableName])) {
            return $this->apiConfig[$variableName];
        } else if ($this->apiConfig && $variableName && !isset($this->apiConfig[$variableName])) {
            // Log the failure to set the api config variables
            $this->log("Requested unset config variable: " . json_encode($variableName));
            return false;
        } else {
            // Log the failure to set the api config variables
            $this->log("Config variable error - Requested Variable: " . json_encode($variableName) . " Variable Array: " . json_encode($this->apiConfig));
            return false;
        }
    }

    /**
     * Manually run the setApiConfig method and refresh the saved apiConfig.
     *
     * $allVariablesArray = $ApiConfig->refresh();
     *
     * @return Array
     */
    public function refresh() {        
        /* Run the api config variable selection manually. */
        return $this->setApiConfig();
    }

    /**
     * Set the apiConfig array based on the current server address.
     *
     * @return void
     */
    private function setApiConfig() {
        $default = array(
            'repoTitle' => 'Angular Seed Slim PHP API',
            'codeRepoUrl' => 'https://gitlab.com/rachellcarbone/angular-seed',
            'author' => 'Rachel L Carbone <hello@rachellcarbone.com>',
            'authorWebsite' => 'http://www.rachellcarbone.com',
            'systemAdminEmail' => 'rachellcarbone+99@gmail.com',
            'apiVersion' => 'v1',
            'debugMode' => true,
            'dbHost' => 'localhost',
            'dbUnixSocket' => false,
            'db' => 'seed',
            'dbUser' => 'angular_seed',
            'dbPass' => 'angular_seed',
            'dbTablePrefix' => 'as_',
            'systemPath' => 'C:/xampp/htdocs/webdev/angular-seed/',
            'dirPublic' => 'public/',
            'dirSystem' => 'api/system/',
            'dirLogs' => 'api/system/logs/'
        );
        
        if($_SERVER['HTTP_HOST'] === 'api.seed.dev') {
            // Localhost
            $this->apiConfig = $default;
        } else {
            // Log the failure to set the api config variables
            $this->log("Could not set the api config variables: " . json_encode($_SERVER));
            // Ensure this is false if it failed
            $this->apiConfig = false;
	    }
    }

    /**
     * Helper function to log config errors.
     *
     * @return void
     */
    private function log($message) {
        syslog(LOG_EMERG, $message);
        error_log($message, 0);
    }
}