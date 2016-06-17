<?php namespace API;
 require_once dirname(dirname(dirname(__FILE__))) . '/services/api.dbconn.php';

class LeaderboardData {
    
    static function selectVenueList() {
        return DBConn::selectAll("SELECT v.id, v.name, v.address, "
                . "v.address_b AS addressb, v.city, v.state, v.zip "
                . "FROM " . DBConn::prefix() . "venues AS v "
                . "ORDER BY v.state, v.city, v.name;"
        );
    }

    static function selectUserIdByEmail($email) {
        return DBConn::selectOne("SELECT u.id FROM " . DBConn::prefix() . "users AS u "
                . "WHERE u.email = :email LIMIT 1;", array(':email' => $email));
    }

    static function selectPlayerLiveScoreByEmail($email, $teamName, $homeVenue) {
        // We only match the api to the db record for the player with
        // this email if they are on the same team and that team has 
        // the same home venue
        return DBConn::selectOne("SELECT u.id AS userId, t.id AS teamId, t.name AS teamName, "
                . "IFNULL(s.score, '0') AS score, v.id AS homeVenueId, v.name AS homeVenue "
                . "FROM " . DBConn::prefix() . "users AS u "
                . "LEFT JOIN " . DBConn::prefix() . "team_members AS tm ON tm.user_id = u.id "
                . "LEFT JOIN " . DBConn::prefix() . "teams AS t ON t.id = tm.team_id "
                . "LEFT JOIN " . DBConn::prefix() . "game_score_teams AS s ON s.game_id = t.current_game_id AND s.team_id = t.id "
                . "LEFT JOIN as_venues AS v ON v.id = t.home_venue_id "
                . "WHERE u.email = :email AND v.name = :home_venue_name AND t.name = :team_name LIMIT 1;", 
                array(':email' => $email, ':team_name' => $teamName, ':home_venue_name' => $homeVenue));
    }
    
    static function selectTeamLiveScoreByNameAndVenue($teamName, $homeVenue) {
        return DBConn::selectOne("SELECT t.id AS teamId, t.name AS teamName, "
                . "IFNULL(s.score, '0') AS score, v.id AS homeVenueId, v.name AS homeVenue "
                . "FROM " . DBConn::prefix() . "teams AS t "
                . "LEFT JOIN " . DBConn::prefix() . "game_score_teams AS s ON s.game_id = t.current_game_id AND s.team_id = t.id "
                . "LEFT JOIN as_venues AS v ON v.id = t.home_venue_id "
                . "WHERE v.name = :home_venue_name AND t.name = :team_name LIMIT 1;", 
                array(':team_name' => $teamName, ':home_venue_name' => $homeVenue));
    }
    
    static function selectTeamLiveCheckinsByNameAndVenue($teamName, $homeVenue) {
        return DBConn::selectOne("SELECT t.id AS teamId, t.name AS teamName, v.id AS homeVenueId, "
                . "v.name AS homeVenue, COUNT(c.game_id) AS checkins "
                . "FROM " . DBConn::prefix() . "teams AS t "
                . "LEFT JOIN " . DBConn::prefix() . "game_score_teams AS s ON s.game_id = t.current_game_id AND s.team_id = t.id "
                . "LEFT JOIN " . DBConn::prefix() . "venues AS v ON v.id = t.home_venue_id "
                . "LEFT JOIN " . DBConn::prefix() . "logs_game_checkins AS c ON c.team_id = t.id "
                . "WHERE v.name = :home_venue_name AND t.name = :team_name "
                . "GROUP BY c.game_id LIMIT 1", 
                array(':team_name' => $teamName, ':home_venue_name' => $homeVenue));
    }
}