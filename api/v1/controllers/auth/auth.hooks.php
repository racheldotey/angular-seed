<?php namespace API;
require_once dirname(__FILE__) . '/auth.data.php';

class AuthHooks {
    
    static function signup($app, $apiResponse) {
        self::hookCallHotSalsa($app, $apiResponse);
    }    
    
    private static function hookCallHotSalsa($app, $apiResponse) {
        $vars = self::data_hookConfigVars('HOT_SALSA_');
        
        if(!isset($vars['HOT_SALSA_PLAYER_REGISTRATION_ENABLED']) || ($vars['HOT_SALSA_PLAYER_REGISTRATION_ENABLED'] !== 'true' && $vars['HOT_SALSA_PLAYER_REGISTRATION_ENABLED'] !== '1')) {
            return;
        }
        
        if(!isset($vars['HOT_SALSA_PLAYER_REGISTRATION_URL']) || 
            !isset($vars['HOT_SALSA_APP_VERSION']) || 
            !isset($vars['HOT_SALSA_URL_CODE']) || 
            !isset($vars['HOT_SALSA_AUTH_KEY']) || 
            !isset($vars['HOT_SALSA_OS']) || 
            !isset($vars['HOT_SALSA_PACKAGE_CODE'])) {
            
            self::data_logHotSalsaError($apiResponse['user']->id, "Could not attempt call. The Hot Salsa signup hook is enabled but a system variable is disabled or missing.", $vars);
            return;
        }
        
        // Get Post Data
        $post = $app->request->post();
        $params = array(
            'email' => $post['email'],
            'firstName' => $post['nameFirst'],
            'lastName' => $post['nameLast'],
            'appVersion' => $vars['HOT_SALSA_APP_VERSION'],
            'code' => $vars['HOT_SALSA_URL_CODE'],
            'authKey' => $vars['HOT_SALSA_AUTH_KEY'],
            'os' => $vars['HOT_SALSA_OS'],
            'packageCode' => $vars['HOT_SALSA_PACKAGE_CODE']
        );
        // If it was standard signup
        if(isset($post['password'])) {
            $params['password'] = password_hash($post['password'], PASSWORD_DEFAULT);
        }
        // If it was facebook signup
        if(isset($post['facebookId'])) {
            $params['facebookId'] = $post['facebookId'];
        }
        
        // create curl resource 
        $ch = curl_init(); 

        // set url 
        curl_setopt($ch, CURLOPT_URL, $vars['HOT_SALSA_PLAYER_REGISTRATION_URL']); 
        curl_setopt($ch, CURLOPT_POST, true); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params); 

        //return the transfer as a string 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

        // $output contains the output string 
        $curlOutput = curl_exec($ch);
        
        if(!$curlOutput) {
            // No Results = Error
            $error = (curl_error($ch)) ? curl_error($ch) : 'ERROR: No results';
            $info = (curl_getinfo($ch)) ? json_encode(curl_getinfo($ch)) : 'ERROR: No Info';
            self::data_logHotSalsaError($apiResponse['user']->id, $error, $info);
        } else {
            // Results
            $curlResult = json_decode($curlOutput, true);
            if(!isset($curlResult['status']) || $curlResult['status'] === 'failed') {
                $error = (isset($curlResult['status'])) ? $curlResult['status'] : 'ERROR: Unknown error occured';
                self::data_logHotSalsaError($apiResponse['user']->id, $error, $curlOutput);
            } else {
                self::data_logHotSalsaResults($curlResult, $app, $apiResponse);
            }
        }        

        // close curl resource to free up system resources 
        curl_close($ch);
        
    }    
    
    private static function data_hookConfigVars($prefix) {
        $varsQuery =  DBConn::executeQuery("SELECT name, `value` FROM " . DBConn::prefix() . "system_config "
                . "WHERE name LIKE '{$prefix}%' AND disabled= 0;");
                
        $vars = Array();
        while($var = $varsQuery->fetch(\PDO::FETCH_OBJ)) {
            $vars[$var->name] = $var->value;
        }
        return $vars;
    }
    
    private static function data_logHotSalsaResults($curlResult, $app, $apiResponse) {
        $logData = array(
            ':user_id' => $apiResponse['user']->id,
            ':salsa_call_status' => isset($curlResult['status']) ? $curlResult['status'] : 'No response',
            ':salsa_user_id' => (isset($curlResult['userData']) && isset($curlResult['userData']['userId'])) ? $curlResult['userData']['userId'] : NULL,
            ':salsa_user_data' => (isset($curlResult['userData'])) ? json_encode($curlResult['userData']) : NULL,
            ':salsa_error_message' => (isset($curlResult['errorMessage'])) ? $curlResult['errorMessage'] : NULL
        );        
        return DBConn::insert("INSERT INTO " . DBConn::prefix() . "logs_hot_salsa_signup(user_id, salsa_call_status, salsa_user_id, salsa_user_data, salsa_error_message) "
                . "VALUES (:user_id, :salsa_call_status, :salsa_user_id, :salsa_user_data, :salsa_error_message);", $logData);
    }
    
    private static function data_logHotSalsaError($userId, $errorMessage, $data) {
        $logData = array(
            ':user_id' => $userId,
            ':salsa_call_status' => $errorMessage,
            ':salsa_error_message' => json_encode($data)
        );

        return DBConn::insert("INSERT INTO " . DBConn::prefix() . "logs_hot_salsa_signup(user_id, salsa_call_status, salsa_error_message) "
                . "VALUES (:user_id, :salsa_call_status, :salsa_error_message);", $logData);
    }




    private static function data_deleteStupidUser($id) {
        DBConn::delete("DELETE FROM " . DBConn::prefix() . "users WHERE id = :id;", array(":id" => $id));
    }
}