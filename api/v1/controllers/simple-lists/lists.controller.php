<?php namespace API;
require_once dirname(__FILE__) . '/lists.data.php';

class ListsController {
    
    static function getUsersList($app) {
        $data = ListsData::selectUsers();
        $list = ($data) ? $data : array();
        return $app->render(200, array('list' => $list));
    }
    
    static function getGroupsList($app) {
        $data = ListsData::selectGroups();
        $list = ($data) ? $data : array();
        return $app->render(200, array('list' => $list));
    }
    
    static function getRolesList($app) {
        $data = ListsData::selectRoles();
        $list = ($data) ? $data : array();
        return $app->render(200, array('list' => $list));
    }
    
    static function getVisibilityFieldsList($app) {
        $data = ListsData::selectVisibilityFields();
        $list = ($data) ? $data : array();
        return $app->render(200, array('list' => $list));
    }
    
    
    
    
    static function getVenuesList($app) {
        $data = ListsData::selectVenues();
        $list = ($data) ? $data : array();
        return $app->render(200, array('list' => $list));
    }
    
    static function getTeamsList($app) {
        $data = ListsData::selectTeams();
        $list = ($data) ? $data : array();
        return $app->render(200, array('list' => $list));
    }
    
    static function getAllTeamsList($app) {
        $data = ListsData::selectAllTeams();
        $list = ($data) ? $data : array();
        return $app->render(200, array('list' => $list));
    }
    
    static function getGamesList($app) {
        $data = ListsData::selectGames();
        $list = ($data) ? $data : array();
        return $app->render(200, array('list' => $list));
    }
    
}