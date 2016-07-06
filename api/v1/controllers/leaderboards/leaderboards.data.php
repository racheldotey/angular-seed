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
    
    private static function getWhereStartAndEnd($startDate, $endDate) {
        // Narrow By Date
        $dates = '';
        if($startDate && date("Y-m-d H:i:s", strtotime($startDate))) {
            $dates .= "game_started > '";
            $dates .= date("Y-m-d H:i:s", strtotime($startDate));
            $dates .= "'";
        }
        
        if($endDate && date("Y-m-d H:i:s", strtotime($endDate))) {
        
            if($dates !== '') {
                $dates .= ' AND ';
            }
            
            $dates .= "game_ended < '";
            $dates .= date("Y-m-d H:i:s", strtotime($endDate));
            $dates .= "'";
        }
        return $dates;
    }
    
    static function selectPlayerScoreLeaderboards($count, $startDate, $endDate, $mergedUserIds = array()) {
        $dates = self::getWhereStartAndEnd($startDate, $endDate);
        
        if(false && count($mergedUserIds) > 0) {
            // TO DO: Debug the WHERE NOT IN clause and 
            // the use of PDO::ATTR_EMULATE_PREPARES on Google Cloud
            // This code works locally.
            DBConn::setPDOAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            $placeholders = str_repeat ('?, ',  count ($mergedUserIds) - 1) . '?';
            $variables[] = (int)$count;
            
            if($dates !== '') {
                $dates = "AND $dates ";
            }
            
            $leaderboard = DBConn::selectAll("SELECT u.id AS userId, u.name_first AS firstName, "
                    . "u.name_last AS lastName, u.email, "
                    . "t.id AS teamId, t.name AS teamName, "
                    . "v.id AS homeJointId, v.name AS homeJoint, "
                    . "COALESCE(SUM(s.score),0) AS score, count(s.game_id) AS gameCheckins "
                    . "FROM " . DBConn::prefix() . "team_members AS m "
                    . "JOIN " . DBConn::prefix() . "users AS u ON u.id = m.user_id "
                    . "JOIN " . DBConn::prefix() . "teams AS t ON t.id = m.team_id "
                    . "JOIN " . DBConn::prefix() . "game_score_teams AS s ON s.team_id = t.id "
                    . "JOIN " . DBConn::prefix() . "games AS g ON g.id = s.game_id "
                    . "LEFT JOIN " . DBConn::prefix() . "venues AS v ON v.id = t.home_venue_id "
                    . "WHERE u.id NOT IN($placeholders) "
                    . $dates
                    . "GROUP BY u.id "
                    . "ORDER BY score DESC "
                    . "LIMIT ?;", $variables);
            DBConn::setPDOAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        } else {   
            if($dates !== '') {
                $dates = "WHERE $dates ";
            }
                        
            $leaderboard = DBConn::selectAll("SELECT u.id AS userId, u.name_first AS firstName, "
                . "u.name_last AS lastName, u.email, "
                . "t.id AS teamId, t.name AS teamName, "
                . "v.id AS homeJointId, v.name AS homeJoint, "
                . "COALESCE(SUM(s.score),0) AS score, count(s.game_id) AS gameCheckins "
                . "FROM " . DBConn::prefix() . "team_members AS m "
                . "JOIN " . DBConn::prefix() . "users AS u ON u.id = m.user_id "
                . "JOIN " . DBConn::prefix() . "teams AS t ON t.id = m.team_id "
                . "JOIN " . DBConn::prefix() . "game_score_teams AS s ON s.team_id = t.id "
                . "JOIN " . DBConn::prefix() . "games AS g ON g.id = s.game_id "
                . "LEFT JOIN " . DBConn::prefix() . "venues AS v ON v.id = t.home_venue_id "
                . $dates
                . "GROUP BY u.id "
                . "ORDER BY score DESC "
                . "LIMIT :limit;", array(':limit' => (int)$count));
        }
        return $leaderboard;
    }
    
    static function selectTeamScoreLeaderboards($count, $startDate, $endDate, $mergedTeamIds = array()) {
        $dates = self::getWhereStartAndEnd($startDate, $endDate);
        
        if(false && count($mergedTeamIds) > 0) {
            // TO DO: Debug the WHERE NOT IN clause and 
            // the use of PDO::ATTR_EMULATE_PREPARES on Google Cloud
            // This code works locally.
            DBConn::setPDOAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            $placeholders = str_repeat ('?, ',  count ($mergedTeamIds) - 1) . '?';
            $variables[] = (int)$count;
            if($dates !== '') {
                $dates = "AND $dates ";
            }
            
            DBConn::setPDOAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            $leaderboard = DBConn::selectAll("SELECT t.id AS teamId, t.name AS teamName, "
                    . "v.id AS homeJointId, v.name AS homeJoint, "
                    . "COALESCE(SUM(s.score),0) AS score, count(s.game_id) AS gameCheckins "
                    . "FROM " . DBConn::prefix() . "teams AS t "
                    . "JOIN " . DBConn::prefix() . "game_score_teams AS s ON s.team_id = t.id "
                    . "JOIN " . DBConn::prefix() . "games AS g ON g.id = s.game_id "
                    . "LEFT JOIN " . DBConn::prefix() . "venues AS v ON v.id = t.home_venue_id "
                    . "WHERE t.id NOT IN($placeholders) "
                    . $dates
                    . "GROUP BY s.team_id "
                    . "ORDER BY score DESC "
                    . "LIMIT ?;", $variables);
            DBConn::setPDOAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        } else {    
            if($dates !== '') {
                $dates = "WHERE $dates ";
            }
            
            $leaderboard = DBConn::selectAll("SELECT t.id AS teamId, t.name AS teamName, "
                . "v.id AS homeJointId, v.name AS homeJoint, "
                . "COALESCE(SUM(s.score),0) AS score, count(s.game_id) AS gameCheckins "
                . "FROM " . DBConn::prefix() . "teams AS t "
                . "JOIN " . DBConn::prefix() . "game_score_teams AS s ON s.team_id = t.id "
                . "JOIN " . DBConn::prefix() . "games AS g ON g.id = s.game_id "
                . "LEFT JOIN " . DBConn::prefix() . "venues AS v ON v.id = t.home_venue_id "
                . $dates
                . "GROUP BY s.team_id "
                . "ORDER BY score DESC "
                . "LIMIT :limit;", array(':limit' => (int)$count));
        }
        return $leaderboard;
    }

    static function selectTeamLiveScoreByVenueAndCity($venueName, $venueCity, $count, $startDate, $endDate, $mergedTeamIds = array()) {   
        $dates = self::getWhereStartAndEnd($startDate, $endDate);
        if($dates !== '') {
            $dates = "AND $dates ";
        }
             
        return DBConn::selectAll("SELECT t.id AS teamId, t.name AS teamName, "
                . "IFNULL(s.score, '0') AS score, v.id AS homeVenueId, v.name AS homeVenue "
                . "FROM " . DBConn::prefix() . "teams AS t "
                . "LEFT JOIN " . DBConn::prefix() . "game_score_teams AS s ON s.team_id = t.id "
                . "LEFT JOIN " . DBConn::prefix() . "games AS g ON g.id = s.game_id "
                . "LEFT JOIN " . DBConn::prefix() . "venues AS v ON v.id = t.home_venue_id "
                . "WHERE v.name = :venue_name AND v.city = :venue_city "
                . $dates
                . "LIMIT $count;",  
                array(':venue_name' => $venueName, ':venue_city' => $venueCity), \PDO::FETCH_ASSOC);
    }

    static function selectTeamLiveScoreByNameAndVenue($teamName, $homeVenue,  $startDate, $endDate) {   
        $dates = self::getWhereStartAndEnd($startDate, $endDate);
        if($dates !== '') {
            $dates = "AND $dates ";
        }
             
        return DBConn::selectOne("SELECT t.id AS teamId, t.name AS teamName, "
                . "IFNULL(s.score, '0') AS score, v.id AS homeVenueId, v.name AS homeVenue "
                . "FROM " . DBConn::prefix() . "teams AS t "
                . "LEFT JOIN " . DBConn::prefix() . "game_score_teams AS s ON s.team_id = t.id "
                . "LEFT JOIN " . DBConn::prefix() . "games AS g ON g.id = s.game_id "
                . "LEFT JOIN " . DBConn::prefix() . "venues AS v ON v.id = t.home_venue_id "
                . "WHERE t.name = :team_name AND v.name = :home_venue_name "
                . $dates
                . "LIMIT 1;", 
                array(':team_name' => $teamName, ':home_venue_name' => $homeVenue), \PDO::FETCH_ASSOC);
    }
    
    static function selectTeamLiveCheckinsByVenueAndCity($venueName, $venueCity, $count, $startDate, $endDate, $mergedTeamIds = array()) {
        $dates = self::getWhereStartAndEnd($startDate, $endDate);
        if($dates !== '') {
            $dates = "AND $dates ";
        }
        
        return DBConn::selectAll("SELECT t.id AS teamId, t.name AS teamName, v.id AS homeVenueId, "
                . "v.name AS homeVenue, COUNT(s.game_id) AS gameCheckins "
                . "FROM " . DBConn::prefix() . "teams AS t "
                . "LEFT JOIN " . DBConn::prefix() . "game_score_teams AS s ON s.team_id = t.id "
                . "LEFT JOIN " . DBConn::prefix() . "games AS g ON g.id = s.game_id "
                . "LEFT JOIN " . DBConn::prefix() . "venues AS v ON v.id = t.home_venue_id "
                . "LEFT JOIN " . DBConn::prefix() . "logs_game_checkins AS c ON c.team_id = t.id "
                . "WHERE v.name = :home_venue_name AND t.name = :team_name "
                . $dates
                . "GROUP BY s.team_id"
                . "LIMIT $count;",   
                array(':venue_name' => $venueName, ':venue_city' => $venueCity), \PDO::FETCH_ASSOC);
    }
    
    static function selectTeamLiveCheckinsByNameAndVenue($teamName, $homeVenue,  $startDate, $endDate) {
        $dates = self::getWhereStartAndEnd($startDate, $endDate);
        if($dates !== '') {
            $dates = "AND $dates ";
        }
        
        return DBConn::selectOne("SELECT t.id AS teamId, t.name AS teamName, v.id AS homeVenueId, "
                . "v.name AS homeVenue, COUNT(s.game_id) AS gameCheckins "
                . "FROM " . DBConn::prefix() . "teams AS t "
                . "LEFT JOIN " . DBConn::prefix() . "game_score_teams AS s ON s.team_id = t.id "
                . "LEFT JOIN " . DBConn::prefix() . "games AS g ON g.id = s.game_id "
                . "LEFT JOIN " . DBConn::prefix() . "venues AS v ON v.id = t.home_venue_id "
                . "LEFT JOIN " . DBConn::prefix() . "logs_game_checkins AS c ON c.team_id = t.id "
                . "WHERE v.name = :home_venue_name AND t.name = :team_name "
                . $dates
                . "GROUP BY s.team_id LIMIT 1;",
                array(':team_name' => $teamName, ':home_venue_name' => $homeVenue));
    }
}