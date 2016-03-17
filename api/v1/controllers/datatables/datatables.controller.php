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
}
