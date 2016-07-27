<?php namespace API;

use \Respect\Validation\Validator as v;
v::with('API\\Validation\\Rules');

class AuthSessionGenerator extends RouteDBController {

    private $SystemVariables;

    private $ApiLogging;

    public function __construct(\Interop\Container\ContainerInterface $slimContainer) {
        parent:: __construct($slimContainer->get('DBConn'));

        $this->SystemVariables = $slimContainer->get('SystemVariables');
        $this->ApiLogging = $slimContainer->get('ApiLogging');
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
        return $this->DBConn->insert("INSERT INTO {$this->prefix}tokens_auth(identifier, token, user_id, expires, ip_address, user_agent) "
                . "VALUES (:identifier, :token, :user_id, :expires, :ip_address, :user_agent);", $validToken);
    }
    
    public function insertLoginLocation($validLog) {
        return $this->DBConn->insert("INSERT INTO {$this->prefix}logs_login_location(user_id, ip_address, user_agent) "
                . "VALUES (:user_id, :ip_address, :user_agent);", $validLog);
    }
}