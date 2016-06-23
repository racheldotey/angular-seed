<?php namespace API;
 require_once dirname(dirname(dirname(__FILE__))) . '/services/api.dbconn.php';

class LeaderboardData {
    
    static function selectVenueList() {
        return DBConn::selectAll("SELECT v.id AS localId, 0 AS hotSalsaId, v.name, v.address, "
                . "v.address_b AS addressb, v.city, v.state, v.zip "
                . "FROM " . DBConn::prefix() . "venues AS v "
                . "ORDER BY v.state, v.city, v.name;"
        );
    }

    static function selectUserIdByEmail($email) {
        return DBConn::selectOne("SELECT u.id FROM " . DBConn::prefix() . "users AS u "
                . "WHERE u.email = :email LIMIT 1;", array(':email' => $email));
    }

    static function getHomeJointForTeamByUserId($userId) {
        return DBConn::selectOne("SELECT t.name AS teamName, v.name AS homeVenue "
                . "FROM " . DBConn::prefix() . "users AS u "
                . "LEFT JOIN " . DBConn::prefix() . "team_members AS tm ON tm.user_id = u.id "
                . "LEFT JOIN " . DBConn::prefix() . "teams AS t ON t.id = tm.team_id "
                . "LEFT JOIN " . DBConn::prefix() . "venues AS v ON v.id = t.home_venue_id "
                . "WHERE u.id = :user_id LIMIT 1;", 
                array(':user_id' => $userId));
    }
    
    static function selectPlayerScoreLeaderboards($count, $mergedUserIds = array()) {
        if(count($mergedUserIds) > 0) {
            //$variables = $mergedUserIds;
            $placeholders = str_repeat ('?, ',  count ($mergedUserIds) - 1) . '?';
            $variables[] = (int)$count;
            
            return DBConn::selectAll("SELECT u.id AS userId, u.name_first AS firstName, "
                    . "u.name_last AS lastName, u.email, "
                    . "t.id AS teamId, t.name AS teamName, "
                    . "v.id AS homeJointId, v.name AS homeJoint, "
                    . "COALESCE(SUM(s.score),0) AS score, count(s.game_id) AS gameCheckins "
                    . "FROM " . DBConn::prefix() . "team_members AS m "
                    . "JOIN " . DBConn::prefix() . "users AS u ON u.id = m.user_id "
                    . "JOIN " . DBConn::prefix() . "teams AS t ON t.id = m.team_id "
                    . "JOIN " . DBConn::prefix() . "game_score_teams AS s ON s.team_id = t.id "
                    . "LEFT JOIN " . DBConn::prefix() . "venues AS v ON v.id = t.home_venue_id "
                    . "GROUP BY u.id "
                    . "ORDER BY score DESC "
                    . "LIMIT ?;", $variables);
        } else {
            return DBConn::selectAll("SELECT u.id AS userId, u.name_first AS firstName, "
                . "u.name_last AS lastName, u.email, "
                . "t.id AS teamId, t.name AS teamName, "
                . "v.id AS homeJointId, v.name AS homeJoint, "
                . "COALESCE(SUM(s.score),0) AS score, count(s.game_id) AS gameCheckins "
                . "FROM " . DBConn::prefix() . "team_members AS m "
                . "JOIN " . DBConn::prefix() . "users AS u ON u.id = m.user_id "
                . "JOIN " . DBConn::prefix() . "teams AS t ON t.id = m.team_id "
                . "JOIN " . DBConn::prefix() . "game_score_teams AS s ON s.team_id = t.id "
                . "LEFT JOIN " . DBConn::prefix() . "venues AS v ON v.id = t.home_venue_id "
                . "GROUP BY u.id "
                . "ORDER BY score DESC "
                . "LIMIT :limit;", array(':limit' => (int)$count));
        }
    }
    
    static function selectTeamScoreLeaderboards($count, $mergedTeamIds = array()) {
        if(count($mergedTeamIds) > 0) {
            //$variables = $mergedTeamIds;
            $placeholders = str_repeat ('?, ',  count ($mergedTeamIds) - 1) . '?';
            $variables[] = (int)$count;

            return DBConn::selectAll("SELECT t.id AS teamId, t.name AS teamName, "
                    . "v.id AS homeJointId, v.name AS homeJoint, "
                    . "COALESCE(SUM(s.score),0) AS score, count(s.game_id) AS gameCheckins "
                    . "FROM " . DBConn::prefix() . "teams AS t "
                    . "JOIN " . DBConn::prefix() . "game_score_teams AS s ON s.team_id = t.id "
                    . "LEFT JOIN " . DBConn::prefix() . "venues AS v ON v.id = t.home_venue_id "
                    . "GROUP BY s.team_id "
                    . "ORDER BY score DESC "
                    . "LIMIT ?;", $variables);
        } else {
            return DBConn::selectAll("SELECT t.id AS teamId, t.name AS teamName, "
                . "v.id AS homeJointId, v.name AS homeJoint, "
                . "COALESCE(SUM(s.score),0) AS score, count(s.game_id) AS gameCheckins "
                . "FROM " . DBConn::prefix() . "teams AS t "
                . "JOIN " . DBConn::prefix() . "game_score_teams AS s ON s.team_id = t.id "
                . "LEFT JOIN " . DBConn::prefix() . "venues AS v ON v.id = t.home_venue_id "
                . "GROUP BY s.team_id "
                . "ORDER BY score DESC "
                . "LIMIT :limit;", array(':limit' => (int)$count));
        }
    }

    static function selectTeamLiveScoreByNameAndVenue($teamName, $homeVenue) {        
        return DBConn::selectOne("SELECT t.id AS teamId, t.name AS teamName, "
                . "IFNULL(s.score, '0') AS score, v.id AS homeVenueId, v.name AS homeVenue "
                . "FROM " . DBConn::prefix() . "teams AS t "
                . "LEFT JOIN " . DBConn::prefix() . "game_score_teams AS s ON s.game_id = t.current_game_id AND s.team_id = t.id "
                . "LEFT JOIN " . DBConn::prefix() . "venues AS v ON v.id = t.home_venue_id "
                . "WHERE t.name = :team_name AND v.name = :home_venue_name LIMIT 1;", 
                array(':team_name' => $teamName, ':home_venue_name' => $homeVenue), \PDO::FETCH_ASSOC);
    }
    
    static function selectTeamLiveCheckinsByNameAndVenue($teamName, $homeVenue) {
        return DBConn::selectOne("SELECT t.id AS teamId, t.name AS teamName, v.id AS homeVenueId, "
                . "v.name AS homeVenue, COUNT(c.game_id) AS gameCheckins "
                . "FROM " . DBConn::prefix() . "teams AS t "
                . "LEFT JOIN " . DBConn::prefix() . "game_score_teams AS s ON s.game_id = t.current_game_id AND s.team_id = t.id "
                . "LEFT JOIN " . DBConn::prefix() . "venues AS v ON v.id = t.home_venue_id "
                . "LEFT JOIN " . DBConn::prefix() . "logs_game_checkins AS c ON c.team_id = t.id "
                . "WHERE v.name = :home_venue_name AND t.name = :team_name "
                . "GROUP BY c.game_id LIMIT 1;", 
                array(':team_name' => $teamName, ':home_venue_name' => $homeVenue));
    }
}