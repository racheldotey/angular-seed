<?php namespace API;
 require_once dirname(dirname(dirname(__FILE__))) . '/services/api.dbconn.php';

class LeaderboardData {
    
    static function selectVenueList() {
        return DBConn::selectAll("SELECT v.id AS localId, 0 AS hotSalsaId, v.name, v.address, "
                . "v.address_b AS addressb, v.city, v.state, v.zip "
                . "FROM trv_venues AS v "
                . "ORDER BY v.state, v.city, v.name;"
        );
    }

    static function selectUserIdByEmail($email) {
        return DBConn::selectOne("SELECT u.id FROM trv_users AS u "
                . "WHERE u.email = :email LIMIT 1;", array(':email' => $email));
    }

    static function getHomeJointForTeamByUserId($userId) {
        return DBConn::selectOne("SELECT t.name AS teamName, v.name AS homeVenue "
                . "FROM trv_users AS u "
                . "LEFT JOIN trv_team_members AS tm ON tm.user_id = u.id "
                . "LEFT JOIN trv_teams AS t ON t.id = tm.team_id "
                . "LEFT JOIN trv_venues AS v ON v.id = t.home_venue_id "
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
        
        // PDO::ATTR_EMULATE_PREPARES is used to allow variables ($dates) to be  
        // concatinated in the MySQL query.
        DBConn::setPDOAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        if(false && count($mergedUserIds) > 0) {
            // TO DO: Debug the WHERE NOT IN clause on Google Cloud
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
                    . "FROM trv_team_members AS m "
                    . "JOIN trv_users AS u ON u.id = m.user_id "
                    . "JOIN trv_teams AS t ON t.id = m.team_id "
                    . "JOIN trv_game_score_teams AS s ON s.team_id = t.id "
                    . "JOIN trv_games AS g ON g.id = s.game_id "
                    . "LEFT JOIN trv_venues AS v ON v.id = t.home_venue_id "
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
                . "FROM trv_team_members AS m "
                . "JOIN trv_users AS u ON u.id = m.user_id "
                . "JOIN trv_teams AS t ON t.id = m.team_id "
                . "JOIN trv_game_score_teams AS s ON s.team_id = t.id "
                . "JOIN trv_games AS g ON g.id = s.game_id "
                . "LEFT JOIN trv_venues AS v ON v.id = t.home_venue_id "
                . $dates
                . "GROUP BY u.id "
                . "ORDER BY score DESC "
                . "LIMIT :limit;", array(':limit' => (int)$count));
        }
        DBConn::setPDOAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        return $leaderboard;
    }
    
    static function selectPlayerScoresByVenueAndCity($venueName, $venueCity, $count, $startDate, $endDate, $mergedUserIds = array()) {
        $dates = self::getWhereStartAndEnd($startDate, $endDate);
        
        // PDO::ATTR_EMULATE_PREPARES is used to allow variables ($dates) to be  
        // concatinated in the MySQL query.
        DBConn::setPDOAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        if(false && count($mergedUserIds) > 0) {
            // TO DO: Debug the WHERE NOT IN clause on Google Cloud
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
                    . "FROM trv_team_members AS m "
                    . "JOIN trv_users AS u ON u.id = m.user_id "
                    . "JOIN trv_teams AS t ON t.id = m.team_id "
                    . "JOIN trv_game_score_teams AS s ON s.team_id = t.id "
                    . "JOIN trv_games AS g ON g.id = s.game_id "
                    . "LEFT JOIN trv_venues AS v ON v.id = t.home_venue_id "
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
                . "FROM trv_team_members AS m "
                . "JOIN trv_users AS u ON u.id = m.user_id "
                . "JOIN trv_teams AS t ON t.id = m.team_id "
                . "JOIN trv_game_score_teams AS s ON s.team_id = t.id "
                . "JOIN trv_games AS g ON g.id = s.game_id "
                . "LEFT JOIN trv_venues AS v ON v.id = t.home_venue_id "
                . $dates
                . "GROUP BY u.id "
                . "ORDER BY score DESC "
                . "LIMIT :limit;", array(':limit' => (int)$count));
        }
        DBConn::setPDOAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        return $leaderboard;
    }
    
    static function selectTeamScoreLeaderboards($count, $startDate, $endDate, $mergedTeamIds = array()) {
        $dates = self::getWhereStartAndEnd($startDate, $endDate);
        
        // PDO::ATTR_EMULATE_PREPARES is used to allow variables ($dates) to be  
        // concatinated in the MySQL query.
        DBConn::setPDOAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        if(false && count($mergedTeamIds) > 0) {
            // TO DO: Debug the WHERE NOT IN clause on Google Cloud
            // This code works locally.
            $placeholders = str_repeat ('?, ',  count ($mergedTeamIds) - 1) . '?';
            $variables[] = (int)$count;
            if($dates !== '') {
                $dates = "AND $dates ";
            }
            
            $leaderboard = DBConn::selectAll("SELECT t.id AS teamId, t.name AS teamName, "
                    . "v.id AS homeJointId, v.name AS homeJoint, "
                    . "COALESCE(SUM(s.score),0) AS score, count(s.game_id) AS gameCheckins "
                    . "FROM trv_teams AS t "
                    . "JOIN trv_game_score_teams AS s ON s.team_id = t.id "
                    . "JOIN trv_games AS g ON g.id = s.game_id "
                    . "LEFT JOIN trv_venues AS v ON v.id = t.home_venue_id "
                    . "WHERE t.id NOT IN($placeholders) "
                    . $dates
                    . "GROUP BY s.team_id "
                    . "ORDER BY score DESC "
                    . "LIMIT ?;", $variables);
        } else {    
            if($dates !== '') {
                $dates = "WHERE $dates ";
            }
            
            $leaderboard = DBConn::selectAll("SELECT t.id AS teamId, t.name AS teamName, "
                . "v.id AS homeJointId, v.name AS homeJoint, "
                . "COALESCE(SUM(s.score),0) AS score, count(s.game_id) AS gameCheckins "
                . "FROM trv_teams AS t "
                . "JOIN trv_game_score_teams AS s ON s.team_id = t.id "
                . "JOIN trv_games AS g ON g.id = s.game_id "
                . "LEFT JOIN trv_venues AS v ON v.id = t.home_venue_id "
                . $dates
                . "GROUP BY s.team_id "
                . "LIMIT :limit;", array(':limit' => (int)$count));
        }
        DBConn::setPDOAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        return $leaderboard;
    }

    static function selectTeamLiveScoreByVenueAndCity($venueName, $venueCity, $count, $startDate, $endDate, $mergedTeamIds = array()) {   
        $dates = self::getWhereStartAndEnd($startDate, $endDate);
        if($dates !== '') {
            $dates = "AND $dates ";
        }
             
        // PDO::ATTR_EMULATE_PREPARES is used to allow variables ($dates) to be  
        // concatinated in the MySQL query.
        DBConn::setPDOAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $results = DBConn::selectAll("SELECT t.id AS teamId, t.name AS teamName, "
                . "IFNULL(s.score, '0') AS score, v.id AS homeJointId, v.name AS homeJoint "
                . "FROM trv_teams AS t "
                . "LEFT JOIN trv_game_score_teams AS s ON s.team_id = t.id "
                . "LEFT JOIN trv_games AS g ON g.id = s.game_id "
                . "LEFT JOIN trv_venues AS v ON v.id = t.home_venue_id "
                . "WHERE v.name = :venue_name AND v.city = :venue_city "
                . $dates
                . "GROUP BY t.id LIMIT $count;",  
                array(':venue_name' => $venueName, ':venue_city' => $venueCity));
        DBConn::setPDOAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        return $results;
    }

    static function selectTeamLiveScoreByNameAndVenue($teamName, $homeVenue,  $startDate, $endDate) {   
        $dates = self::getWhereStartAndEnd($startDate, $endDate);
        if($dates !== '') {
            $dates = "AND $dates ";
        }
             
        // PDO::ATTR_EMULATE_PREPARES is used to allow variables ($dates) to be  
        // concatinated in the MySQL query.
        DBConn::setPDOAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $results = DBConn::selectOne("SELECT t.id AS teamId, t.name AS teamName, "
                . "IFNULL(s.score, '0') AS score, v.id AS homeVenueId, v.name AS homeVenue "
                . "FROM trv_teams AS t "
                . "LEFT JOIN trv_game_score_teams AS s ON s.team_id = t.id "
                . "LEFT JOIN trv_games AS g ON g.id = s.game_id "
                . "LEFT JOIN trv_venues AS v ON v.id = t.home_venue_id "
                . "WHERE t.name = :team_name AND v.name = :home_venue_name "
                . $dates
                . "LIMIT 1;", 
                array(':team_name' => $teamName, ':home_venue_name' => $homeVenue), \PDO::FETCH_ASSOC);
        DBConn::setPDOAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        return $results;
    }
    
    static function selectTeamLiveCheckinsByVenueAndCity($venueName, $venueCity, $count, $startDate, $endDate, $mergedTeamIds = array()) {
        $dates = self::getWhereStartAndEnd($startDate, $endDate);
        if($dates !== '') {
            $dates = "AND $dates ";
        }

        // PDO::ATTR_EMULATE_PREPARES is used to allow variables ($dates) to be  
        // concatinated in the MySQL query.
        DBConn::setPDOAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $results = DBConn::selectAll("SELECT t.id AS teamId, t.name AS teamName, v.id AS homeJointId, "
                . "v.name AS homeJoint, COUNT(s.game_id) AS gameCheckins "
                . "FROM trv_teams AS t "
                . "LEFT JOIN trv_game_score_teams AS s ON s.team_id = t.id "
                . "LEFT JOIN trv_games AS g ON g.id = s.game_id "
                . "LEFT JOIN trv_venues AS v ON v.id = t.home_venue_id "
                . "WHERE v.name = :venue_name AND v.city = :venue_city "
                . $dates
                . "GROUP BY t.id LIMIT $count;",   
                array(':venue_name' => $venueName, ':venue_city' => $venueCity));
        DBConn::setPDOAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        return $results;
    }
    
    static function selectTeamLiveCheckinsByNameAndVenue($teamName, $homeVenue,  $startDate, $endDate) {
        $dates = self::getWhereStartAndEnd($startDate, $endDate);
        if($dates !== '') {
            $dates = "AND $dates ";
        }
        
        // PDO::ATTR_EMULATE_PREPARES is used to allow variables ($dates) to be  
        // concatinated in the MySQL query.
        DBConn::setPDOAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $results = DBConn::selectOne("SELECT t.id AS teamId, t.name AS teamName, v.id AS homeVenueId, "
                . "v.name AS homeVenue, COUNT(s.game_id) AS gameCheckins "
                . "FROM trv_teams AS t "
                . "LEFT JOIN trv_game_score_teams AS s ON s.team_id = t.id "
                . "LEFT JOIN trv_games AS g ON g.id = s.game_id "
                . "LEFT JOIN trv_venues AS v ON v.id = t.home_venue_id "
                . "WHERE v.name = :home_venue_name AND t.name = :team_name "
                . $dates
                . "GROUP BY s.team_id LIMIT 1;",
                array(':team_name' => $teamName, ':home_venue_name' => $homeVenue), \PDO::FETCH_ASSOC);
        DBConn::setPDOAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        return $results;
    }
}