<?php namespace API;
 require_once dirname(dirname(__FILE__)) . '/services/api.dbconn.php';

class APIConfig {
    static $dbConfig = false;
    static $config = false;

    static function setAPIConfig() {
        $default = array(
            'apiVersion' => 'v1',
            'debugMode' => true,
            'dbHost' => 'localhost',
            'dbUnixSocket' => false,
            'db' => 'angular_seed',
            'dbUser' => 'angular_seed',
            'dbPass' => 'angular_seed',
            'dbTablePrefix' => 'as_',
            'systemPath' => 'C:/xampp/htdocs/webdev/angular-seed/',
            'dirPublic' => 'public/',
            'dirSystem' => 'api/system/',
            'dirLogs' => 'api/system/logs/'
        );

        // PROD Config
        $prod = array_merge($default, array(
            'dbHost' => false,
            'dbUnixSocket' => '/cloudsql/triviajoint-prod2:triviajoint-prod',
            'db' => 'tj_prod_db',
            'dbUser' => 'root',
            'dbPass' => '',
            'dbTablePrefix' => 'trv_',
            'debugMode' => false,
            'systemPath' => dirname(__FILE__)
        ));

        // QA Config
        $qa = array_merge($default, array(
            'dbHost' => false,
            'dbUnixSocket' => '/cloudsql/triviajoint-qa2:triviajoint-qa',
            'db' => 'tj_qa_db',
            'dbUser' => 'root',
            'dbPass' => '',
            'dbTablePrefix' => 'trv_',
            'debugMode' => false,
            'systemPath' => dirname(__FILE__)
        ));
        
        if($_SERVER['HTTP_HOST'] === 'api.seed.dev') {
            // Localhost
            self::$config = $default;
        } else if($_SERVER['HTTP_HOST'] === 'api-dot-triviajoint-qa2.appspot.com') {
            // QA on Google Cloud
            self::$config = $qa;
        }  else if($_SERVER['HTTP_HOST'] === 'api-dot-triviajoint-prod2.appspot.com' || $_SERVER['HTTP_HOST'] === 'app.triviajoint.com') {
            // QA on Google Cloud
            self::$config = $prod;
        } else {
            self::$config = false;
	}
        
        if(self::$config !== false) {
            $dbConfig = self::selectSystemVariables();
            self::$config = array_merge(self::$config, $dbConfig);
        }
    }

    static function get($opt = false) {
        if(!self::$config) {
            self::setAPIConfig();
        }
        
        if ($opt !== false) {
            if(isset(self::$config[$opt])) {
                
                return self::$config[$opt];
            } else {
                return false;
            }
        }
        return self::$config;
    }

    private static function selectSystemVariables() {
        $qDBConfig = DBConn::executeQuery("SELECT name, value FROM " . DBConn::prefix() . "system_config WHERE disabled = 0;");
        
        $dbConfig = Array();
        while($var = $qDBConfig->fetch(\PDO::FETCH_OBJ)) {  
            $dbConfig[$var->name] = $var->value;
        }
        self::$dbConfig = $dbConfig;
        
        return self::$dbConfig;
    }
}