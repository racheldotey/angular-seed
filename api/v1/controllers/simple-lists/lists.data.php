<?php namespace API;
 require_once dirname(dirname(dirname(__FILE__))) . '/services/api.dbconn.php';

class ListsData {
    
    static function selectUsers() {
        return DBConn::selectAll("SELECT u.id, CONCAT(u.name_last, ', ', u.name_first, '   (ID: ', u.id, ')') AS label "
                . "FROM " . DBConn::prefix() . "users AS u WHERE u.disabled IS NULL ORDER BY u.name_last;");
    }
    
    static function selectGroups() {
        return DBConn::selectAll("SELECT g.id, g.group AS label "
                . "FROM " . DBConn::prefix() . "auth_groups AS g ORDER BY g.group;");
    }
    
    static function selectRoles() {
        return DBConn::selectAll("SELECT r.id, r.role AS label "
                . "FROM " . DBConn::prefix() . "auth_roles AS r ORDER BY r.role;");
    }
    
    static function selectVisibilityFields() {
        return DBConn::selectAll("SELECT f.id, CONCAT('(', f.type, ') ', f.identifier) AS label "
                . "FROM " . DBConn::prefix() . "auth_fields AS f ORDER BY label;");
    }
        
    static function selectVenues() {
        //return DBConn::selectAll("SELECT `id`, `name`, CONCAT(`state`, ', ', `city`, ' - ', `name`) AS label "
        //        . "FROM " . DBConn::prefix() . "venues WHERE disabled IS NULL ORDER BY label;");
		 return DBConn::selectAll("SELECT v.id, v.name AS name, CONCAT(`state`, ', ', `city`, ' - ', `name`) AS label,"
                . "vs.trivia_day AS triviaDay, vs.trivia_time AS triviaTime "
                . "FROM " . DBConn::prefix() . "venues AS v "
                . "LEFT JOIN " . DBConn::prefix() . "venue_roles AS vr ON vr.venue_id = v.id "
                . "LEFT JOIN " . DBConn::prefix() . "users AS u ON u.id = vr.user_id "
                . "LEFT JOIN " . DBConn::prefix() . "users AS u2 ON u2.id = v.created_user_id "
                . "LEFT JOIN " . DBConn::prefix() . "venues_trivia_schedules AS vs ON vs.venue_id = v.id ORDER BY v.name;");
    }
    
    static function selectTeams() {
        return DBConn::selectAll("SELECT t.id, t.name AS label "
                . "FROM " . DBConn::prefix() . "teams AS t "
                . "WHERE t.current_game_id IS NULL ORDER BY t.name;");
    }
    
    static function selectAllTeams() {
        return DBConn::selectAll("SELECT t.id, t.name AS label "
                . "FROM " . DBConn::prefix() . "teams AS t ORDER BY t.name;");
    }
    
    static function selectGames() {
        return DBConn::selectAll("SELECT g.id, g.name AS label "
                . "FROM " . DBConn::prefix() . "games AS g "
                . "WHERE g.game_started IS NOT NULL AND g.game_ended IS NULL ORDER BY g.name;");
    }
}
