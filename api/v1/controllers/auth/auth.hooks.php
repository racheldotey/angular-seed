<?php namespace API;
require_once dirname(__FILE__) . '/auth.data.php';

class AuthHooks {
    
    static function signup($app, $apiResponse) {
        self::data_deleteStupidUser($apiResponse['user']->id);
        self::hookCallHotSalsa($app, $apiResponse);
    }    
    
    private function hookCallHotSalsa($app, $apiResponse) {
        $vars = self::data_hookConfigVars('HOT_SALSA_');
        
        if(!isset($vars['HOT_SALSA_ENABLED']) || ($vars['HOT_SALSA_ENABLED'] !== 'true' && $vars['HOT_SALSA_ENABLED'] !== '1')) {
            return;
        }
        
        if(!isset($vars['HOT_SALSA_URL']) || 
            !isset($vars['HOT_SALSA_APP_VERSION']) || 
            !isset($vars['HOT_SALSA_URL_CODE']) || 
            !isset($vars['HOT_SALSA_AUTH_KEY']) || 
            !isset($vars['HOT_SALSA_OS']) || 
            !isset($vars['HOT_SALSA_PACKAGE_CODE'])) {
            
            $logData = array(
                ':user_id' => $apiResponse['user']->id,
                ':salsa_call_status' => "Could not attempt call. The Hot Salsa signup hook is enabled but a system variable is disabled or missing.",
                ':salsa_error_message' => json_encode($vars)
            );
            DBConn::insert("INSERT INTO " . DBConn::prefix() . "log_hot_salsa_signup(user_id, salsa_call_status, salsa_error_message) "
                    . "VALUES (:user_id, :salsa_call_status, :salsa_error_message);", $logData);
            return;
        }
        
        // Get Post Data
        $post = $app->request->post();
        $params = array(
            'email' => $post['email'],
            'nameFirst' => $post['nameFirst'],
            'nameLast' => $post['nameLast']
        );
        // If it was standard signup
        if(isset($post['password'])) {
            $params['password'] = password_hash($post['password'], PASSWORD_DEFAULT);
        }
        // If it was facebook signup
        if(isset($post['facebookId'])) {
            $params['facebookId'] = $post['facebookId'];
        }
            
        $HOT_SALSA_URL = $vars['HOT_SALSA_URL'];
        $HOT_SALSA_APP_VERSION = $vars['HOT_SALSA_APP_VERSION'];
        $HOT_SALSA_URL_CODE = $vars['HOT_SALSA_URL_CODE'];
        $HOT_SALSA_AUTH_KEY = $vars['HOT_SALSA_AUTH_KEY'];
        $HOT_SALSA_OS = $vars['HOT_SALSA_OS'];
        $HOT_SALSA_PACKAGE_CODE = $vars['HOT_SALSA_PACKAGE_CODE'];

        // https://svcdev.hotsalsainteractive.com/user/registerAPI?appVersion=2&code=gBa4U7UYHX4Q3amRXnxGvH1rKAZsHXTXz31tbWsSTwIXG&authKey=W5fLHehgfHUhmI7x7clD8x1Ki1Gf8oY4uePbs7rHOmZb4&os=4&packageCode=com.hotsalsainteractive.browserTrivia
        $url = "{$HOT_SALSA_URL}?appVersion={$HOT_SALSA_APP_VERSION}&code={$HOT_SALSA_URL_CODE}&authKey={$HOT_SALSA_AUTH_KEY}&os={$HOT_SALSA_OS}&packageCode={$HOT_SALSA_PACKAGE_CODE}";

        print_r($url);
        // create curl resource 
        $ch = curl_init(); 

        // set url 
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_POST, true); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params); 

        //return the transfer as a string 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

        // $output contains the output string 
        $curlOutput = curl_exec($ch);
        $curlResult = json_decode($curlOutput);
        
        print_r($curlResult);

        // close curl resource to free up system resources 
        curl_close($ch);
        
        self::data_logeHotSalsaResults($curlResult, $app, $apiResponse);
    }
    
    
    private function data_hookConfigVars($prefix) {
        $varsQuery =  DBConn::executeQuery("SELECT name, `value` FROM " . DBConn::prefix() . "system_config "
                . "WHERE name LIKE '{$prefix}%' AND disabled= 0;");
                
        $vars = Array();
        while($var = $varsQuery->fetch(\PDO::FETCH_OBJ)) {
            $vars[$var->name] = $var->value;
        }
        return $vars;
    }
    
    private function data_logeHotSalsaResults($curlResult, $app, $apiResponse) {
        $logData = array(
            ':user_id' => $apiResponse['user']->id,
            ':salsa_call_status' => isset($curlResult['callStatus']) ? $curlResult['callStatus'] : 'No response',
            ':salsa_user_id' => (isset($curlResult['userData']) && isset($curlResult['userData']['userId'])) ? $curlResult['userData']['userId'] : NULL,
            ':salsa_user_data' => (isset($curlResult['userData'])) ? json_encode($curlResult['userData']) : NULL,
            ':salsa_error_message' => (isset($curlResult['errorMessage'])) ? $curlResult['errorMessage'] : NULL
        );        
        return DBConn::insert("INSERT INTO " . DBConn::prefix() . "log_hot_salsa_signup(user_id, salsa_call_status, salsa_user_id, salsa_user_data, salsa_error_message) "
                . "VALUES (:user_id, :salsa_call_status, :salsa_user_id, :salsa_user_data, :salsa_error_message);", $logData);
    }
    
    
    private function data_deleteStupidUser($id) {
        DBConn::delete("DELETE FROM `as_users` WHERE id = :id;", array(":id" => $id));
    }
}