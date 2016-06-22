<?php namespace API;
require_once dirname(__FILE__) . '/datatables.data.php';

class DatatablesController {
    static function getUsers($app) {
        $data = DatatablesData::selectUsers();
        $table = ($data) ? $data : array();
        return $app->render(200, array('table' => $table ));
    }
    
    static function getUserGroups($app) {
        $data = DatatablesData::selectUserGroups();
        $table = ($data) ? $data : array();
        return $app->render(200, array('table' => $table ));
    }
    
    static function getGroupRoles($app) {
        $data = DatatablesData::selectGroupRoles();
        $table = ($data) ? $data : array();
        return $app->render(200, array('table' => $table ));
    }
    
    static function getConfigVariables($app) {
        $data = DatatablesData::selectConfigVariables();
        $table = ($data) ? $data : array();
        return $app->render(200, array('table' => $table ));
    }
    
    static function getVisibilityFields($app) {
        $data = DatatablesData::selectVisibilityFields();
        $table = ($data) ? $data : array();
        return $app->render(200, array('table' => $table ));
    }
    
    // Admin Trivia
    
    static function getCurrentGames($app) {
        $data = DatatablesData::selectCurrentGames();
        $table = ($data) ? $data : array();
        return $app->render(200, array('table' => $table ));
    }
    
    static function getTriviaTeams($app) {
        $data = DatatablesData::selectTriviaTeams();
        $table = ($data) ? $data : array();
        return $app->render(200, array('table' => $table ));
    }
    
    static function getTriviaVenues($app) {
        $data = DatatablesData::selectTriviaVenues();
        $table = ($data) ? $data : array();
        return $app->render(200, array('table' => $table ));
    }

    static function getTriviaHosts($app) {
        $hosts=array();
        $data = DatatablesData::selectTriviaHosts();
        if(!empty($data)){
            foreach ($data as $i => $d) {
                unset($d->temp);
                $venue=array();
                $array=get_object_vars ($d);
                $keys = array_keys($array);
                
                foreach ($keys as $j => $key) {
                    
                    if(preg_match('/^venue(.*)/', $key))
                    {
                        $venue[$key]=$d->{$key};
                    }
                    else if(!isset($hosts[$d->id][$key])){
                        $hosts[$d->id][$key]=$d->{$key};
                    }
                }
                if(!isset($hosts[$d->id])){
                      $hosts[$d->id]['venues']=array();
                }
                $hosts[$d->id]['venues'][]=$venue;
             
            }
        }
        $table = ($hosts) ? array_values($hosts) : array();

        return $app->render(200, array('table' => $table ));
    }
    static function getTeamGameCheckins($app, $teamId) {
        $data = DatatablesData::selectTeamGameCheckins($teamId);
        $table = ($data) ? $data : array();
        return $app->render(200, array('table' => $table ));
    }
    
    // Games
    
    static function getGames($app) {
        $data = DatatablesData::selectGames();
        $table = ($data) ? $data : array();
        return $app->render(200, array('table' => $table ));
    }
    
    static function getHostGames($app, $hostId) {
        $data = DatatablesData::selectHostGames($hostId);
        $table = ($data) ? $data : array();
        return $app->render(200, array('table' => $table ));
    }
    
    static function getVenueGames($app, $venueId) {
        $data = DatatablesData::selectVenueGames($venueId);
        $table = ($data) ? $data : array();
        return $app->render(200, array('table' => $table ));
    }
    
    static function getTeamGames($app, $teamId) {
        $data = DatatablesData::selectTeamGames($teamId);
        $table = ($data) ? $data : array();
        return $app->render(200, array('table' => $table ));
    }
    
    // Game Scoreboard
    
    static function getGameSimpleScoreboard($app, $gameId, $roundNumber) {
        $data = DatatablesData::selectGameSimpleScoreboard($gameId, $roundNumber);
        $table = ($data) ? $data : array();
        return $app->render(200, array('table' => $table ));
    }
}
