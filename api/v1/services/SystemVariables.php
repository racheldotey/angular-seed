<?php namespace API;

/* @author  Rachel L Carbone <hello@rachellcarbone.com> */

class SystemVariables {

    /*
     * System Database Helper Instance
     */
    protected $DBConn;
    
    /*
     * Database Table Prefix
     */
    private $dbTablePrefix;

    /*
     * System Logger Instance
     */
    private $ApiLogging;
    
    /*
     * Email Log File Name String
     */
    private $logFileName = 'system_variables_log';

    /* 
     * Array of key => value pairs 
     */
    private $systemVariables;

    /**
     * System Variables Handler to manage the use of variables stored in the database
     * to be used throught the API.
     * 
     * $SystemVars = new SystemVariables( new \API\ApiDBConn(), new \API\ApiLogging() );
     *
     * @param  \API\ApiDBConn   $dbConn  Database Connection Helper Method
     * @param  \API\ApiLogging  $ApiLogging optional System Logging Helper Method
     */
    public function __construct(\API\ApiDBConn $ApiDBConn, \API\ApiLogging $ApiLogging) {
        $this->DBConn = $ApiDBConn;
        $this->dbTablePrefix = $ApiDBConn->prefix();

        $this->ApiLogging = $ApiLogging;

        /* Select and set the System Variables */
        $this->setSystemVariables();
    }

    /**
     * This method works two ways:
     * 
     * One - Select a system variable by its name, is the variable exists its value (mixed)
     *      will be returned, or if the variable doesn't exist false (bool) will be returned. 
     *
     * Two - Return the system variable key => value pairs (array), or if there was an 
     *      error selecting the variables on init, return false (bool).
     *
     * $allVariablesArray = $SystemVariables->get();
     * $oneVariableValue = $SystemVariables->get('UNIQUE_VARIABLE_IDENTIFIER');
     *
     * @param  String   $variableName optional Name of the system variable requested.
     *
     * @return Mixed
     */
    public function get($variableName = false) {        
        /* If a variable name was sent to the get function, 
         * try to select just that variable. */
        if ($this->systemVariables && $variableName !== false) {
            return (isset($this->systemVariables[$variableName])) ? $this->systemVariables[$variableName] : false;
        }
        return $this->systemVariables;
    }

    /**
     * Manually run the setSystemVariables method and refresh the saved systemVariables.
     *
     * $allVariablesArray = $SystemVariables->refresh();
     *
     * @return Array
     */
    public function refresh() {        
        /* Run the system variable selection manually. */
        return $this->setSystemVariables();
    }

    /**
     * Select enabled system variables from the database table.
     *
     * @return Array
     */
    private function setSystemVariables() {
        /* Select the system variables from the database */
        $qVariables = $this->DBConn->executeQuery("SELECT name, value FROM {$this->dbTablePrefix}system_config WHERE disabled = 0;");
        
        /* Format the variables into an associatiave array for easier lookup */
        $variables = Array();
        while($var = $qVariables->fetch(\PDO::FETCH_OBJ)) {  
            $variables[$var->name] = $var->value;
        }
        
        /* If the system variables wern't set send error to the system logger */
        if(!$variables || !is_array($variables) || count($variables) <= 0) {
            // Log the failure to set the system variables
            $this->log("Could not select system variables from the database.");
            // Ensure this is false if it failed
            $this->systemVariables = false;
        } else {
            /* Save the system variables. */
            $this->systemVariables = $variables;
        }

        /* Return system variables. */
        return $this->systemVariables;
    }

    /**
     * Helper function to log to logger if it's set or syslog if it isn't.
     *
     * @return void
     */
    private function log($message) {
        if($this->ApiLogging) {
            $this->ApiLogging->log($message, LOG_ERR, $this->logFileName);
        } else {
            syslog(LOG_ERR, $message);
        }
    }
}