<?php namespace API;

/* @author  Rachel L Carbone <hello@rachellcarbone.com> */

use \Respect\Validation\Validator as v;
v::with('API\\Validation\\Rules');

class AuthSessionGenerator {

    /*
     * System Variables Class Instance
     */
    private $SystemVariables;

    /*
     * System Logger Instance
     */
    private $ApiLogging;
    
    /*
     * System Database Helper Instance
     */
    private $DBConn;
    
    /*
     * Database Table Prefix
     */
    private $dbTablePrefix;


    /**
     * System Variables Handler to manage the use of variables stored in the database
     * to be used throught the API.
     * 
     * $AuthSessionGenerator = new AuthSessionGenerator( new \API\ApiDBConn(), new \API\SystemVariables(), new \API\ApiLogging() );
     *
     * @param  \API\ApiDBConn       $dbConn  Database Connection Helper Method
     * @param  \API\SystemVariables $SystemVariables  Database System Variables Helper Method
     * @param  \API\ApiLogging      $ApiLogging  System Logging Helper Method
     */
    public function __construct(\API\ApiDBConn $ApiDBConn, \API\SystemVariables $SystemVariables, \API\ApiLogging $ApiLogging) {
        $this->DBConn = $ApiDBConn;
        $this->dbTablePrefix = $ApiDBConn->prefix();
        
        $this->SystemVariables = $SystemVariables;
        $this->ApiLogging = $ApiLogging;
    }
    
    public function createAuthToken($post, $userId) {
        // IF the remember flag was sent
        if(v::key('remember', v::POSTBooleanTrue())->validate($post)) {
            $hours = intval($this->SystemVariables->get('AUTH_COOKIE_TIMEOUT_HOURS_REMEMBER'));
            $timeoutInHours = (!$hours) ? 24 : $hours;
        } else {
            $hours = intval($this->SystemVariables->get('AUTH_COOKIE_TIMEOUT_HOURS'));
            $timeoutInHours = (!$hours) ? 3 : $hours;
        }

        $token = array(
            'apiKey' => hash('sha512', uniqid()),
            'apiToken' => hash('sha512', uniqid()),
            'sessionLifeHours' => $timeoutInHours
        );

        // Save token to create session
        $saved = $this->insertAuthToken(array(
            ':user_id' => $userId,
            ':identifier' => $token['apiKey'],
            ':token' => password_hash($token['apiToken'], PASSWORD_DEFAULT),
            ':ip_address' => $_SERVER['REMOTE_ADDR'],
            ':user_agent' => (!isset($_SERVER['HTTP_USER_AGENT'])) ? 'User Agent Not Set' : $_SERVER['HTTP_USER_AGENT'],
            ':expires' => date('Y-m-d H:i:s', time() + ($token['sessionLifeHours'] * 60 * 60))
        ));

        // Save login statistical info
        $this->insertLoginLocation(array(
            ':user_id' => $userId,
            ':ip_address' => $_SERVER['REMOTE_ADDR'],
            ':user_agent' => (!isset($_SERVER['HTTP_USER_AGENT'])) ? 'User Agent Not Set' : $_SERVER['HTTP_USER_AGENT']
        ));

        return ($saved) ? $token : false;
    }
    
    public function insertAuthToken($validToken) {
        return $this->DBConn->insert("INSERT INTO {$this->dbTablePrefix}tokens_auth(identifier, token, user_id, expires, ip_address, user_agent) "
                . "VALUES (:identifier, :token, :user_id, :expires, :ip_address, :user_agent);", $validToken);
    }
    
    public function insertLoginLocation($validLog) {
        return $this->DBConn->insert("INSERT INTO {$this->dbTablePrefix}logs_login_location(user_id, ip_address, user_agent) "
                . "VALUES (:user_id, :ip_address, :user_agent);", $validLog);
    }
}