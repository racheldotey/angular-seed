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
     * /location/getTeamNames?locationId=11 
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
     * /trivia/gameNight/cumilativeScore?scoreType=player&scoreLevel=bar&locationId=11&count=10&startDate=optional&endDate=optional
     * 
     * Per Joint Team Score Leaderboard API
     * /trivia/gameNight/cumilativeScore?scoreType=team&scoreLevel=bar&locationId=11&count=10&startDate= optional&endDate=optional
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
     * /location/getCheckins?scoreType=player&scoreLevel=bar&locationId=11&count=10
     * 
     * Per Joint Team Checkins Leaderboard API
     * /location/getCheckins?scoreType=team&scoreLevel=bar&locationId=11&count=10
     * 
     * Returns: Player Info (email address, first name, last name), Team Name, Players’s Checkin Count
     */
    private static $HOT_SALSA_URL_GAME_CHECKINS = '/location/getCheckins';
    
    private static function makeHotSalsaRequest($apiPath, $app) { 
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
    
    private static function getHotSalsaLocationId($venueId) {
        // Leave for hooking into later
        return 
        $venueId;
    }
    
    
    // Global Player Score Leaderboard
    static function getGlobalPlayersLeaderboard($app, $count) {
        $limit = (!v::intVal()->validate($count)) ? '10' : $count;
        
        // /trivia/gameNight/cumilativeScore?scoreType=player&count=10&startDate= optional&endDate=optional
        $url = self::$HOT_SALSA_URL_MOBILE_SCORE . "?scoreType=player&count={$limit}";
        $salsaData = self::makeHotSalsaRequest($url, $app);
        
        if($salsaData && isset($salsaData['scores'])) {
        /* {
         *      "status":"success",
         *      "scores":[
         *          {"name":"billman_c@hotmail.com",
         *          "userId":"1770",
         *          "score":"783",
         *          "confirmed":"0",
         *          "lastName":"Rustad",
         *          "firstName":"Conrad",
         *          "emailAddress":"billman_c@hotmail.com",
         *          "facebookId":"10154054462631407",
         *          "teamName":"Super Villans",
         *          "photoUser":"http://cfxcdnorigin.hotsalsainteractive.com/hotsalsainteractive/userPhoto/1463168056.png","hasPhoto":1,"upgraded":1}
         *      ]
         * } */
            $results = array();
            foreach($salsaData['scores'] AS $salsaPlayer) {
                $first = (isset($salsaPlayer['firstName'])) ? $salsaPlayer['firstName'] : '';
                $last = (isset($salsaPlayer['lastName'])) ? $salsaPlayer['lastName'] : '';
                $email = (isset($salsaPlayer['emailAddress'])) ? $salsaPlayer['emailAddress'] : '';
                
                $teamName = (isset($salsaPlayer['teamName'])) ? $salsaPlayer['teamName'] : '';
                $homeJoint = (isset($salsaPlayer['homeJoint'])) ? $salsaPlayer['homeJoint'] : '';
                        
                $player = LeaderboardData::selectPlayerLiveScoreByEmail($email, $teamName, $homeJoint);
                if(!$player) {
                    $player = array();
                }
                        
                $results[] = array( 
                    'mobileScore' => (isset($salsaPlayer['score'])) ? $salsaPlayer['score'] : 0,
                    'liveScore' => ($player && isset($player['score'])) ? $player['score'] : 0,
                    
                    'player' => "{$first} {$last}", 
                    'userId' => ($player && isset($player['userId'])) ? $player['userId'] : 0,
                    'hotSalsaUserId' => (isset($salsaPlayer['userId'])) ? $salsaPlayer['userId'] : 0,
                    'email' => $email, 
                    'img' => (isset($salsaPlayer['photoUser'])) ? $salsaPlayer['photoUser'] : '', 
                            
                    'teamName' => $teamName,
                    'teamId' => ($player && isset($player['teamId'])) ? $player['teamId'] : 0, 
                    'hotSalsaTeamId' => (isset($salsaPlayer['teamId'])) ? $salsaPlayer['teamId'] : 0,
                            
                    'homeJoint' => $homeJoint,
                    'homeJointId' => ($player && isset($player['homeVenueId'])) ? $player['homeVenueId'] : 0,
                    'hotSalsaHomeJointId' => (isset($salsaPlayer['jointId'])) ? $salsaPlayer['jointId'] : 0
                );
            }
            return $app->render(200, array('leaderboard' => $results));
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
        
        $salsaData = self::makeHotSalsaRequest($url, $app);
        if($salsaData && isset($salsaData['scores'])) {
            /*  {
             *      "status":"success",
             *      "scores":[{  
             *          "name":"Answerers",
             *          "score":"71",
             *          "players":[{  
             *              "userId":"1031",
             *              "confirmed":"0",
             *              "lastName":"Test",
             *              "firstName":"Frank",
             *              "emailAddress":"Frank@test.go",
             *              "facebookId":"10207653889717859",
             *              "teamName":"Answerers",
             *              "photoUser":"http://cfxcdnorigin.hotsalsainteractive.com/hotsalsainteractive/userPhoto/1458942803.png",
             *              "hasPhoto":0,
             *              "upgraded":0
             *         }]
             * } */
            $results = array();
            foreach($salsaData['scores'] AS $salsaTeam) {
                $teamName = (isset($salsaTeam['name'])) ? $salsaTeam['name'] : '';
                $homeJoint = (isset($salsaTeam['homeJoint'])) ? $salsaTeam['homeJoint'] : '';
                
                $team = LeaderboardData::selectTeamLiveScoreByNameAndVenue($teamName, $homeJoint);
                if(!$team) {
                    $team = array();
                }
                
                $results[] = array( 
                    'mobileScore' => (isset($salsaTeam['score'])) ? $salsaTeam['score'] : 0,
                    'liveScore' => ($team && isset($team['score'])) ? $team['score'] : 0,
                    
                    'teamName' => $teamName,
                    'teamId' => ($team && isset($team['teamId'])) ? $team['teamId'] : 0,
                    'hotSalsaTeamId' => (isset($salsaTeam['teamId'])) ? $salsaTeam['teamId'] : 0,
                    
                    'homeJoint' => $homeJoint,
                    'homeJointId' => ($team && isset($team['homeVenueId'])) ? $team['homeVenueId'] : 0,
                    'hotSalsaHomeJointId' => (isset($salsaTeam['jointId'])) ? $salsaTeam['jointId'] : 0
                );
            }
            return $app->render(200, array('leaderboard' => $results));
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
        
        $locationId = self::getHotSalsaLocationId($venueId);
        
        // /trivia/gameNight/cumilativeScore?scoreType=player&scoreLevel=bar&locationId=11&count=10&startDate=optional&endDate=optional
        $url = self::$HOT_SALSA_URL_MOBILE_SCORE . "?scoreType=player&scoreLevel=bar&locationId={$locationId}&count={$limit}";
        
        $salsaData = self::makeHotSalsaRequest($url, $app);
        /*
        {  
            "status":"success",
            "addresses":{  
               "sdkAddressId":"11",
               "name":"Bayou Cafe",
               "city":"Schenectady",
               "state":"NY",
               "address":"507 Saratoga Road",
               "postalCode":"12302",
               "country":"US",
               "image":"http://cfxcdnorigin.hotsalsainteractive.com/hotsalsainteractive/address/1459204914.png",
               "hasTrivia":1,
               "triviaDay":"Wednesday",
               "triviaTime":"7:00 PM"
            },
            "scores":[  
               {  
                  "name":"10154054462631407",
                  "userId":"1044",
                  "score":"550",
                  "confirmed":"1",
                  "lastName":"Rustad",
                  "firstName":"Conrad",
                  "emailAddress":"10154054462631407",
                  "facebookId":"10154054462631407",
                  "teamName":"Lotus",
                  "photoUser":"http://cfxcdnorigin.hotsalsainteractive.com/hotsalsainteractive/userPhoto/1458942803.png",
                  "hasPhoto":0,
                  "upgraded":0
               }
         }
         */
        if($salsaData && isset($salsaData['scores'])) {
            $results = array();
            foreach($salsaData['scores'] AS $salsaPlayer) {
                $first = (isset($salsaPlayer['firstName'])) ? $salsaPlayer['firstName'] : '';
                $last = (isset($salsaPlayer['lastName'])) ? $salsaPlayer['lastName'] : '';
                $email = (isset($salsaPlayer['emailAddress'])) ? $salsaPlayer['emailAddress'] : '';
                
                $teamName = (isset($salsaPlayer['teamName'])) ? $salsaPlayer['teamName'] : '';
                $homeJoint = (isset($salsaPlayer['homeJoint'])) ? $salsaPlayer['homeJoint'] : '';
                        
                $player = LeaderboardData::selectPlayerLiveScoreByEmail($email, $teamName, $homeJoint);
                if(!$player) {
                    $player = array();
                }
                        
                $results[] = array( 
                    'mobileScore' => (isset($salsaPlayer['score'])) ? $salsaPlayer['score'] : 0,
                    'liveScore' => ($player && isset($player['score'])) ? $player['score'] : 0,
                    
                    'player' => "{$first} {$last}", 
                    'userId' => ($player && isset($player['userId'])) ? $player['userId'] : 0,
                    'hotSalsaUserId' => (isset($salsaPlayer['userId'])) ? $salsaPlayer['userId'] : 0,
                    'email' => $email, 
                    'img' => (isset($salsaPlayer['photoUser'])) ? $salsaPlayer['photoUser'] : '', 
                            
                    'teamName' => $teamName,
                    'teamId' => ($player && isset($player['teamId'])) ? $player['teamId'] : 0, 
                    'hotSalsaTeamId' => (isset($salsaPlayer['teamId'])) ? $salsaPlayer['teamId'] : 0,
                            
                    'homeJoint' => $homeJoint,
                    'homeJointId' => ($player && isset($player['homeVenueId'])) ? $player['homeVenueId'] : 0,
                    'hotSalsaHomeJointId' => (isset($salsaPlayer['jointId'])) ? $salsaPlayer['jointId'] : 0
                );
            }
            return $app->render(200, array('leaderboard' => $results));
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
        
        $locationId = self::getHotSalsaLocationId($venueId);
        
        // /trivia/gameNight/cumilativeScore?scoreType=team&scoreLevel=bar&locationId=11&count=10&startDate= optional&endDate=optional
        // Returns: Player Info (email address, first name, last name), Team Name, Player’s Mobile App Score 
        $url = self::$HOT_SALSA_URL_MOBILE_SCORE . "?scoreType=team&scoreLevel=bar&locationId={$locationId}&count={$limit}";
        
        $salsaData = self::makeHotSalsaRequest($url, $app);
        /* {  
           "status":"success",
           "addresses":{  
              "sdkAddressId":"11",
              "name":"Bayou Cafe",
              "city":"Schenectady",
              "state":"NY",
              "address":"507 Saratoga Road",
              "postalCode":"12302",
              "country":"US",
              "image":"http://cfxcdnorigin.hotsalsainteractive.com/hotsalsainteractive/address/1459204914.png",
              "hasTrivia":1,
              "triviaDay":"Wednesday",
              "triviaTime":"7:00 PM"
           },
           "scores":[{  
                "name":"Super Villans",
                "score":"80",
                "players":[  
                   {  
                      "userId":"1000",
                      "confirmed":"0",
                      "lastName":"Testerton",
                      "firstName":"Testle",
                      "emailAddress":"test@test.test",
                      "facebookId":"",
                      "teamName":"Super Villans",
                      "photoUser":"http://cfxcdnorigin.hotsalsainteractive.com/hotsalsainteractive/userPhoto/1458942803.png",
                      "hasPhoto":0,
                      "upgraded":0
                   }
                ]
             }]
        } */
        if($salsaData && isset($salsaData['addresses']) && isset($salsaData['scores'])) {
            $results = array();
            foreach($salsaData['scores'] AS $salsaTeam) {
                $teamName = (isset($salsaTeam['name'])) ? $salsaTeam['name'] : '';
                $homeJoint = (isset($salsaData['addresses']['name'])) ? $salsaData['addresses']['name'] : '';
                
                $team = LeaderboardData::selectTeamLiveScoreByNameAndVenue($teamName, $homeJoint);
                if(!$team) {
                    $team = array();
                }
                
                $results[] = array( 
                    'mobileScore' => (isset($salsaTeam['score'])) ? $salsaTeam['score'] : 0,
                    'liveScore' => ($team && isset($team['score'])) ? $team['score'] : 0,
                    
                    'teamName' => $teamName,
                    'teamId' => ($team && isset($team['teamId'])) ? $team['teamId'] : 0,
                    'hotSalsaTeamId' => (isset($salsaTeam['teamId'])) ? $salsaTeam['teamId'] : 0,
                    
                    'homeJoint' => $homeJoint,
                    'homeJointId' => ($team && isset($team['homeVenueId'])) ? $team['homeVenueId'] : 0,
                    'hotSalsaHomeJointId' => (isset($salsaTeam['jointId'])) ? $salsaTeam['jointId'] : 0
                );
            }
            return $app->render(200, array('leaderboard' => $results));
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
        
        $data = self::makeHotSalsaRequest($url, $app);
        /* {
         *      "status":"success",
         *      "checkins":[
         *          {"firstname":"Pavel",
         *          "lastName":"Goncharov",
         *          "email":"thundrax@gmail.com",
         *          "teamName":"Lotus",
         *          "checkinCount":"3"}
         *      ]
         * } */
        if($data && isset($data['checkins'])) {
            $results = array();
            foreach($data['checkins'] AS $player) {
                $first = (isset($player['firstName'])) ? $player['firstName'] : $player['firstname'];
                $last = (isset($player['lastName'])) ? $player['lastName'] : '';
                $mobile = (isset($player['checkinCount'])) ? $player['checkinCount'] : 0;
                $live = 0;
                        
                $results[] = array( 
                    'img' => '', 
                    'label' => "{$first} {$last}", 
                    'mobileScore' => $mobile,
                    'liveScore' => $live
                );
            }
            return $app->render(200, array('leaderboard' => $results));
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
        
        $data = self::makeHotSalsaRequest($url, $app);
        /* {
         *      "status":"success",
         *      "checkins":[
         *          { "players":[
         *              {"firstname":"Pavel",
         *              "lastName":"Goncharov",
         *              "email":"thundrax@gmail.com",
         *              "teamName":"Lotus",
         *              "checkinCount":"3"}],
         *          "teamName":"Super Villans",
         *          "checkinCount":"4"}]}
         *      ]
         * } */
        if($data && isset($data['checkins'])) {
            $results = array();
            foreach($data['checkins'] AS $team) {
                $name = (isset($team['teamName'])) ? $team['teamName'] : '';
                $mobile = (isset($team['checkinCount'])) ? $team['checkinCount'] : 0;
                $live = 0;
                        
                $results[] = array( 
                    'img' => '', 
                    'label' => $name, 
                    'mobileScore' => $mobile,
                    'liveScore' => $live
                );
            }
            return $app->render(200, array('leaderboard' => $results));
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
        
        $locationId = self::getHotSalsaLocationId($venueId);
        
        // /location/getCheckins?scoreType=player&scoreLevel=bar&locationId=11&count=10
        // Returns: Player Info (email address, first name, last name), Team Name, Players’s Checkin Count
        $url = self::$HOT_SALSA_URL_GAME_CHECKINS . "?scoreType=player&scoreLevel=bar&locationId={$locationId}&count={$limit}";
        
        $data = self::makeHotSalsaRequest($url, $app);
        /* {
         *      "status":"success",
         *      "checkins":[
         *          {"firstname":"Pavel",
         *          "lastName":"Goncharov",
         *          "email":"thundrax@gmail.com",
         *          "teamName":"Lotus",
         *          "checkinCount":"3"}
         *      ]
         * } */
        if($data && isset($data['checkins'])) {
            $results = array();
            foreach($data['checkins'] AS $player) {
                $first = (isset($player['firstName'])) ? $player['firstName'] : $player['firstname'];
                $last = (isset($player['lastName'])) ? $player['lastName'] : '';
                $mobile = (isset($player['checkinCount'])) ? $player['checkinCount'] : 0;
                $live = 0;
                        
                $results[] = array( 
                    'img' => '', 
                    'label' => "{$first} {$last}", 
                    'mobileScore' => $mobile,
                    'liveScore' => $live
                );
            }
            return $app->render(200, array('leaderboard' => $results));
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
        
        $locationId = self::getHotSalsaLocationId($venueId);
        
        // /location/getCheckins?scoreType=team&scoreLevel=bar&locationId=11&count=10
        // Returns: Player Info (email address, first name, last name), Team Name, Players’s Checkin Count
        $url = self::$HOT_SALSA_URL_GAME_CHECKINS . "?scoreType=team&scoreLevel=bar&locationId={$locationId}&count={$limit}";
        
        $data = self::makeHotSalsaRequest($url, $app);
        /* {
         *      "status":"success",
         *      "checkins":[
         *          { "players":[
         *              {"firstname":"Pavel",
         *              "lastName":"Goncharov",
         *              "email":"thundrax@gmail.com",
         *              "teamName":"Lotus",
         *              "checkinCount":"3"}],
         *          "teamName":"Super Villans",
         *          "checkinCount":"4"}]}
         *      ]
         * } */
        if($data && isset($data['checkins'])) {
            $results = array();
            foreach($data['checkins'] AS $team) {
                $name = (isset($team['teamName'])) ? $team['teamName'] : '';
                $mobile = (isset($team['checkinCount'])) ? $team['checkinCount'] : 0;
                $live = 0;
                        
                $results[] = array( 
                    'img' => '', 
                    'label' => $name, 
                    'mobileScore' => $mobile,
                    'liveScore' => $live
                );
            }
            return $app->render(200, array('leaderboard' => $results));
        } else {
            return $app->render(400,  array('msg' => 'Could not select Per Joint Team Checkins Leaderboard.'));
        }
    }
    
    static function getVenueLocalList($app) {
        $data = LeaderboardData::selectVenueList();
        
        if($data) {
            return $app->render(200, array('joints' => $data));
        } else {
            return $app->render(400,  array('msg' => 'Could not select list of joints.'));
        }
    }
    
    static function getVenueHotSalsaList($app) {
        // /locationList
        $url = self::$HOT_SALSA_URL_VENUE_LIST;
        
        $salsaData = self::makeHotSalsaRequest($url, $app);
        /*
        {  
            "status":"success",
            "addresses":[{  
               "sdkAddressId":"11",
               "name":"Bayou Cafe",
               "city":"Schenectady",
               "state":"NY",
               "address":"507 Saratoga Road",
               "postalCode":"12302",
               "country":"US",
               "image":"http://cfxcdnorigin.hotsalsainteractive.com/hotsalsainteractive/address/1459204914.png",
               "hasTrivia":1,
               "triviaDay":"Wednesday",
               "triviaTime":"7:00 PM"
            }]
         }
         */
        if($salsaData && isset($salsaData['addresses'])) {
            $results = array();
            foreach($salsaData['addresses'] AS $salsaVenue) {
                if(!isset($salsaVenue['sdkAddressId'])) {
                    break;
                }
                $results[] = array( 
                    'id' => $salsaVenue['sdkAddressId'],
                    'name' => $salsaVenue['name'],
                    'city' => $salsaVenue['city'],
                    'state' => $salsaVenue['state'],
                    'zip' => $salsaVenue['postalCode'],
                    'address' => $salsaVenue['address'],
                    'image' => $salsaVenue['image'],
                    'hasTrivia' => $salsaVenue['hasTrivia'],
                    'triviaDay' => $salsaVenue['triviaDay'],
                    'triviaTime' => $salsaVenue['triviaTime']
                );
            }
            return $app->render(200, array('joints' => $results));
        } else {
            return $app->render(400,  array('msg' => $salsaData));
        }
    }
}