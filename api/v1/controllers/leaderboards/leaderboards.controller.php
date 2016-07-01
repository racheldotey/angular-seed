<?php namespace API;
require_once dirname(__FILE__) . '/leaderboards.data.php';
 require_once dirname(dirname(dirname(__FILE__))) . '/config/config.php';
 require_once dirname(dirname(dirname(__FILE__))) . '/services/api.auth.php';
 require_once dirname(dirname(dirname(__FILE__))) . '/services/HotSalsaRequest.php';

use \Respect\Validation\Validator as v;


class LeaderboardController {
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
        
        $salsaVenueData = HotSalsaRequest::makeRequest(HotSalsaRequest::$HOT_SALSA_URL_VENUE_LIST);
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
    
    static function CustomeCompareOnSortColumn($a, $b) {
        return ((float)$b['sort'] - (float)$a['sort']);
    }
    
    static private function sortAndTrimLeaderboardResults($leaderboard, $limit) {
        usort($leaderboard, array('API\LeaderboardController', 'CustomeCompareOnSortColumn'));
        return array_slice($leaderboard, 0, (int)$limit);
    }
    
    // Global Player Score Leaderboard
    static function getGlobalPlayersLeaderboard($app) {
        $limit = (!v::key('limit', v::intVal())->validate($app->request->get())) ? 10 : $app->request->get('limit');
        $startDate = (!v::key('startDate', v::date('Y-m-d'))->validate($app->request->get())) ? false : $app->request->get('startDate');
        $endDate = (!v::key('endDate', v::date('Y-m-d'))->validate($app->request->get())) ? false : $app->request->get('endDate');
                
        // /trivia/gameNight/cumilativeScore?scoreType=player&count=10&startDate= optional&endDate=optional
        $url = HotSalsaRequest::$HOT_SALSA_URL_MOBILE_SCORE . "?scoreType=player&count={$limit}";
        if($startDate) { $url = $url . "&startDate={$startDate}"; }
        if($endDate) { $url = $url . "&endDate={$endDate}"; }
        // CURL Hot Salsa
        $salsaData = HotSalsaRequest::makeRequest($url);
        
        $results = array();
        $mergedUserIds = array();
        if($salsaData && isset($salsaData['scores'])) {
            /* {
            "status":"success",
            "scores":[ {
                "name":"billman_c@hotmail.com",
                "userId":"1770",
                "score":"783",
                "confirmed":"0",
                "lastName":"Rustad",
                "firstName":"Conrad",
                "emailAddress":"billman_c@hotmail.com",
                "facebookId":"10154054462631407",
                "teamName":"Super Villans",
                "photoUser":"http://cfxcdnorigin.hotsalsainteractive.com/hotsalsainteractive/userPhoto/1463168056.png",
                "hasPhoto":1,
                "upgraded":1,
                "addresses": {
                    "sdkAddressId":"12",
                    "name":"The Saratoga Winery",
                    "city":"Saratoga Springs",
                    "state":"NY",
                    "address":"462 New York 29",
                    "postalCode":"12866",
                    "country":"US",
                    "image":"http://cfxcdnorigin.hotsalsainteractive.com/hotsalsainteractive/address/1459206559.png",
                    "hasTrivia":1,
                    "triviaDay":"Thursday",
                    "triviaTime":"7:00 PM"
                }
                }
                ]
            } */
            foreach($salsaData['scores'] AS $salsaPlayer) {
                $first = (isset($salsaPlayer['firstName'])) ? $salsaPlayer['firstName'] : '';
                $last = (isset($salsaPlayer['lastName'])) ? $salsaPlayer['lastName'] : '';
                $email = (isset($salsaPlayer['emailAddress'])) ? $salsaPlayer['emailAddress'] : '';
                
                $teamName = (isset($salsaPlayer['teamName'])) ? $salsaPlayer['teamName'] : '';
                $homeJoint = (isset($salsaPlayer['addresses']) && isset($salsaPlayer['addresses']['name'])) ? 
                        $salsaPlayer['addresses']['name'] : '';
                
                $user = LeaderboardData::selectUserIdByEmail($email);
                $team = LeaderboardData::selectTeamLiveScoreByNameAndVenue($teamName, $homeJoint, $startDate, $endDate);
                        
                $result = array(
                    'mobileScore' => (isset($salsaPlayer['score'])) ? $salsaPlayer['score'] : 0,
                    'liveScore' => ($team) ? $team['score'] : 0,
                    
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
                    
                $result['sort'] = (float)$result['mobileScore'] + (float)$result['liveScore'];
                
                if($result['userId'] > 0) {
                    $mergedUserIds[] = $result['userId'];
                }
                
                $results[] = $result;
            }
        }
        
        $localData = LeaderboardData::selectPlayerScoreLeaderboards($limit, $startDate, $endDate, $mergedUserIds);
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
            $leaderboard = self::sortAndTrimLeaderboardResults($results, $limit);
            return $app->render(200, array('leaderboard' => $leaderboard, 'startDate' => $startDate, 'endDate' => $endDate));
        } else {
            return $app->render(400,  array('msg' => 'Could not select Global Player Score Leaderboard.', 'url' => $url));
        }
    }

    // Global Team Score Leaderboard
    static function getGlobalTeamsLeaderboard($app) {
        $limit = (!v::key('limit', v::intVal())->validate($app->request->get())) ? 10 : $app->request->get('limit');
        $startDate = (!v::key('startDate', v::date('Y-m-d'))->validate($app->request->get())) ? false : $app->request->get('startDate');
        $endDate = (!v::key('endDate', v::date('Y-m-d'))->validate($app->request->get())) ? false : $app->request->get('endDate');
        
        // /trivia/gameNight/cumilativeScore?scoreType=team&count=10&startDate= optional&endDate=optional 
        $url = HotSalsaRequest::$HOT_SALSA_URL_MOBILE_SCORE . "?scoreType=team&count={$limit}";
        if($startDate) { $url = $url . "&startDate={$startDate}"; }
        if($endDate) { $url = $url . "&endDate={$endDate}"; }
        // CURL Hot Salsa
        $salsaData = HotSalsaRequest::makeRequest($url);
        $results = array();
        $mergedTeamIds = array();
        if($salsaData && isset($salsaData['scores'])) {
            /* {
            "status":"success",
            "scores":[{
                "name":"Norse Gods",
                "score":"101",
                "addresses":{
                    "sdkAddressId":"2",
                    "name":"British Open English Pub",
                    "city":"Scottsdale",
                    "state":"AZ",
                    "address":"1334 North Scottsdale Road",
                    "postalCode":"85257",
                    "country":"US",
                    "image":"http://cfxcdnorigin.hotsalsainteractive.com/hotsalsainteractive/address/1458596177.png",
                    "hasTrivia":1,
                    "triviaDay":"Friday",
                    "triviaTime":"8:00 PM"
                },
                "players":[{
                    "userId":"959",
                    "confirmed":"1",
                    "lastName":"Goncharov",
                    "firstName":"Pavel",
                    "emailAddress":"pgonch1@gmail.com",
                    "facebookId":"",
                    "teamName":"Norse Gods",
                    "photoUser":"http://cfxcdnorigin.hotsalsainteractive.com/hotsalsainteractive/userPhoto/1463161893.png",
                    "hasPhoto":1,
                    "upgraded":1
                }]
            }]
        } */
            foreach($salsaData['scores'] AS $salsaTeam) {
                $teamName = (isset($salsaTeam['name'])) ? $salsaTeam['name'] : '';
                $homeJoint = (isset($salsaTeam['addresses']) && isset($salsaTeam['addresses']['name'])) ? 
                        $salsaTeam['addresses']['name'] : '';
                
                $team = LeaderboardData::selectTeamLiveScoreByNameAndVenue($teamName, $homeJoint, $startDate, $endDate);
                
                $result = array( 
                    'mobileScore' => (isset($salsaTeam['score'])) ? $salsaTeam['score'] : 0,
                    'liveScore' => ($team) ? $team['score'] : 0,
                    
                    'teamName' => $teamName,
                    'teamId' => ($team) ? $team['teamId'] : 0,
                    'hotSalsaTeamId' => (isset($salsaTeam['teamId'])) ? $salsaTeam['teamId'] : 0,
                    
                    'homeJoint' => $homeJoint,
                    'homeJointId' => ($team) ? $team['homeVenueId'] : 0,
                    'hotSalsaHomeJointId' => (isset($salsaTeam['jointId'])) ? $salsaTeam['jointId'] : 0
                );
                    
                $result['sort'] = (float)$result['mobileScore'] + (float)$result['liveScore'];
                
                if($result['teamId'] > 0) {
                    $mergedTeamIds[] = $result['teamId'];
                }
                
                $results[] = $result;
            }
        }
                
        $localData = LeaderboardData::selectTeamScoreLeaderboards($limit, $startDate, $endDate, $mergedTeamIds);
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
            $leaderboard = self::sortAndTrimLeaderboardResults($results, $limit);
            return $app->render(200, array('leaderboard' => $leaderboard, 'startDate' => $startDate, 'endDate' => $endDate));
        } else {
            return $app->render(400,  array('msg' => 'Could not select Global Team Score Leaderboard.', 'url' => $url));
        }
    }

    // Per Joint Player Score Leaderboard
    static function getVenuePlayersLeaderboard($app, $venueId) {
        if(!v::intVal()->validate($venueId)) {
            return $app->render(400,  array('msg' => 'Invalid Joint ID. Check your parameters and try again.'));
        } elseif ($venueId === '0') {
            $venueId = self::getHotSalsaLocationIdForUserHomeJoint();
            
            if(!$venueId) {
                return $app->render(400,  array('msg' => 'Invalid ID. Check your parameters and try again.'));
            }
        }
        
        $limit = (!v::key('limit', v::intVal())->validate($app->request->get())) ? 10 : $app->request->get('limit');
        $startDate = (!v::key('startDate', v::date('Y-m-d'))->validate($app->request->get())) ? false : $app->request->get('startDate');
        $endDate = (!v::key('endDate', v::date('Y-m-d'))->validate($app->request->get())) ? false : $app->request->get('endDate');
        
        $locationId = self::getHotSalsaLocationId($venueId);
        
        // /trivia/gameNight/cumilativeScore?scoreType=player&scoreLevel=bar&locationId=11&count=10&startDate=optional&endDate=optional
        $url = HotSalsaRequest::$HOT_SALSA_URL_MOBILE_SCORE . "?scoreType=player&scoreLevel=bar&locationId={$locationId}&count={$limit}";
        if($startDate) { $url = $url . "&startDate={$startDate}"; }
        if($endDate) { $url = $url . "&endDate={$endDate}"; }
        // CURL Hot Salsa
        $salsaData = HotSalsaRequest::makeRequest($url);
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
                $homeJoint = (isset($salsaPlayer['addresses']) && isset($salsaPlayer['addresses']['name'])) ? 
                        $salsaPlayer['addresses']['name'] : '';
                        
                $user = LeaderboardData::selectUserIdByEmail($email);
                $team = LeaderboardData::selectTeamLiveScoreByNameAndVenue($teamName, $homeJoint, $startDate, $endDate);
                        
                $results[] = array(
                    'mobileScore' => (isset($salsaPlayer['score'])) ? $salsaPlayer['score'] : 0,
                    'liveScore' => ($team) ? $team['score'] : 0,
                    
                    'player' => "{$first} {$last}", 
                    'userId' => ($user && $user->id) ? $user->id : 0,
                    'hotSalsaUserId' => (isset($salsaPlayer['userId'])) ? $salsaPlayer['userId'] : 0,
                    'email' => $email, 
                    'img' => (isset($salsaPlayer['photoUser'])) ? $salsaPlayer['photoUser'] : '', 
                            
                    'teamName' => $teamName,
                    'teamId' => ($team) ? $team['teamId'] : 0, 
                    'hotSalsaTeamId' => (isset($salsaPlayer['teamId'])) ? $salsaPlayer['teamId'] : 0,
                            
                    'homeJoint' => $homeJoint,
                    'homeJointId' => ($team) ? $team['homeVenueId'] : 0,
                    'hotSalsaHomeJointId' => (isset($salsaPlayer['jointId'])) ? $salsaPlayer['jointId'] : 0
                );
            }
            return $app->render(200, array('leaderboard' => $results, 'startDate' => $startDate, 'endDate' => $endDate));
        } else {
            return $app->render(400,  array('msg' => 'Could not select Per Joint Player Score Leaderboard.', 'url' => $url));
        }
    }

    // Per Joint Team Score Leaderboard
    static function getVenueTeamsLeaderboard($app, $venueId) {
        if(!v::intVal()->validate($venueId)) {
            return $app->render(400,  array('msg' => 'Invalid Joint ID. Check your parameters and try again.'));
        } elseif ($venueId === '0') {
            $venueId = self::getHotSalsaLocationIdForUserHomeJoint();
            
            if(!$venueId) {
                return $app->render(400,  array('msg' => 'Invalid ID. Check your parameters and try again.'));
            }
        }
                
        $limit = (!v::key('limit', v::intVal())->validate($app->request->get())) ? 10 : $app->request->get('limit');
        $startDate = (!v::key('startDate', v::date('Y-m-d'))->validate($app->request->get())) ? false : $app->request->get('startDate');
        $endDate = (!v::key('endDate', v::date('Y-m-d'))->validate($app->request->get())) ? false : $app->request->get('endDate');
        
        $locationId = self::getHotSalsaLocationId($venueId);
        
        // /trivia/gameNight/cumilativeScore?scoreType=team&scoreLevel=bar&locationId=11&count=10&startDate= optional&endDate=optional
        $url = HotSalsaRequest::$HOT_SALSA_URL_MOBILE_SCORE . "?scoreType=team&scoreLevel=bar&locationId={$locationId}&count={$limit}";
        if($startDate) { $url = $url . "&startDate={$startDate}"; }
        if($endDate) { $url = $url . "&endDate={$endDate}"; }
        // CURL Hot Salsa
        $salsaData = HotSalsaRequest::makeRequest($url);
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
                
                $team = LeaderboardData::selectTeamLiveScoreByNameAndVenue($teamName, $homeJoint, $startDate, $endDate);
                
                $results[] = array( 
                    'mobileScore' => (isset($salsaTeam['score'])) ? $salsaTeam['score'] : 0,
                    'liveScore' => ($team) ? $team['score'] : 0,
                    
                    'teamName' => $teamName,
                    'teamId' => ($team) ? $team['teamId'] : 0,
                    'hotSalsaTeamId' => (isset($salsaTeam['teamId'])) ? $salsaTeam['teamId'] : 0,
                    
                    'homeJoint' => $homeJoint,
                    'homeJointId' => ($team) ? $team['homeVenueId'] : 0,
                    'hotSalsaHomeJointId' => (isset($salsaTeam['jointId'])) ? $salsaTeam['jointId'] : 0
                );
            }
            return $app->render(200, array('leaderboard' => $results, 'startDate' => $startDate, 'endDate' => $endDate));
        } else {
            return $app->render(400,  array('msg' => 'Could not select Per Joint Team Score Leaderboard.', 'url' => $url));
        }
    }
    
    // Global Player Checkin Leaderboard
    static function getGlobalPlayerCheckinsLeaderboard($app) {
        $limit = (!v::key('limit', v::intVal())->validate($app->request->get())) ? 10 : $app->request->get('limit');
        $startDate = (!v::key('startDate', v::date('Y-m-d'))->validate($app->request->get())) ? false : $app->request->get('startDate');
        $endDate = (!v::key('endDate', v::date('Y-m-d'))->validate($app->request->get())) ? false : $app->request->get('endDate');
        
        // /location/getCheckins?scoreType=player&count=10
        $url = HotSalsaRequest::$HOT_SALSA_URL_GAME_CHECKINS . "?scoreType=player&count={$limit}";
        if($startDate) { $url = $url . "&startDate={$startDate}"; }
        if($endDate) { $url = $url . "&endDate={$endDate}"; }
        // CURL Hot Salsa
        $salsaData = HotSalsaRequest::makeRequest($url);
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
        $results = array();
        $mergedUserIds = array();
        if($salsaData && isset($salsaData['checkins'])) {
            foreach($salsaData['checkins'] AS $salsaPlayer) {
                $first = (isset($salsaPlayer['firstName'])) ? $salsaPlayer['firstName'] : '';
                $last = (isset($salsaPlayer['lastName'])) ? $salsaPlayer['lastName'] : '';
                $email = (isset($salsaPlayer['emailAddress'])) ? $salsaPlayer['emailAddress'] : '';
                
                $teamName = (isset($salsaPlayer['teamName'])) ? $salsaPlayer['teamName'] : '';
                $homeJoint = (isset($salsaPlayer['homeJoint'])) ? $salsaPlayer['homeJoint'] : '';
                        
                $user = LeaderboardData::selectUserIdByEmail($email);
                $team = LeaderboardData::selectTeamLiveCheckinsByNameAndVenue($teamName, $homeJoint, $startDate, $endDate);
                if(!$team) {
                    $team = array();
                }
                        
                $result = array( 
                    'mobileCheckins' => (isset($salsaPlayer['checkinCount'])) ? $salsaPlayer['checkinCount'] : 0,
                    'liveCheckins' => ($team && isset($team['gameCheckins'])) ? $team['gameCheckins'] : 0,
                    
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
                $result['sort'] = (float)$result['mobileCheckins'] + (float)$result['liveCheckins'];
                
                if($result['userId'] > 0) {
                    $mergedUserIds[] = $result['userId'];
                }
                
                $results[] = $result;
            }
        }
        
        $localData = LeaderboardData::selectPlayerScoreLeaderboards($limit, $startDate, $endDate, $mergedUserIds);
        if($localData) {
            foreach($localData AS $localPlayer) {
                $result = array(
                    'mobileCheckins' => 0,
                    'liveCheckins' => $localPlayer->gameCheckins,
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
                    'sort' => $localPlayer->gameCheckins
                );
                $results[] = $result;
            }
        }
            
        if(count($results) > 0) {
            $leaderboard = self::sortAndTrimLeaderboardResults($results, $limit);
            return $app->render(200, array('leaderboard' => $leaderboard, 'startDate' => $startDate, 'endDate' => $endDate));
        } else {
            return $app->render(400,  array('msg' => 'Could not select Global Player Checkin Leaderboard.', 'url' => $url));
        }
    }

    // Global Team Checkin Leaderboard
    static function getGlobalTeamCheckinsLeaderboard($app) {
        $limit = (!v::key('limit', v::intVal())->validate($app->request->get())) ? 10 : $app->request->get('limit');
        $startDate = (!v::key('startDate', v::date('Y-m-d'))->validate($app->request->get())) ? false : $app->request->get('startDate');
        $endDate = (!v::key('endDate', v::date('Y-m-d'))->validate($app->request->get())) ? false : $app->request->get('endDate');
        
        // /location/getCheckins?scoreType=team&count=10
        $url = HotSalsaRequest::$HOT_SALSA_URL_GAME_CHECKINS . "?scoreType=team&count={$limit}";
        if($startDate) { $url = $url . "&startDate={$startDate}"; }
        if($endDate) { $url = $url . "&endDate={$endDate}"; }
        // CURL Hot Salsa
        $data = HotSalsaRequest::makeRequest($url);
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
        $results = array();
        $mergedTeamIds = array();
        if($data && isset($data['checkins'])) {
            foreach($data['checkins'] AS $salsaTeam) {
                $teamName = (isset($salsaTeam['teamName'])) ? $salsaTeam['teamName'] : '';
                $homeJoint = (isset($salsaTeam['homeJoint'])) ? $salsaTeam['homeJoint'] : '';
                
                $team = LeaderboardData::selectTeamLiveCheckinsByNameAndVenue($teamName, $homeJoint, $startDate, $endDate);
                if(!$team) {
                    $team = array();
                }
                
                $result = array( 
                    'mobileCheckins' => (isset($salsaTeam['checkinCount'])) ? $salsaTeam['checkinCount'] : 0,
                    'liveCheckins' => ($team && isset($team['gameCheckins'])) ? $team['gameCheckins'] : 0,
                    
                    'teamName' => $teamName,
                    'teamId' => ($team && isset($team['teamId'])) ? $team['teamId'] : 0,
                    'hotSalsaTeamId' => (isset($salsaTeam['teamId'])) ? $salsaTeam['teamId'] : 0,
                    
                    'homeJoint' => $homeJoint,
                    'homeJointId' => ($team && isset($team['homeVenueId'])) ? $team['homeVenueId'] : 0,
                    'hotSalsaHomeJointId' => (isset($salsaTeam['jointId'])) ? $salsaTeam['jointId'] : 0
                );
                $result['sort'] = (float)$result['mobileCheckins'] + (float)$result['liveCheckins'];
                
                if($result['teamId'] > 0) {
                    $mergedTeamIds[] = $result['teamId'];
                }
                
                $results[] = $result;
            }
        }
                
        $localData = LeaderboardData::selectTeamScoreLeaderboards($limit, $startDate, $endDate, $mergedTeamIds);
        if($localData) {
            foreach($localData AS $localTeam) {
                $result = array(
                    'mobileCheckins' => 0,
                    'liveCheckins' => $localTeam->gameCheckins,
                    'teamName' => $localTeam->teamName,
                    'teamId' => $localTeam->teamId,
                    'hotSalsaTeamId' => 0,
                    'homeJoint' => $localTeam->homeJoint,
                    'homeJointId' => $localTeam->homeJointId,
                    'hotSalsaHomeJointId' => 0,
                    'sort' => $localTeam->gameCheckins
                );
                $results[] = $result;
            }
        }
            
        if(count($results) > 0) {
            $leaderboard = self::sortAndTrimLeaderboardResults($results, $limit);
            return $app->render(200, array('leaderboard' => $leaderboard, 'startDate' => $startDate, 'endDate' => $endDate));
        } else {
            return $app->render(400,  array('msg' => 'Could not select Global Team Checkin Leaderboard.', 'url' => $url));
        }
    }

    // Per Joint Player Checkins Leaderboard
    static function getVenuePlayerCheckinsLeaderboard($app, $venueId) {
        if(!v::intVal()->validate($venueId)) {
            return $app->render(400,  array('msg' => 'Invalid Joint ID. Check your parameters and try again.'));
        } elseif ($venueId === '0') {
            $venueId = self::getHotSalsaLocationIdForUserHomeJoint();
            
            if(!$venueId) {
                return $app->render(400,  array('msg' => 'Invalid ID. Check your parameters and try again.'));
            }
        }
        
        $limit = (!v::key('limit', v::intVal())->validate($app->request->get())) ? 10 : $app->request->get('limit');
        $startDate = (!v::key('startDate', v::date('Y-m-d'))->validate($app->request->get())) ? false : $app->request->get('startDate');
        $endDate = (!v::key('endDate', v::date('Y-m-d'))->validate($app->request->get())) ? false : $app->request->get('endDate');
        
        $locationId = self::getHotSalsaLocationId($venueId);
        
        // /location/getCheckins?scoreType=player&scoreLevel=bar&locationId=11&count=10
        $url = HotSalsaRequest::$HOT_SALSA_URL_GAME_CHECKINS . "?scoreType=player&scoreLevel=bar&locationId={$locationId}&count={$limit}";
        if($startDate) { $url = $url . "&startDate={$startDate}"; }
        if($endDate) { $url = $url . "&endDate={$endDate}"; }
        // CURL Hot Salsa
        $salsaData = HotSalsaRequest::makeRequest($url);
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
                $team = LeaderboardData::selectTeamLiveCheckinsByNameAndVenue($teamName, $homeJoint, $startDate, $endDate);
                if(!$team) {
                    $team = array();
                }
                        
                $results[] = array( 
                    'mobileCheckins' => (isset($salsaPlayer['checkinCount'])) ? $salsaPlayer['checkinCount'] : 0,
                    'liveCheckins' => ($team && isset($team['gameCheckins'])) ? $team['gameCheckins'] : 0,
                    
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
            return $app->render(200, array('leaderboard' => $results, 'startDate' => $startDate, 'endDate' => $endDate));
        } else {
            return $app->render(400,  array('msg' => 'Could not select Per Joint Player Checkins Leaderboard.', 'url' => $url));
        }
    }

    // Per Joint Team Checkins Leaderboard
    static function getVenueTeamCheckinsLeaderboard($app, $venueId) {
        if(!v::intVal()->validate($venueId)) {
            return $app->render(400,  array('msg' => 'Invalid Joint ID. Check your parameters and try again.'));
        } elseif ($venueId === '0') {
            $venueId = self::getHotSalsaLocationIdForUserHomeJoint();
            
            if(!$venueId) {
                return $app->render(400,  array('msg' => 'Invalid ID. Check your parameters and try again.'));
            }
        }
        
        $limit = (!v::key('limit', v::intVal())->validate($app->request->get())) ? 10 : $app->request->get('limit');
        $startDate = (!v::key('startDate', v::date('Y-m-d'))->validate($app->request->get())) ? false : $app->request->get('startDate');
        $endDate = (!v::key('endDate', v::date('Y-m-d'))->validate($app->request->get())) ? false : $app->request->get('endDate');
        
        $locationId = self::getHotSalsaLocationId($venueId);
        
        // /location/getCheckins?scoreType=team&scoreLevel=bar&locationId=11&count=10
        $url = HotSalsaRequest::$HOT_SALSA_URL_GAME_CHECKINS . "?scoreType=team&scoreLevel=bar&locationId={$locationId}&count={$limit}";
        if($startDate) { $url = $url . "&startDate={$startDate}"; }
        if($endDate) { $url = $url . "&endDate={$endDate}"; }
        // CURL Hot Salsa
        $salsaData = HotSalsaRequest::makeRequest($url);
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
                
                $team = LeaderboardData::selectTeamLiveCheckinsByNameAndVenue($teamName, $homeJoint, $startDate, $endDate);
                if(!$team) {
                    $team = array();
                }
                
                $results[] = array( 
                    'mobileCheckins' => (isset($salsaTeam['checkinCount'])) ? $salsaTeam['checkinCount'] : 0,
                    'liveCheckins' => ($team && isset($team['gameCheckins'])) ? $team['gameCheckins'] : 0,
                    
                    'teamName' => $teamName,
                    'teamId' => ($team && isset($team['teamId'])) ? $team['teamId'] : 0,
                    'hotSalsaTeamId' => (isset($salsaTeam['teamId'])) ? $salsaTeam['teamId'] : 0,
                    
                    'homeJoint' => $homeJoint,
                    'homeJointId' => ($team && isset($team['homeVenueId'])) ? $team['homeVenueId'] : 0,
                    'hotSalsaHomeJointId' => (isset($salsaTeam['jointId'])) ? $salsaTeam['jointId'] : 0
                );
            }
            return $app->render(200, array('leaderboard' => $results, 'startDate' => $startDate, 'endDate' => $endDate));
        } else {
            return $app->render(400,  array('msg' => 'Could not select Per Joint Team Checkins Leaderboard.', 'url' => $url));
        }
    }
}

class LeaderboardListsController {
    /* Venue Lists */
    
    static function getMergedVenuesList($app) {
        $localData = LeaderboardData::selectVenueList();
        $salsaData = HotSalsaRequest::makeRequest(HotSalsaRequest::$HOT_SALSA_URL_VENUE_LIST);
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
        $salsaData = HotSalsaRequest::makeRequest(HotSalsaRequest::$HOT_SALSA_URL_VENUE_LIST);
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
        $url = HotSalsaRequest::$HOT_SALSA_URL_TEAMS_LIST;
        
        $salsaData = HotSalsaRequest::makeRequest($url);
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