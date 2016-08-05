<?php namespace API;

/* @author  Rachel L Carbone <hello@rachellcarbone.com> */

class ApiDBConn {
    
    /*
     * PDO Instance
     */
    private $pdo;
    
    /*
     * API Config Class Instance
     */
    private $ApiConfig;
    
    /*
     * System Logger Instance
     */
    private $ApiLogging;
    
    /*
     * Email Log File Name String
     */
    private $logFileName = 'pdo_exception';
    
    /*
     * Database Table Prefix
     */
    private $dbTablePrefix;
    
    /**
     * API Database Connection Handler to manage the use of a PDO Database connection
     * to be used for all database communication throughout the API.
     * 
     * $ApiDBConn = new ApiDBConn( new \API\ApiConfig(), new \API\ApiLogging() );
     *
     * @param  \API\ApiConfig       $ApiConfig Api Config Helper Method
     * @param  \API\ApiLogging      $ApiLogging  System Logging Helper Method
     */
    function __construct(\API\ApiConfig $ApiConfig, \API\ApiLogging $ApiLogging) {

        $this->ApiConfig = $ApiConfig;
        $this->ApiLogging = $ApiLogging;
        // Get the API config
        $this->dbTablePrefix = $ApiConfig->get('dbTablePrefix');
    }
    
    /*
     * Retrieve the database table prefix set in the API Config file.
     *
     * @return String
     */
    public function prefix() {
        return $this->dbTablePrefix;
    }
    
    /*
     * Create a PDO connection if one does not exist.
     *
     * @return PDO Represents a connection between PHP and a database server.
     */
    private function connect() {
        // If a PDO instance does not already exist
        if(!$this->pdo) {
            // Get the system configuration file
            $c = $this->ApiConfig->get();

            // Create a set of PDO options
            $options = array(
                /*
                 * Persistent connections are not closed at the end of the script, but 
                 * are cached and re-used when another script requests a connection 
                 * using the same credentials. The persistent connection cache allows 
                 * you to avoid the overhead of establishing a new connection every 
                 * time a script needs to talk to a database, resulting in a faster web 
                 * application.
                 * http://php.net/manual/en/pdo.connections.php
                 */
                \PDO::ATTR_PERSISTENT => true,
                // Error Mode: Throw Exceptions
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            );
            
            // 
            $invocation = ($c['dbHost']) ? "host={$c['dbHost']}" : "unix_socket={$c['dbUnixSocket']}";

            try {
                $this->pdo = new \PDO("mysql:{$invocation};dbname={$c['db']}", $c["dbUser"], $c["dbPass"], $options);
            } catch (\PDOException $e) {
                $this->logError("Could not connect to the DB \"mysql:{$invocation};dbname={$c['db']}\" from 'api.dbconn'.");
                // If we cant connect to the database die
                die('Could not connect to the database:<br/>' . $e);
            }
        }
        
        // Return the cached instance of PDO
        return $this->pdo;
    }
    
    /*
     * Log the last PDO error
     */
    private function logPDOError($pdo) {
        // Write the error arry to the log file
        $this->ApiLogging->write($pdo->errorInfo(), LOG_ERR, $this->logFileName);
    }
    
    /*
     * Log the last PDO error
     */
    private function logError($error) {
        // Write the error arry to the log file
        $this->ApiLogging->write($error, LOG_ERR, $this->logFileName);
    }
    
    /* 
     * Get select limit string for MySQL
     */
    public function getLimit($limit = 20, $page = 1) {
        $p = (is_numeric($page) && intval($page) > 1) ? intval($page) : 1;
        $l = (is_numeric($limit) && intval($limit) > 0) ? intval($limit) : 20;
        $offset = ($p - 1) * $l;
        
        return "LIMIT {$offset}, {$l}";
    }
    
    /*
     * Preform insert opperation with PDO connection.
     * 
     * @param string A valid SQL statement template for the target database server.
     * @param array An array of values with as many elements as there are bound parameters in the SQL statement being executed.
     * 
     * @return int|bool New row ID if success, FALSE if insert fails.
     */
    public function insert($query, $data) {
        $pdo = $this->connect();
        try {
            $q = $pdo->prepare($query);
            $e = $q->execute($data);
            if ($e === false) {
                return false;
            } else {
                $id = $pdo->lastInsertId();
                return ($id === false) ? false : $id;
            }
        } catch (\PDOException $e) {
            $this->logPDOError($q);
            return false;
        }
    }
    
    /*
     * Preform update opperation with PDO connection.
     * 
     * @param string A valid SQL statement template for the target database server.
     * @param array|optional An array of values with as many elements as there  are bound parameters in the SQL statement being executed.
     * 
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function update($query, $data = array()) {
        $pdo = $this->connect();
        try {
            $q = $pdo->prepare($query);            
            return $q->execute($data);
        } catch (\PDOException $e) {
            $this->logPDOError($q);
            return false;
        }
    }
    
    /*
     * Preform fetchAll opperation with on a prepared PDO query.
     * 
     * @param string A valid SQL statement template for the target database server.
     * @param array|optional An array of values with as many elements as there 
     * are bound parameters in the SQL statement being executed.
     * @param enum This value must be one of the \PDO::FETCH_* constants, 
     * defaulting to \PDO::FETCH_OBJ (php.net/manual/en/pdostatement.fetch.php)
     * 
     * @return array|bool returns an array containing all of the remaining rows in the 
     * result set. The array represents each row as either an array of column 
     * values or an object with properties corresponding to each column name. 
     * An empty array is returned if there are zero results to fetch, 
     * or FALSE on failure.
     */
    public function selectAll($query, $data = array(), $style = \PDO::FETCH_OBJ) {
        $pdo = $this->connect();
        
        try {
            $q = $pdo->prepare($query);
            $q->execute($data);
            return $q->fetchAll($style);
        } catch (\PDOException $e) {
            $this->logPDOError($q);
            return false;
        }
    }
    
    /*
     * Preform fetchAll opperation with on a prepared PDO query.
     * 
     * @param string A valid SQL statement template for the target database server.
     * @param array|optional An array of values with as many elements as there 
     * are bound parameters in the SQL statement being executed.
     * @param enum This value must be one of the \PDO::FETCH_* constants, 
     * defaulting to \PDO::FETCH_OBJ (php.net/manual/en/pdostatement.fetch.php)
     * 
     * @return array|bool returns a single column in the next row of a result set,
     * or FALSE if no results were found.
     */
    public function selectOne($query, $data = array(), $style = \PDO::FETCH_OBJ) {
        $pdo = $this->connect();
        
        try {
            $q = $pdo->prepare($query);
            $q->execute($data);
            return $q->fetch($style);
        } catch (\PDOException $e) {
            $this->logPDOError($q);
            return false;
        }
    }
    
    /*
     * Preform fetchAll opperation with on a prepared PDO query.
     * If only one value is found, just the value is returned.
     * 
     * @param string A valid SQL statement template for the target database server.
     * @param array|optional An array of values with as many elements as there 
     * are bound parameters in the SQL statement being executed.
     * @param enum This value must be one of the \PDO::FETCH_* constants, 
     * defaulting to \PDO::FETCH_OBJ (php.net/manual/en/pdostatement.fetch.php)
     * 
     * @return array|bool returns a single column in the next row of a result set,
     * or FALSE if no results were found. If only one value is found, just the 
     * value is returned.
     */
    public function selectColumn($query, $data = array()) {
        $pdo = $this->connect();
        try {
            $q = $pdo->prepare($query);            
            $q->execute($data);
            $found = $q->fetchAll(\PDO::FETCH_COLUMN);
            return ($found && isset($found[0]) && count($found) === 1) ? $found[0] : $found;
        } catch (\PDOException $e) {
            $this->logPDOError($q);
            return false;
        }
    }

    /*
     * Preform delete opperation with on a prepared PDO query.
     * 
     * @param string A valid SQL statement template for the target database server.
     * @param array An array of values with as many elements as there 
     * are bound parameters in the SQL statement being executed.
     * 
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function delete($query, $data = array()) {
        $pdo = $this->connect();
        try {
            $q = $pdo->prepare($query);            
            $q->execute($data);
            return $q->rowCount();
        } catch (\PDOException $e) {
            $this->logPDOError($q);
            return false;
        }
    }
    
    /*
     * Prepares a statement for execution and returns a statement object.
     * 
     * @param string A valid SQL statement template for the target database server.
     * 
     * @return PDOStatement PDO statement object to be executed later.
     */
    public function preparedQuery($query) {
        $pdo = $this->connect();
        try {
            $q = $pdo->prepare($query);
            return $q;
        } catch (\PDOException $e) {
            $this->logPDOError($q);
            return false;
        }
    }
    
    /*
     * Prepares and exicutes a query.
     * 
     * @param string A valid SQL statement template for the target database server.
     * @param array|optional An array of values with as many elements as there 
     * are bound parameters in the SQL statement being executed.
     * 
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function executeQuery($query, $data = array()) {
        $pdo = $this->connect();
        try {
            $q = $pdo->prepare($query);            
            $q->execute($data);
            return $q;
        } catch (\PDOException $e) {
            $this->logPDOError($q);
            return false;
        }
    }
}
