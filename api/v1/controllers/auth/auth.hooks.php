<?php namespace API;
require_once dirname(__FILE__) . '/auth.data.php';

class AuthHooks {
    
    static function signupHook($app, $results) {
        self::hookCallHotSalsa($app, $results);
    }    
    
    private function hookCallHotSalsa($app, $results) {
        $vars = self::data_hookConfigVars();
        
        if(!isset($vars['HOT_SALSA_ENABLED']) || ($vars['HOT_SALSA_ENABLED'] !== 'true' || $vars['HOT_SALSA_ENABLED'] !== '1')) {
            return;
        }
        
        if(!isset($vars['HOT_SALSA_URL']) || 
            !isset($vars['HOT_SALSA_APP_VERSION']) || 
            !isset($vars['HOT_SALSA_URL_CODE']) || 
            !isset($vars['HOT_SALSA_AUTH_KEY']) || 
            !isset($vars['HOT_SALSA_OS']) || 
            !isset($vars['HOT_SALSA_PACKAGE_CODE'])) {
            
            return;
        }
        
        $params = $results;
            
        $HOT_SALSA_URL = $vars['HOT_SALSA_URL'];
        $HOT_SALSA_APP_VERSION = $vars['HOT_SALSA_APP_VERSION'];
        $HOT_SALSA_URL_CODE = $vars['HOT_SALSA_URL_CODE'];
        $HOT_SALSA_AUTH_KEY = $vars['HOT_SALSA_AUTH_KEY'];
        $HOT_SALSA_OS = $vars['HOT_SALSA_OS'];
        $HOT_SALSA_PACKAGE_CODE = $vars['HOT_SALSA_PACKAGE_CODE'];

        $url = "{$HOT_SALSA_URL}?appVersion={$HOT_SALSA_APP_VERSION}&code={$HOT_SALSA_URL_CODE}&authKey={$HOT_SALSA_AUTH_KEY}&os={$HOT_SALSA_OS}&packageCode={$HOT_SALSA_PACKAGE_CODE}";

        // create curl resource 
        $ch = curl_init(); 

        // set url 
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_POST, true); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params); 

        //return the transfer as a string 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

        // $output contains the output string 
        $output = curl_exec($ch); 

        // close curl resource to free up system resources 
        curl_close($ch);
        
    }
    
    
    private function data_hookConfigVars() {
        return DBConn::selectAll("SELECT `name`, `value`, `disabled` FROM " . DBConn::prefix() . "system_config "
                . "WHERE name LIKE 'HOT_SALSA_%' AND `disabled`= 0;");
    }
}