<?php namespace API;
require_once dirname(__FILE__) . '/leaderboards.data.php';
 require_once dirname(dirname(dirname(__FILE__))) . '/config/config.php';
 require_once dirname(dirname(dirname(__FILE__))) . '/services/api.auth.php';

use \Respect\Validation\Validator as v;


class LeaderboardController {
    
    /* Global List of Teams API
     * /location/getTeamNames 
     *  
     * Per Joint List of Teams API
     * Input: locationId (optional)
     * /location/getTeamNames?locationId=11 
     */
    private static $HOT_SALSA_URL_TEAMS_LIST = '/location/getTeamNames';
    
    /* Global List of Joints API
     * /locationList 
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
     */
    private static $HOT_SALSA_URL_GAME_CHECKINS = '/location/getCheckins';
    
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
    
    private static function getHotSalsaLocationId($venueId) {
        // Leave for hooking into later
        return $venueId;
    }
    
    private static function getHotSalsaLocationIdForUserHomeJoint() {
        $loggedInUserId = APIAuth::getUserId();
        if(!$loggedInUserId) {
            return false; 
        }
        
        $homeJointName = LeaderboardData::getHomeJointForTeamByUserId($loggedInUserId);
        if(!$homeJointName) {
            return false; 
        }
        
        $salsaVenueData = self::getHotSalsaVenuesList();
        if(!$salsaVenueData || !isset($salsaVenueData["addresses"])) {
            return false; 
        }
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
        foreach($salsaVenueData["addresses"] AS $venue) {
            if($venue['name'] === $homeJointName) {
                return $venue['sdkAddressId'];
            }
        }
        return false;
    }
    
    
    // Global Player Score Leaderboard
    static function getGlobalPlayersLeaderboard($app, $count) {
        $limit = (!v::intVal()->validate($count)) ? '10' : $count;
        
        // /trivia/gameNight/cumilativeScore?scoreType=player&count=10&startDate= optional&endDate=optional
        $url = self::$HOT_SALSA_URL_MOBILE_SCORE . "?scoreType=player&count={$limit}";
        $salsaData = self::makeHotSalsaRequest($url);
        
        $results = array();
        $mergedUserIds = array();
        if(false && $salsaData && isset($salsaData['scores'])) {
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
            foreach($salsaData['scores'] AS $salsaPlayer) {
                $first = (isset($salsaPlayer['firstName'])) ? $salsaPlayer['firstName'] : '';
                $last = (isset($salsaPlayer['lastName'])) ? $salsaPlayer['lastName'] : '';
                $email = (isset($salsaPlayer['emailAddress'])) ? $salsaPlayer['emailAddress'] : '';
                
                $teamName = (isset($salsaPlayer['teamName'])) ? $salsaPlayer['teamName'] : '';
                $homeJoint = (isset($salsaPlayer['homeJoint'])) ? $salsaPlayer['homeJoint'] : '';
                
                $user = LeaderboardData::selectUserIdByEmail($email);
                $team = LeaderboardData::selectTeamLiveScoreByNameAndVenue($teamName, $homeJoint);
                if(!$team) {
                    $team = array();
                }
                        
                $result = array(
                    'mobileScore' => (isset($salsaPlayer['score'])) ? $salsaPlayer['score'] : 0,
                    'liveScore' => ($team && isset($team['score'])) ? $team['score'] : 0,
                    
                    'player' => "{$first} {$last}", 
                    'userId' => ($user && $user->id) ? $user->id : 0,
                    'hotSalsaUserId' => (isset($salsaPlayer['userId'])) ? $salsaPlayer['userId'] : 0,
                    'email' => $email, 
                    'img' => (isset($salsaPlayer['photoUser'])) ? $salsaPlayer['photoUser'] : '', 
                            
                    'teamName' => $teamName,
                    'teamId' => ($team && isset($team['teamId'])) ? $team['teamId'] : 0, 
                    'hotSalsaTeamId' => (isset($salsaPlayer['teamId'])) ? $salsaPlayer['teamId'] : 0,
                            
                    'homeJoint' => $homeJoint,
                    'homeJointId' => ($team && isset($team['homeVenueId'])) ? $team['homeVenueId'] : 0,
                    'hotSalsaHomeJointId' => (isset($salsaPlayer['jointId'])) ? $salsaPlayer['jointId'] : 0
                );
                    
                $result['sort'] = ($result['mobileScore'] > $result['liveScore']) ? $result['mobileScore'] : $result['liveScore'];
                
                if($result['userId'] > 0) {
                    $mergedUserIds[] = $result['userId'];
                }
                
                $results[] = $result;
            }
        }
        
        $localData = LeaderboardData::selectPlayerScoreLeaderboards($count, $mergedUserIds);
        if($localData) {
            foreach($localData AS $localPlayer) {
                $result = array(
                    'mobileScore' => 0,
                    'liveScore' => $localPlayer->score,
                    'player' => $localPlayer->firstName . " " . $localPlayer->lastName,
                    'userId' => $localPlayer->userId,
                    'hotSalsaUserId' => 0,
                    'email' => $localPlayer->email,
                    'img' => '',
                    'teamName' => $localPlayer->teamName,
                    'teamId' => $localPlayer->teamId,
                    'hotSalsaTeamId' => 0,
                    'homeJoint' => $localPlayer->homeJoint,
                    'homeJointId' => $localPlayer->homeJointId,
                    'hotSalsaHomeJointId' => 0,
                    'sort' => $localPlayer->score
                );
                $results[] = $result;
            }
        }
            
        if(count($results) > 0) {
            return $app->render(200, array('leaderboard' => $results));
        } else {
            return $app->render(400,  array('msg' => 'Could not select Global Player Score Leaderboard.'));
        }
    }

    // Global Team Score Leaderboard
    static function getGlobalTeamsLeaderboard($app, $count) {
        $limit = (!v::intVal()->validate($count)) ? '10' : $count;
        
        // /trivia/gameNight/cumilativeScore?scoreType=team&count=10&startDate= optional&endDate=optional 
        $url = self::$HOT_SALSA_URL_MOBILE_SCORE . "?scoreType=team&count={$limit}";
        
        $salsaData = self::makeHotSalsaRequest($url);
        $results = array();
        $mergedTeamIds = array();
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
            foreach($salsaData['scores'] AS $salsaTeam) {
                $teamName = (isset($salsaTeam['name'])) ? $salsaTeam['name'] : '';
                $homeJoint = (isset($salsaTeam['homeJoint'])) ? $salsaTeam['homeJoint'] : '';
                
                $team = LeaderboardData::selectTeamLiveScoreByNameAndVenue($teamName, $homeJoint);
                if(!$team) {
                    $team = array();
                }
                
                $result = array( 
                    'mobileScore' => (isset($salsaTeam['score'])) ? $salsaTeam['score'] : 0,
                    'liveScore' => ($team && isset($team['score'])) ? $team['score'] : 0,
                    
                    'teamName' => $teamName,
                    'teamId' => ($team && isset($team['teamId'])) ? $team['teamId'] : 0,
                    'hotSalsaTeamId' => (isset($salsaTeam['teamId'])) ? $salsaTeam['teamId'] : 0,
                    
                    'homeJoint' => $homeJoint,
                    'homeJointId' => ($team && isset($team['homeVenueId'])) ? $team['homeVenueId'] : 0,
                    'hotSalsaHomeJointId' => (isset($salsaTeam['jointId'])) ? $salsaTeam['jointId'] : 0
                );
                    
                $result['sort'] = ($result['mobileScore'] > $result['liveScore']) ? $result['mobileScore'] : $result['liveScore'];
                
                if($result['teamId'] > 0) {
                    $mergedTeamIds[] = $result['teamId'];
                }
                
                $results[] = $result;
            }
        }
                
        $localData = LeaderboardData::selectTeamScoreLeaderboards($count, $mergedTeamIds);
        if($localData) {
            foreach($localData AS $localTeam) {
                $result = array(
                    'mobileScore' => 0,
                    'liveScore' => $localTeam->score,
                    'teamName' => $localTeam->teamName,
                    'teamId' => $localTeam->teamId,
                    'hotSalsaTeamId' => 0,
                    'homeJoint' => $localTeam->homeJoint,
                    'homeJointId' => $localTeam->homeJointId,
                    'hotSalsaHomeJointId' => 0,
                    'sort' => $localTeam->score
                );
                $results[] = $result;
            }
        }
            
        if(count($results) > 0) {
            return $app->render(200, array('leaderboard' => $results));
        } else {
            return $app->render(400,  array('msg' => 'Could not select Global Team Score Leaderboard.'));
        }
    }

    // Per Joint Player Score Leaderboard
    static function getVenuePlayersLeaderboard($app, $venueId, $count) {
        if(!v::intVal()->validate($venueId)) {
            return $app->render(400,  array('msg' => 'Invalid Joint ID. Check your parameters and try again.'));
        } elseif ($venueId === '0') {
            $venueId = self::getHotSalsaLocationIdForUserHomeJoint();
            
            if(!$venueId) {
                return $app->render(400,  array('msg' => 'Invalid ID. Check your parameters and try again.'));
            }
        }
        
        $limit = (!v::intVal()->validate($count)) ? '10' : $count;
        
        $locationId = self::getHotSalsaLocationId($venueId);
        
        // /trivia/gameNight/cumilativeScore?scoreType=player&scoreLevel=bar&locationId=11&count=10&startDate=optional&endDate=optional
        $url = self::$HOT_SALSA_URL_MOBILE_SCORE . "?scoreType=player&scoreLevel=bar&locationId={$locationId}&count={$limit}";
        
        $salsaData = self::makeHotSalsaRequest($url);
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
                        
                $user = LeaderboardData::selectUserIdByEmail($email);
                $team = LeaderboardData::selectTeamLiveScoreByNameAndVenue($teamName, $homeJoint);
                if(!$team) {
                    $team = array();
                }
                        
                $results[] = array(
                    'mobileScore' => (isset($salsaPlayer['score'])) ? $salsaPlayer['score'] : 0,
                    'liveScore' => ($team && isset($team['score'])) ? $team['score'] : 0,
                    
                    'player' => "{$first} {$last}", 
                    'userId' => ($user && $user->id) ? $user->id : 0,
                    'hotSalsaUserId' => (isset($salsaPlayer['userId'])) ? $salsaPlayer['userId'] : 0,
                    'email' => $email, 
                    'img' => (isset($salsaPlayer['photoUser'])) ? $salsaPlayer['photoUser'] : '', 
                            
                    'teamName' => $teamName,
                    'teamId' => ($team && isset($team['teamId'])) ? $team['teamId'] : 0, 
                    'hotSalsaTeamId' => (isset($salsaPlayer['teamId'])) ? $salsaPlayer['teamId'] : 0,
                            
                    'homeJoint' => $homeJoint,
                    'homeJointId' => ($team && isset($team['homeVenueId'])) ? $team['homeVenueId'] : 0,
                    'hotSalsaHomeJointId' => (isset($salsaPlayer['jointId'])) ? $salsaPlayer['jointId'] : 0
                );
            }
            return $app->render(200, array('leaderboard' => $results));
        } else {
            return $app->render(400,  array('msg' => 'Could not select Per Joint Player Score Leaderboard.'));
        }
    }

    // Per Joint Team Score Leaderboard
    static function getVenueTeamsLeaderboard($app, $venueId, $count) {
        if(!v::intVal()->validate($venueId)) {
            return $app->render(400,  array('msg' => 'Invalid Joint ID. Check your parameters and try again.'));
        } elseif ($venueId === '0') {
            $venueId = self::getHotSalsaLocationIdForUserHomeJoint();
            
            if(!$venueId) {
                return $app->render(400,  array('msg' => 'Invalid ID. Check your parameters and try again.'));
            }
        }
                
        $limit = (!v::intVal()->validate($count)) ? '10' : $count;
        
        $locationId = self::getHotSalsaLocationId($venueId);
        
        // /trivia/gameNight/cumilativeScore?scoreType=team&scoreLevel=bar&locationId=11&count=10&startDate= optional&endDate=optional
        $url = self::$HOT_SALSA_URL_MOBILE_SCORE . "?scoreType=team&scoreLevel=bar&locationId={$locationId}&count={$limit}";
        
        $salsaData = self::makeHotSalsaRequest($url);
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
        $url = self::$HOT_SALSA_URL_GAME_CHECKINS . "?scoreType=player&count={$limit}";
        $salsaData = self::makeHotSalsaRequest($url);
        /* {  
            "status":"success",
            "checkins":[  
               {  
                  "userId":"1688",
                  "confirmed":"1",
                  "lastName":"Goncharov",
                  "firstName":"Pavel",
                  "emailAddress":"thundrax@gmail.com",
                  "facebookId":"10207653889717859",
                  "teamName":"Lotus",
                  "photoUser":"http://cfxcdnorigin.hotsalsainteractive.com/hotsalsainteractive/userPhoto/1458942803.png",
                  "hasPhoto":0,
                  "upgraded":0,
                  "checkinCount":"3"
               }
            ]
         } */
        if($salsaData && isset($salsaData['checkins'])) {
            $results = array();
            foreach($salsaData['checkins'] AS $salsaPlayer) {
                $first = (isset($salsaPlayer['firstName'])) ? $salsaPlayer['firstName'] : '';
                $last = (isset($salsaPlayer['lastName'])) ? $salsaPlayer['lastName'] : '';
                $email = (isset($salsaPlayer['emailAddress'])) ? $salsaPlayer['emailAddress'] : '';
                
                $teamName = (isset($salsaPlayer['teamName'])) ? $salsaPlayer['teamName'] : '';
                $homeJoint = (isset($salsaPlayer['homeJoint'])) ? $salsaPlayer['homeJoint'] : '';
                        
                $user = LeaderboardData::selectUserIdByEmail($email);
                $team = LeaderboardData::selectTeamLiveCheckinsByNameAndVenue($teamName, $homeJoint);
                if(!$team) {
                    $team = array();
                }
                        
                $results[] = array( 
                    'mobileCheckins' => (isset($salsaPlayer['checkinCount'])) ? $salsaPlayer['checkinCount'] : 0,
                    'liveCheckins' => ($team && isset($team['checkins'])) ? $team['checkins'] : 0,
                    
                    'player' => "{$first} {$last}", 
                    'userId' => ($user && $user->id) ? $user->id : 0,
                    'hotSalsaUserId' => (isset($salsaPlayer['userId'])) ? $salsaPlayer['userId'] : 0,
                    'email' => $email, 
                    'img' => (isset($salsaPlayer['photoUser'])) ? $salsaPlayer['photoUser'] : '', 
                            
                    'teamName' => $teamName,
                    'teamId' => ($team && isset($team['teamId'])) ? $team['teamId'] : 0, 
                    'hotSalsaTeamId' => (isset($salsaPlayer['teamId'])) ? $salsaPlayer['teamId'] : 0,
                            
                    'homeJoint' => $homeJoint,
                    'homeJointId' => ($team && isset($team['homeVenueId'])) ? $team['homeVenueId'] : 0,
                    'hotSalsaHomeJointId' => (isset($salsaPlayer['jointId'])) ? $salsaPlayer['jointId'] : 0
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
        $url = self::$HOT_SALSA_URL_GAME_CHECKINS . "?scoreType=team&count={$limit}";
        $data = self::makeHotSalsaRequest($url);
        /* { 
            "status":"success",
            "checkins":[  
               {  
                  "players":[  
                     {  
                        "userId":"1033",
                        "confirmed":"0",
                        "lastName":"Test",
                        "firstName":"John",
                        "emailAddress":"John@test.go",
                        "facebookId":"",
                        "teamName":"Lotus",
                        "photoUser":"http://cfxcdnorigin.hotsalsainteractive.com/hotsalsainteractive/userPhoto/1458942803.png",
                        "hasPhoto":0,
                        "upgraded":0
                     }
                  ],
                  "teamName":"Lotus",
                  "checkinCount":"3"
               }]
         } */
        if($data && isset($data['checkins'])) {
            $results = array();
            foreach($data['checkins'] AS $salsaTeam) {
                $teamName = (isset($salsaTeam['teamName'])) ? $salsaTeam['teamName'] : '';
                $homeJoint = (isset($salsaTeam['homeJoint'])) ? $salsaTeam['homeJoint'] : '';
                
                $team = LeaderboardData::selectTeamLiveCheckinsByNameAndVenue($teamName, $homeJoint);
                if(!$team) {
                    $team = array();
                }
                
                $results[] = array( 
                    'mobileCheckins' => (isset($salsaTeam['checkinCount'])) ? $salsaTeam['checkinCount'] : 0,
                    'liveCheckins' => ($team && isset($team['checkins'])) ? $team['checkins'] : 0,
                    
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
            return $app->render(400,  array('msg' => 'Could not select Global Team Checkin Leaderboard.'));
        }
    }

    // Per Joint Player Checkins Leaderboard
    static function getVenuePlayerCheckinsLeaderboard($app, $venueId, $count) {
        if(!v::intVal()->validate($venueId)) {
            return $app->render(400,  array('msg' => 'Invalid Joint ID. Check your parameters and try again.'));
        } elseif ($venueId === '0') {
            $venueId = self::getHotSalsaLocationIdForUserHomeJoint();
            
            if(!$venueId) {
                return $app->render(400,  array('msg' => 'Invalid ID. Check your parameters and try again.'));
            }
        }
        
        $limit = (!v::intVal()->validate($count)) ? '10' : $count;
        
        $locationId = self::getHotSalsaLocationId($venueId);
        
        // /location/getCheckins?scoreType=player&scoreLevel=bar&locationId=11&count=10
        $url = self::$HOT_SALSA_URL_GAME_CHECKINS . "?scoreType=player&scoreLevel=bar&locationId={$locationId}&count={$limit}";
        
        $salsaData = self::makeHotSalsaRequest($url);
        /* {  
            "status":"success",
            "checkins":[  
               {  
                  "userId":"1688",
                  "confirmed":"1",
                  "lastName":"Goncharov",
                  "firstName":"Pavel",
                  "emailAddress":"thundrax@gmail.com",
                  "facebookId":"10207653889717859",
                  "teamName":"Lotus",
                  "photoUser":"http://cfxcdnorigin.hotsalsainteractive.com/hotsalsainteractive/userPhoto/1458942803.png",
                  "hasPhoto":0,
                  "upgraded":0,
                  "checkinCount":"3"
               },
            ]
         } */
        if($salsaData && isset($salsaData['checkins'])) {
            $results = array();
            foreach($salsaData['checkins'] AS $salsaPlayer) {
                $first = (isset($salsaPlayer['firstName'])) ? $salsaPlayer['firstName'] : '';
                $last = (isset($salsaPlayer['lastName'])) ? $salsaPlayer['lastName'] : '';
                $email = (isset($salsaPlayer['emailAddress'])) ? $salsaPlayer['emailAddress'] : '';
                
                $teamName = (isset($salsaPlayer['teamName'])) ? $salsaPlayer['teamName'] : '';
                $homeJoint = (isset($salsaPlayer['homeJoint'])) ? $salsaPlayer['homeJoint'] : '';
                        
                $user = LeaderboardData::selectUserIdByEmail($email);
                $team = LeaderboardData::selectTeamLiveCheckinsByNameAndVenue($teamName, $homeJoint);
                if(!$team) {
                    $team = array();
                }
                        
                $results[] = array( 
                    'mobileCheckins' => (isset($salsaPlayer['checkinCount'])) ? $salsaPlayer['checkinCount'] : 0,
                    'liveCheckins' => ($team && isset($team['checkins'])) ? $team['checkins'] : 0,
                    
                    'player' => "{$first} {$last}", 
                    'userId' => ($user && $user->id) ? $user->id : 0,
                    'hotSalsaUserId' => (isset($salsaPlayer['userId'])) ? $salsaPlayer['userId'] : 0,
                    'email' => $email, 
                    'img' => (isset($salsaPlayer['photoUser'])) ? $salsaPlayer['photoUser'] : '', 
                            
                    'teamName' => $teamName,
                    'teamId' => ($team && isset($team['teamId'])) ? $team['teamId'] : 0, 
                    'hotSalsaTeamId' => (isset($salsaPlayer['teamId'])) ? $salsaPlayer['teamId'] : 0,
                            
                    'homeJoint' => $homeJoint,
                    'homeJointId' => ($team && isset($team['homeVenueId'])) ? $team['homeVenueId'] : 0,
                    'hotSalsaHomeJointId' => (isset($salsaPlayer['jointId'])) ? $salsaPlayer['jointId'] : 0
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
        } elseif ($venueId === '0') {
            $venueId = self::getHotSalsaLocationIdForUserHomeJoint();
            
            if(!$venueId) {
                return $app->render(400,  array('msg' => 'Invalid ID. Check your parameters and try again.'));
            }
        }
        
        $limit = (!v::intVal()->validate($count)) ? '10' : $count;
        
        $locationId = self::getHotSalsaLocationId($venueId);
        
        // /location/getCheckins?scoreType=team&scoreLevel=bar&locationId=11&count=10
        $url = self::$HOT_SALSA_URL_GAME_CHECKINS . "?scoreType=team&scoreLevel=bar&locationId={$locationId}&count={$limit}";
        
        $salsaData = self::makeHotSalsaRequest($url);
        /* {  
            "status":"success",
            "checkins":[  
               {  
                  "players":[  
                     {  
                        "userId":"1033",
                        "confirmed":"0",
                        "lastName":"Test",
                        "firstName":"John",
                        "emailAddress":"John@test.go",
                        "facebookId":"",
                        "teamName":"Lotus",
                        "photoUser":"http://cfxcdnorigin.hotsalsainteractive.com/hotsalsainteractive/userPhoto/1458942803.png",
                        "hasPhoto":0,
                        "upgraded":0
                     }
                  ],
                  "teamName":"Lotus",
                  "checkinCount":"3"
        }] */
        if($salsaData && isset($salsaData['checkins'])) {
            $results = array();
            foreach($salsaData['checkins'] AS $salsaTeam) {
                $teamName = (isset($salsaTeam['teamName'])) ? $salsaTeam['teamName'] : '';
                $homeJoint = (isset($salsaTeam['homeJoint'])) ? $salsaTeam['homeJoint'] : '';
                
                $team = LeaderboardData::selectTeamLiveCheckinsByNameAndVenue($teamName, $homeJoint);
                if(!$team) {
                    $team = array();
                }
                
                $results[] = array( 
                    'mobileCheckins' => (isset($salsaTeam['checkinCount'])) ? $salsaTeam['checkinCount'] : 0,
                    'liveCheckins' => ($team && isset($team['checkins'])) ? $team['checkins'] : 0,
                    
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
            return $app->render(400,  array('msg' => 'Could not select Per Joint Team Checkins Leaderboard.'));
        }
    }
    
    /* Venue Lists */
    
    static function getMergedVenuesList($app) {
        $localData = LeaderboardData::selectVenueList();
        $salsaData = self::makeHotSalsaRequest(self::$HOT_SALSA_URL_VENUE_LIST);
        if (!$localData && !$salsaData) {
            return $app->render(400,  array('msg' => 'Could not select list of joints.'));
        } else if (!$localData) {
            return $app->render(200, array('joints' => $salsaData));            
        } else if (!$salsaData) {
            return $app->render(200, array('joints' => $localData));            
        }
        
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
        
        return DBConn::selectAll("SELECT v.id, v.name, v.address, "
                . "v.address_b AS addressb, v.city, v.state, v.zip "
                . "FROM " . DBConn::prefix() . "venues AS v "
                . "ORDER BY v.state, v.city, v.name;"
         */
                
        $results = array();
        foreach($salsaData['addresses'] AS $salsaVenue) {
            if(!isset($salsaVenue['sdkAddressId'])) {
                break;
            }
            
            
            for($i = 0; $i < count($localData); $i++) {
                if($localData[$i]['name'] === $salsaVenue['name']) {
                    $foundLocalVenue = $localData[$i];
                    unset($localData[$i]);
                    break;
                }
            }
            
            $results[] = array( 
                'salsaUserId' => $salsaVenue['sdkAddressId'],
                'localUserId' => (isset($foundLocalVenue['id'])) ? $foundLocalVenue['id'] : '0',
                'name' => $salsaVenue['name'],
                'city' => $salsaVenue['city'],
                'state' => $salsaVenue['state'],
                'zip' => $salsaVenue['postalCode'],
                'address' => $salsaVenue['address']
            );
        }
        return $app->render(200, array('joints' => $results));
    }
    
    static function getLocalVenuesList($app) {
        $data = LeaderboardData::selectVenueList();
        if($data) {
            return $app->render(200, array('joints' => $data));
        } else {
            return $app->render(400,  array('msg' => 'Could not select list of joints.'));
        }
    }
    
    static function getHotSalsaVenuesList($app) {
        // /locationList
        $salsaData = self::makeHotSalsaRequest(self::$HOT_SALSA_URL_VENUE_LIST);
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
            return $app->render(400,  array('msg' => 'Could not select Hot Salsa Venue List.'));
        }
    }
    
    private static function getHotSalsaTeamsList() {
        // /locationList
        $url = self::$HOT_SALSA_URL_TEAMS_LIST;
        
        $salsaData = self::makeHotSalsaRequest($url);
        /*{  
            "status":"success",
            "teamNames":[{  
                "teamName":"Super Villans",
                "locationName":"The Saratoga Winery",
                "locationId":"12",
                "players":[{  
                    "firstname":"Testle",
                    "lastName":"Testerton",
                    "email":"test@test.test",
                    "teamName":"Super Villans",
                    "id":"1000",
                    "image":""
                }]
            }]
        } */
        if($salsaData && isset($salsaData['teamNames'])) {
            return $salsaData['teamNames'];
        } else {
            return false;
        }
    }
}