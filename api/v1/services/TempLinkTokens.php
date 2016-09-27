<?php namespace API;

/* @author  Rachel L Carbone <hello@rachellcarbone.com> */

class TempLinkTokens {

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
     * Temporary Link Tokens Handler to manage the generation of tokens (random hash).
     * Tokens are stored with an expiration timestamp in table `tokens_temp_links`.
     * When added to a link variable these tokens can be used to create expiring links
     * (temporary access) to pages withing the system. (seed.dev/reset-password/*****)
     * 
     * $TempLinkTokens = new TempLinkTokens( new \API\ApiDBConn(), new \API\SystemVariables(), new \API\ApiLogging() );
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

    /**
     * Validate that a token and password pair exist and are not expired.
     *
     * @param String $token Link token 
     * @param String $password Additional string to use as a password
     *
     * @return Mixed Boolean
     */
    public function validateToken($linkToken, $password = false) {
        $token = $this->selectTempToken($linkToken);

        if($token && $password) {
            return password_verify($password, $token->password);
        } else {
            return ($token);
        }
    }

    /**
     * Generates a 32 character long token and saves it with an expiration timestamp,
     * optional password hash used for validation, and optional description string
     * that may or may not be used by the system.
     *
     * @param String $password Optional string to use as a password
     * @param String $timeoutInHours Optional timeout duration in hours for the token expiration
     * @param String $optionalDesc Optional token description (ex: 'password-reset', 'confirm-email')
     *
     * @return Mixed false or String
     */
    public function generateToken($userId = false, $password = false, $timeoutInHours = 24, $optionalDesc = '') {
        // md5 Calculate the md5 hash of a string
        // String (32) 5d41402abc4b2a76b9719d911017c592 
        $token = hash('md5', uniqid());
        $saved = $this->insertTempToken(array(
            ':user_id' => (!$userId) ? NULL : $userId, 
            ':token' => $token, 
            ':password' => (!$password) ? NULL : password_hash($password, PASSWORD_DEFAULT), 
            ':desc' => $optionalDesc, 
            ':expires' => date('Y-m-d H:i:s', time() + ($timeoutInHours * 60 * 60))
        ));

        if(!$saved) {
            $this->ApiLogging->write('Could not generate link token.');
            return false;
        } else {
            return $token;
        }
    }

    /**
     * Selects basic user data if a user id is associated with the link token.
     *
     * @param String $token Link token 
     *
     * @return Mixed false or associative array
     */
    public function getUserByToken($linkToken) {
        return $this->selectUserByToken($linkToken);
    }

    /**
     * Delete a token.
     *
     * @param String $token Md5 hash token to delete.
     */
    public function deleteToken($token) {
        return $this->deleteTempToken($token);
    }

    private function insertTempToken($validToken) {
        return $this->DBConn->insert("INSERT INTO {$this->dbTablePrefix}tokens_temp_links(user_id, token, password, `desc`, expires) "
                . "VALUES (:user_id, :token, :password, :desc, :expires);", $validToken);
    }

    private function selectTempToken($token) {
        return $this->DBConn->selectOne("SELECT token, password, `desc`, created, expires FROM {$this->dbTablePrefix}tokens_temp_links "
                . "WHERE token = :token AND expires > NOW() LIMIT 1;", array(':token' => $token));
    }

    private function selectUserByToken($token) {
        return $this->DBConn->selectOne("SELECT u.id AS userId, u.name_first AS nameFirst, u.name_last AS nameLast, u.email "
                . "FROM {$this->dbTablePrefix}tokens_temp_links AS l "
                . "JOIN {$this->dbTablePrefix}users AS u ON l.user_id = u.id "
                . "WHERE token = :token LIMIT 1;", array(':token' => $token));
    }

    private function deleteTempToken($token) {
        return $this->DBConn->insert("DELETE FROM {$this->dbTablePrefix}tokens_temp_links "
                . "WHERE token = :token LIMIT 1;", array(':token' => $token));
    }

}