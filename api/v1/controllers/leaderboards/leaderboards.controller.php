<?php namespace API;
require_once dirname(__FILE__) . '/leaderboards.data.php';

use \Respect\Validation\Validator as v;


class LeaderboardController {

    private static function hook($app, $apiResponse) { 
        $vars = self::data_hookConfigVars('HOT_SALSA_');

        if (!isset($vars['HOT_SALSA_PLAYER_REGISTRATION_ENABLED']) || ($vars['HOT_SALSA_PLAYER_REGISTRATION_ENABLED'] !== 'true' && $vars['HOT_SALSA_PLAYER_REGISTRATION_ENABLED'] !== '1')) {
            return;
        }

        if (!isset($vars['HOT_SALSA_PLAYER_REGISTRATION_URL']) ||
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

        if (!$curlOutput) {
            // No Results = Error
            $error = (curl_error($ch)) ? curl_error($ch) : 'ERROR: No results';
            $info = (curl_getinfo($ch)) ? json_encode(curl_getinfo($ch)) : 'ERROR: No Info';
            self::data_logHotSalsaError($apiResponse['user']->id, $error, $info);
        } else {
            // Results
            $curlResult = json_decode($curlOutput, true);
            if (!isset($curlResult['status']) || $curlResult['status'] === 'failed') {
                $error = (isset($curlResult['status'])) ? $curlResult['status'] : 'ERROR: Unknown error occured';
                self::data_logHotSalsaError($apiResponse['user']->id, $error, $curlOutput);
            } else {
                self::data_logHotSalsaResults($curlResult, $app, $apiResponse);
            }
        }

        // close curl resource to free up system resources 
        curl_close($ch);
    }
    
    private static function callHotSalsa($url, $limit) {
        $testImage = APIConfig::get('SYSTEM_DEV_TEST_DATA_IMAGE');
        $img = ($testImage) ? $testImage : '';
        
        $results = array();
        for($i = 1; $i <= $limit; $i++) {
            $results[] = array( 'img' => $img, 'label' => "Name #{$i}", 'mobileScore' => rand(1, 100), 'liveScore' => rand(1, 100) );
        }
        return $results;
    }
    
    private static function makeHotSalsaRequest($app, $apiResponse) { 
        $url = APIConfig::get('HOT_SALSA_API_URL');
        $version = APIConfig::get('HOT_SALSA_APP_VERSION');
        $code = APIConfig::get('HOT_SALSA_URL_CODE');
        $key = APIConfig::get('HOT_SALSA_AUTH_KEY');
        $os = APIConfig::get('HOT_SALSA_OS');
        $package = APIConfig::get('HOT_SALSA_PACKAGE_CODE');

        if (!$url || !$version || !$code || !$key || !$os || !$package) {
            self::data_logHotSalsaError($apiResponse['user']->id, "Could not attempt call. The Hot Salsa signup hook is enabled but a system variable is disabled or missing.", $vars);
            return false;
        }

        $params = array(
            'appVersion' => $version,
            'code' => $code,
            'authKey' => $key,
            'os' => $os,
            'packageCode' => $package
        );
        
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

        if (!$curlOutput) {
            // No Results = Error
            $error = (curl_error($ch)) ? curl_error($ch) : 'ERROR: No results';
            $info = (curl_getinfo($ch)) ? json_encode(curl_getinfo($ch)) : 'ERROR: No Info';
            self::data_logHotSalsaError($apiResponse['user']->id, $error, $info);
        } else {
            // Results
            $curlResult = json_decode($curlOutput, true);
            if (!isset($curlResult['status']) || $curlResult['status'] === 'failed') {
                $error = (isset($curlResult['status'])) ? $curlResult['status'] : 'ERROR: Unknown error occured';
                self::data_logHotSalsaError($apiResponse['user']->id, $error, $curlOutput);
            } else {
                self::data_logHotSalsaResults($curlResult, $app, $apiResponse);
            }
        }

        // close curl resource to free up system resources 
        curl_close($ch);
    }

    // Global Player Score Leaderboard
    static function getGlobalPlayersLeaderboard($app, $count) {
        $limit = (!v::intVal()->validate($count)) ? '10' : $count;
        $url = '';
        
        $data = self::callHotSalsa($url, $limit);
        if($data) {
            return $app->render(200, array('leaderboard' => $data));
        } else {
            return $app->render(400,  array('msg' => 'Could not select list of joints.'));
        }
    }

    // Global Team Score Leaderboard
    static function getGlobalTeamsLeaderboard($app, $count) {
        $limit = (!v::intVal()->validate($count)) ? '10' : $count;
        $url = '';
        
        $data = self::callHotSalsa($url, $limit);
        if($data) {
            return $app->render(200, array('leaderboard' => $data));
        } else {
            return $app->render(400,  array('msg' => 'Could not select list of joints.'));
        }
    }

    // Per Joint Player Score Leaderboard
    static function getJointPlayersLeaderboard($app, $venueId, $count) {
        if(!v::intVal()->validate($venueId)) {
            return $app->render(400,  array('msg' => 'Invalid Joint ID. Check your parameters and try again.'));
        }
        
        $limit = (!v::intVal()->validate($count)) ? '10' : $count;
        $url = '';
        
        $data = self::callHotSalsa($url, $limit);
        if($data) {
            return $app->render(200, array('leaderboard' => $data));
        } else {
            return $app->render(400,  array('msg' => 'Could not select list of joints.'));
        }
    }

    // Per Joint Team Score Leaderboard
    static function getJointTeamsLeaderboard($app, $venueId, $count) {
        if(!v::intVal()->validate($venueId)) {
            return $app->render(400,  array('msg' => 'Invalid Joint ID. Check your parameters and try again.'));
        }
        
        $limit = (!v::intVal()->validate($count)) ? '10' : $count;
        $url = '';
        
        $data = self::callHotSalsa($url, $limit);
        if($data) {
            return $app->render(200, array('leaderboard' => $data));
        } else {
            return $app->render(400,  array('msg' => 'Could not select list of joints.'));
        }
    }

    // Global Player Checkin Leaderboard
    static function getGlobalPlayerCheckinsLeaderboard($app, $count) {
        $limit = (!v::intVal()->validate($count)) ? '10' : $count;
        $url = '';
        
        $data = self::callHotSalsa($url, $limit);
        if($data) {
            return $app->render(200, array('leaderboard' => $data));
        } else {
            return $app->render(400,  array('msg' => 'Could not select list of joints.'));
        }
    }

    // Global Team Checkin Leaderboard
    static function getGlobalTeamCheckinsLeaderboard($app, $count) {
        $limit = (!v::intVal()->validate($count)) ? '10' : $count;
        $url = '';
        
        $data = self::callHotSalsa($url, $limit);
        if($data) {
            return $app->render(200, array('leaderboard' => $data));
        } else {
            return $app->render(400,  array('msg' => 'Could not select list of joints.'));
        }
    }

    // Per Joint Player Checkins Leaderboard
    static function getVenuePlayerCheckinsLeaderboard($app, $venueId, $count) {
        if(!v::intVal()->validate($venueId)) {
            return $app->render(400,  array('msg' => 'Invalid Joint ID. Check your parameters and try again.'));
        }
        
        $limit = (!v::intVal()->validate($count)) ? '10' : $count;
        $url = '';
        
        $data = self::callHotSalsa($url, $limit);
        if($data) {
            return $app->render(200, array('leaderboard' => $data));
        } else {
            return $app->render(400,  array('msg' => 'Could not select list of joints.'));
        }
    }

    // Per Joint Team Checkins Leaderboard
    static function getVenueTeamCheckinsLeaderboard($app, $venueId, $count) {
        if(!v::intVal()->validate($venueId)) {
            return $app->render(400,  array('msg' => 'Invalid Joint ID. Check your parameters and try again.'));
        }
        
        $limit = (!v::intVal()->validate($count)) ? '10' : $count;
        $url = '';
        
        $data = self::callHotSalsa($url, $limit);
        if($data) {
            return $app->render(200, array('leaderboard' => $data));
        } else {
            return $app->render(400,  array('msg' => 'Could not select list of joints.'));
        }
    }
    
    static function getVenueList($app) {
        $data = LeaderboardData::selectVenueList();
        
        if($data) {
            return $app->render(200, array('joints' => $data));
        } else {
            return $app->render(400,  array('msg' => 'Could not select list of joints.'));
        }
    }
}