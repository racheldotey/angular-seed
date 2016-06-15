<?php namespace API;
require_once dirname(__FILE__) . '/leaderboards.data.php';
 require_once dirname(dirname(dirname(__FILE__))) . '/config/config.php';

use \Respect\Validation\Validator as v;


class LeaderboardController {
    
    /* Global List of Teams API
     * /location/getTeamNames 
     *  
     * Per Joint List of Teams API
     * Input: locationId (optional)
     * /location/getTeamNames?locationId=111 
     * 
     * Returns: Joint Name/Id, Team Name/Id, Player List (email address, first name, lastname, image, id)
     */
    private static $HOT_SALSA_URL_TEAMS_LIST = '/location/getTeamNames';
    
    /* Global List of Joints API
     * /locationList 
     * 
     * Returns: Joint Name/Id, address, city, state, zip 
     */
    private static $HOT_SALSA_URL_VENUE_LIST = '/locationList';
    
    /* Global Player Score Leaderboard API
     * /trivia/gameNight/cumilativeScore?scoreType=player&count=10&startDate= optional&endDate=optional
     * 
     * Global Team Score Leaderboard API
     * /trivia/gameNight/cumilativeScore?scoreType=team&count=10&startDate= optional&endDate=optional 
     * 
     * Per Joint Player Score Leaderboard APIs
     * /trivia/gameNight/cumilativeScore?scoreType=player&scoreLevel=bar&locationId=111&count=10&startDate=optional&endDate=optional
     * 
     * Per Joint Team Score Leaderboard API
     * /trivia/gameNight/cumilativeScore?scoreType=team&scoreLevel=bar&locationId=111&count=10&startDate= optional&endDate=optional
     * 
     * Returns: Player Info (email address, first name, last name), Team Name, Player’s Mobile App Score 
     */
    private static $HOT_SALSA_URL_MOBILE_SCORE = '/trivia/gameNight/cumilativeScore';
    
    /* Global Player Checkins Leaderboard API
     * /location/getCheckins?scoreType=player&count=10
     * 
     * Global Team Checkins Leaderboard API
     * /location/getCheckins?scoreType=team&count=10
     * 
     * Per Joint Player Checkins Leaderboard API
     * /location/getCheckins?scoreType=player&scoreLevel=bar&locationId=111&count=10
     * 
     * Per Joint Team Checkins Leaderboard API
     * /location/getCheckins?scoreType=team&scoreLevel=bar&locationId=111&count=10
     * 
     * Returns: Player Info (email address, first name, last name), Team Name, Players’s Checkin Count
     */
    private static $HOT_SALSA_URL_GAME_CHECKINS = '/location/getCheckins';
        
    private static function callHotSalsa($url, $limit) {
        $testImage = APIConfig::get('SYSTEM_DEV_TEST_DATA_IMAGE');
        $img = ($testImage) ? $testImage : '';
        
        $results = array();
        for($i = 1; $i <= $limit; $i++) {
            $results[] = array( 'img' => $img, 'label' => "Name #{$i}", 'mobileScore' => rand(1, 100), 'liveScore' => rand(1, 100) );
        }
        return $results;
    }
    
    private static function makeHotSalsaRequest($apiPath) { 
        $log = new Logging('leaderboards');
            
        $url = APIConfig::get('HOT_SALSA_API_URL');
        $version = APIConfig::get('HOT_SALSA_APP_VERSION');
        $code = APIConfig::get('HOT_SALSA_URL_CODE');
        $key = APIConfig::get('HOT_SALSA_AUTH_KEY');
        $os = APIConfig::get('HOT_SALSA_OS');
        $package = APIConfig::get('HOT_SALSA_PACKAGE_CODE');

        if (!$url || !$version || !$code || !$key || !$os || !$package) {
            $log->write("Could not attempt call. The Hot Salsa signup hook is enabled but a system variable is disabled or missing.");
            $log->write(array(
                'apiUrl' => $url,
                'appVersion' => $version,
                'code' => $code,
                'authKey' => $key,
                'os' => $os,
                'packageCode' => $package
            ));
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
        curl_setopt($ch, CURLOPT_URL, $url . $apiPath);
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
            
            $log->write($error);
            $log->write($info);
            
            curl_close($ch);
            
            return false;
        }
        
        // Results
        $curlResult = json_decode($curlOutput, true);
        
        if (!isset($curlResult['status']) || $curlResult['status'] === 'failed') {
            $error = (isset($curlResult['status'])) ? $curlResult['status'] : 'ERROR: Unknown error occured';
            
            $log->write($error);
            
            curl_close($ch);
            
            return false;
        } 
        curl_close($ch);
        return $curlResult;
    }
    
    private static function getHotSalsaVenues() {
        $url = 'locationList';
        return $results;
    }

    private static function getHotSalsaLocationId($venueName, $venueZip) {
        return '65';
    }
    
    // Global Player Score Leaderboard
    static function getGlobalPlayersLeaderboard($app, $count) {
        $limit = (!v::intVal()->validate($count)) ? '10' : $count;
        
        // /trivia/gameNight/cumilativeScore?scoreType=player&count=10&startDate= optional&endDate=optional
        // Returns: Player Info (email address, first name, last name), Team Name, Player’s Mobile App Score 
        $url = self::$HOT_SALSA_URL_MOBILE_SCORE . "?scoreType=player&count={$limit}";
        
            
        $data = self::makeHotSalsaRequest($url);
        if($data) {
            return $app->render(200, array('leaderboard' => $data));
        } else {
            return $app->render(400,  array('msg' => 'Could not select Global Player Score Leaderboard.'));
        }
    }

    // Global Team Score Leaderboard
    static function getGlobalTeamsLeaderboard($app, $count) {
        $limit = (!v::intVal()->validate($count)) ? '10' : $count;
        
        // /trivia/gameNight/cumilativeScore?scoreType=team&count=10&startDate= optional&endDate=optional 
        // Returns: Player Info (email address, first name, last name), Team Name, Player’s Mobile App Score 
        $url = self::$HOT_SALSA_URL_MOBILE_SCORE . "?scoreType=team&count={$limit}";
        
        $data = self::makeHotSalsaRequest($url);
        if($data) {
            return $app->render(200, array('leaderboard' => $data));
        } else {
            return $app->render(400,  array('msg' => 'Could not select Global Team Score Leaderboard.'));
        }
    }

    // Per Joint Player Score Leaderboard
    static function getJointPlayersLeaderboard($app, $venueId, $count) {
        if(!v::intVal()->validate($venueId)) {
            return $app->render(400,  array('msg' => 'Invalid Joint ID. Check your parameters and try again.'));
        }
        
        $limit = (!v::intVal()->validate($count)) ? '10' : $count;
        
        $locationId = self::getHotSalsaLocationId('', '');
        
        // /trivia/gameNight/cumilativeScore?scoreType=player&scoreLevel=bar&locationId=111&count=10&startDate=optional&endDate=optional
        // Returns: Player Info (email address, first name, last name), Team Name, Player’s Mobile App Score 
        $url = self::$HOT_SALSA_URL_MOBILE_SCORE . "?scoreType=player&scoreLevel=bar&locationId={$locationId}&count={$limit}";
        
        $data = self::makeHotSalsaRequest($url);
        if($data) {
            return $app->render(200, array('leaderboard' => $data));
        } else {
            return $app->render(400,  array('msg' => 'Could not select Per Joint Player Score Leaderboard.'));
        }
    }

    // Per Joint Team Score Leaderboard
    static function getJointTeamsLeaderboard($app, $venueId, $count) {
        if(!v::intVal()->validate($venueId)) {
            return $app->render(400,  array('msg' => 'Invalid Joint ID. Check your parameters and try again.'));
        }
        
        $limit = (!v::intVal()->validate($count)) ? '10' : $count;
        
        $locationId = self::getHotSalsaLocationId('', '');
        
        // /trivia/gameNight/cumilativeScore?scoreType=team&scoreLevel=bar&locationId=111&count=10&startDate= optional&endDate=optional
        // Returns: Player Info (email address, first name, last name), Team Name, Player’s Mobile App Score 
        $url = self::$HOT_SALSA_URL_MOBILE_SCORE . "?scoreType=team&scoreLevel=bar&locationId={$locationId}&count={$limit}";
        
        $data = self::makeHotSalsaRequest($url);
        if($data) {
            return $app->render(200, array('leaderboard' => $data));
        } else {
            return $app->render(400,  array('msg' => 'Could not select Per Joint Team Score Leaderboard.'));
        }
    }
    
    // Global Player Checkin Leaderboard
    static function getGlobalPlayerCheckinsLeaderboard($app, $count) {
        $limit = (!v::intVal()->validate($count)) ? '10' : $count;
        
        // /location/getCheckins?scoreType=player&count=10
        // Returns: Player Info (email address, first name, last name), Team Name, Players’s Checkin Count
        $url = self::$HOT_SALSA_URL_GAME_CHECKINS . "?scoreType=player&count={$limit}";
        
        $data = self::makeHotSalsaRequest($url);
        if($data) {
            return $app->render(200, array('leaderboard' => $data));
        } else {
            return $app->render(400,  array('msg' => 'Could not select Global Player Checkin Leaderboard.'));
        }
    }

    // Global Team Checkin Leaderboard
    static function getGlobalTeamCheckinsLeaderboard($app, $count) {
        $limit = (!v::intVal()->validate($count)) ? '10' : $count;
        
        // /location/getCheckins?scoreType=team&count=10
        // Returns: Player Info (email address, first name, last name), Team Name, Players’s Checkin Count
        $url = self::$HOT_SALSA_URL_GAME_CHECKINS . "?scoreType=team&count={$limit}";
        
        $data = self::makeHotSalsaRequest($url);
        if($data) {
            return $app->render(200, array('leaderboard' => $data));
        } else {
            return $app->render(400,  array('msg' => 'Could not select Global Team Checkin Leaderboard.'));
        }
    }

    // Per Joint Player Checkins Leaderboard
    static function getVenuePlayerCheckinsLeaderboard($app, $venueId, $count) {
        if(!v::intVal()->validate($venueId)) {
            return $app->render(400,  array('msg' => 'Invalid Joint ID. Check your parameters and try again.'));
        }
        
        $limit = (!v::intVal()->validate($count)) ? '10' : $count;
        
        $locationId = self::getHotSalsaLocationId('', '');
        
        // /location/getCheckins?scoreType=player&scoreLevel=bar&locationId=111&count=10
        // Returns: Player Info (email address, first name, last name), Team Name, Players’s Checkin Count
        $url = self::$HOT_SALSA_URL_GAME_CHECKINS . "?scoreType=player&scoreLevel=bar&locationId={$locationId}&count={$limit}";
        
        $data = self::makeHotSalsaRequest($url);
        if($data) {
            return $app->render(200, array('leaderboard' => $data));
        } else {
            return $app->render(400,  array('msg' => 'Could not select Per Joint Player Checkins Leaderboard.'));
        }
    }

    // Per Joint Team Checkins Leaderboard
    static function getVenueTeamCheckinsLeaderboard($app, $venueId, $count) {
        if(!v::intVal()->validate($venueId)) {
            return $app->render(400,  array('msg' => 'Invalid Joint ID. Check your parameters and try again.'));
        }
        
        $limit = (!v::intVal()->validate($count)) ? '10' : $count;
        
        $locationId = self::getHotSalsaLocationId('', '');
        
        // /location/getCheckins?scoreType=team&scoreLevel=bar&locationId=111&count=10
        // Returns: Player Info (email address, first name, last name), Team Name, Players’s Checkin Count
        $url = self::$HOT_SALSA_URL_GAME_CHECKINS . "?scoreType=team&scoreLevel=bar&locationId={$locationId}&count={$limit}";
        
        $data = self::makeHotSalsaRequest($url);
        if($data) {
            return $app->render(200, array('leaderboard' => $data));
        } else {
            return $app->render(400,  array('msg' => 'Could not select Per Joint Team Checkins Leaderboard.'));
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