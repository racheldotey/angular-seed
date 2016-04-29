<?php namespace API;
 require_once dirname(dirname(dirname(__FILE__))) . '/services/api.dbconn.php';

class DatatablesData {
    
    static function selectUsers() {
        $qUsers = DBConn::executeQuery("SELECT u.id, u.name_first AS nameFirst, u.name_last AS nameLast, "
                . "u.email, u.email_verified AS verified, u.created, u.last_updated AS lastUpdated, "
                . "u.disabled, CONCAT(u1.name_first, ' ', u1.name_last) AS updatedBy "
                . "FROM as_users AS u "
                . "LEFT JOIN as_users AS u1 ON u1.id = u.last_updated_by ORDER BY u.id;");
        
        $qGroups = DBConn::preparedQuery("SELECT grp.id, grp.group, grp.desc, look.created AS assigned "
                . "FROM as_auth_groups AS grp "
                . "JOIN as_auth_lookup_user_group AS look ON grp.id = look.auth_group_id "
                . "WHERE look.user_id = :id ORDER BY grp.group;");

        $users = Array();
        while($user = $qUsers->fetch(\PDO::FETCH_OBJ)) {
            $qGroups->execute(array(':id' => $user->id));
            $user->groups = $qGroups->fetchAll(\PDO::FETCH_OBJ);        
            array_push($users, $user);
        }
        return $users;
    }
    
    static function selectUserGroups() {
        $qGroups = DBConn::executeQuery("SELECT g.id, g.group, g.slug AS identifier, g.desc, g.created, g.last_updated AS lastUpdated, "
                . "CONCAT(u1.name_first, ' ', u1.name_last) AS createdBy, "
                . "CONCAT(u2.name_first, ' ', u2.name_last) AS updatedBy "
                . "FROM as_auth_groups AS g "
                . "JOIN as_users AS u1 ON u1.id = g.created_user_id "
                . "JOIN as_users AS u2 ON u2.id = g.last_updated_by ORDER BY g.group;");

        $qRoles = DBConn::preparedQuery("SELECT r.id, r.role, r.desc "
                . "FROM as_auth_roles AS r "
                . "JOIN as_auth_lookup_group_role AS look ON r.id = look.auth_role_id "
                . "WHERE look.auth_group_id = :id ORDER BY r.role;");

        $groups = Array();

        while($group = $qGroups->fetch(\PDO::FETCH_OBJ)) {
            $qRoles->execute(array(':id' => $group->id));
            $group->roles = $qRoles->fetchAll(\PDO::FETCH_OBJ);        
            array_push($groups, $group);
        }
        
        return $groups;
    }
    
    static function selectGroupRoles() {
        $qRoles = DBConn::executeQuery("SELECT r.id, r.role, r.slug AS identifier, r.desc, r.created, r.last_updated AS lastUpdated, "
                . "CONCAT(u1.name_first, ' ', u1.name_last) AS createdBy, "
                . "CONCAT(u2.name_first, ' ', u2.name_last) AS updatedBy "
                . "FROM as_auth_roles AS r "
                . "JOIN as_users AS u1 ON u1.id = r.created_user_id "
                . "JOIN as_users AS u2 ON u2.id = r.last_updated_by ORDER BY r.role;");

        $qFields = DBConn::preparedQuery("SELECT e.id, e.identifier, e.desc "
                . "FROM as_auth_fields AS e "
                . "JOIN as_auth_lookup_role_field AS look ON e.id = look.auth_field_id "
                . "WHERE look.auth_role_id = :id ORDER BY e.identifier;");
        
        $qGroups = DBConn::preparedQuery("SELECT g.id, g.group, g.desc "
                . "FROM as_auth_groups AS g "
                . "JOIN as_auth_lookup_group_role AS look ON g.id = look.auth_group_id "
                . "WHERE look.auth_role_id = :id ORDER BY g.group;");
        
        $roles = Array();

        while($role = $qRoles->fetch(\PDO::FETCH_OBJ)) {
            $qGroups->execute(array(':id' => $role->id));
            $role->groups = $qGroups->fetchAll(\PDO::FETCH_OBJ);
            
            $qFields->execute(array(':id' => $role->id));
            $role->elements = $qFields->fetchAll(\PDO::FETCH_OBJ);
            
            array_push($roles, $role);
        }
        
        return $roles;
    }
    
    static function selectConfigVariables() {
        return DBConn::selectAll("SELECT c.id, c.name, c.value, c.created, c.last_updated AS lastUpdated, c.disabled, c.indestructible, c.locked, "
                . "CONCAT(u1.name_first, ' ', u1.name_last) AS createdBy, "
                . "CONCAT(u2.name_first, ' ', u2.name_last) AS updatedBy "
                . "FROM as_system_config AS c "
                . "JOIN as_users AS u1 ON u1.id = c.created_user_id "
                . "JOIN as_users AS u2 ON u2.id = c.last_updated_by ORDER BY c.name;");
    }
    
    static function selectVisibilityFields() {
        $qFields = DBConn::executeQuery("SELECT e.id, e.identifier, e.type, e.desc, e.initialized, e.created, e.last_updated AS lastUpdated, "
                . "CONCAT(u1.name_first, ' ', u1.name_last) AS createdBy, CONCAT(u2.name_first, ' ', u2.name_last) AS updatedBy "
                . "FROM as_auth_fields AS e "
                . "JOIN as_users AS u1 ON u1.id = e.created_user_id "
                . "JOIN as_users AS u2 ON u2.id = e.last_updated_by ORDER BY e.identifier;");

        $qRoles = DBConn::preparedQuery("SELECT r.id, r.role, r.desc "
                . "FROM as_auth_roles AS r "
                . "JOIN as_auth_lookup_role_field AS look ON r.id = look.auth_role_id "
                . "WHERE look.auth_field_id = :id ORDER BY r.role;");
        
        $elements = Array();

        while($field = $qFields->fetch(\PDO::FETCH_OBJ)) {            
            $qRoles->execute(array(':id' => $field->id));
            $field->roles = $qRoles->fetchAll(\PDO::FETCH_OBJ);
            
            array_push($elements, $field);
        }
        
        return $elements;
    }
    
    // Admin Trivia
        
    static function selectTriviaGames() {
        return DBConn::selectAll("SELECT g.id, g.name, g.scheduled, g.venue_id AS venueId, g.host_user_id AS hostId, "
                . "game_started AS started, game_ended AS ended, max_points maxPoints, "
                . "CONCAT(u.name_first, ' ', u.name_last) AS host, v.name AS venue "
                . "FROM as_games AS g LEFT JOIN as_users AS u ON u.id = g.host_user_id "
                . "LEFT JOIN as_venues AS v ON v.id = g.venue_id;");
    }
        
    static function selectTriviaTeams() {
        $qTeams = DBConn::executeQuery("SELECT t.id, t.name AS team, t.created, "
                . "CONCAT(u.name_first, ' ', u.name_last) AS createdBy, "
                . "t.home_venue_id AS homeVenueId, hv.name AS homeVenue, " 
                . "g.id AS lastGameId, g.name AS lastGameName "
                . "FROM as_teams AS t "
                . "LEFT JOIN as_users AS u ON u.id = t.created_user_id "
                . "LEFT JOIN as_venues AS hv ON hv.id = t.home_venue_id "
                . "LEFT JOIN as_game_score_teams AS gt ON gt.team_id = t.id "
                . "LEFT JOIN as_games AS g ON gt.game_id = g.id GROUP BY t.id ORDER BY g.game_ended, t.name;");
        
        $qMembers = DBConn::preparedQuery("SELECT u.id, CONCAT(u.name_first, ' ', u.name_last) AS name, m.joined FROM as_team_members AS m JOIN as_users AS u ON m.user_id = u.id WHERE m.team_id = :team_id ORDER BY name;");
        
        $elements = Array();

        while($team = $qTeams->fetch(\PDO::FETCH_OBJ)) {            
            $qMembers->execute(array(':team_id' => $team->id));
            $team->members = $qMembers->fetchAll(\PDO::FETCH_OBJ);
            
            array_push($elements, $team);
        }
        
        return $elements;
    }
        
    static function selectTriviaVenues() {
        return DBConn::selectAll("SELECT v.id, v.name AS venue, v.city, v.state, v.website, v.facebook_url as facebook, v.logo, v.hours, v.referral, v.created, 
CONCAT(u.name_first, ' ', u.name_last) AS createdBy
FROM as_venues AS v
LEFT JOIN as_users AS u ON u.id = v.created_user_id
ORDER BY v.name;");
    }
    
    // Games    
    
    static function selectGames() {
        $qGames = DBConn::executeQuery("SELECT g.id, g.name, g.scheduled, g.venue_id AS venueId, g.host_user_id AS hostId, "
                . "g.game_started AS started, g.game_ended AS ended, g.max_points maxPoints, "
                . "CONCAT(u.name_first, ' ', u.name_last) AS host, v.name AS venue "
                . "FROM as_games AS g LEFT JOIN as_users AS u ON u.id = g.host_user_id "
                . "LEFT JOIN as_venues AS v ON v.id = g.venue_id "
                . "WHERE g.game_started IS NOT NULL;");
        return self::selectGameScoreboard($qGames);
    }
    
    static function selectHostGames($hostId) {
        $qGames = DBConn::executeQuery("SELECT g.id, g.name, g.scheduled, g.venue_id AS venueId, g.host_user_id AS hostId, "
                . "g.game_started AS started, g.game_ended AS ended, g.max_points maxPoints, "
                . "CONCAT(u.name_first, ' ', u.name_last) AS host, v.name AS venue "
                . "FROM as_games AS g LEFT JOIN as_users AS u ON u.id = g.host_user_id "
                . "LEFT JOIN as_venues AS v ON v.id = g.venue_id "
                . "WHERE g.host_user_id = :host_user_id;", array(':host_user_id' => $hostId));
        return self::selectGameScoreboard($qGames);
    }
    
    static function selectVenueGames($venueId) {
        $qGames = DBConn::executeQuery("SELECT g.id, g.name, g.scheduled, g.venue_id AS venueId, g.host_user_id AS hostId, "
                . "g.game_started AS started, g.game_ended AS ended, g.max_points maxPoints, "
                . "CONCAT(u.name_first, ' ', u.name_last) AS host, v.name AS venue "
                . "FROM as_games AS g LEFT JOIN as_users AS u ON u.id = g.host_user_id "
                . "LEFT JOIN as_venues AS v ON v.id = g.venue_id "
                . "WHERE g.venue_id = :venue_id;", array(':venue_id' => $venueId));
        return self::selectGameScoreboard($qGames);
    }
    
    static function selectTeamGames($teamId) {
        $qGames = DBConn::executeQuery("SELECT g.id, g.name, g.scheduled, g.venue_id AS venueId, g.host_user_id AS hostId, "
                . "g.game_started AS started, g.game_ended AS ended, g.max_points maxPoints, "
                . "CONCAT(u.name_first, ' ', u.name_last) AS host, v.name AS venue "
                . "FROM as_games AS g LEFT JOIN as_users AS u ON u.id = g.host_user_id "
                . "LEFT JOIN as_venues AS v ON v.id = g.venue_id "
                . "JOIN as_game_score_teams AS t ON t.game_id = g.id "
                . "WHERE t.team_id = :team_id;", array(':team_id' => $teamId));
        return self::selectGameScoreboard($qGames);
    }
    
    private static function selectGameScoreboard($qGames) {
        $qScores = DBConn::preparedQuery("SELECT s.team_id AS teamId, s.score AS gameScore, "
                . "s.game_rank AS gameRank, s.game_winner AS gameWinner, t.name AS teamName "
                . "FROM as_game_score_teams AS s "
                . "LEFT JOIN as_teams AS t ON s.team_id = t.id "
                . "WHERE s.game_id = :game_id ORDER BY s.game_rank");
        
        $elements = Array();

        while($game = $qGames->fetch(\PDO::FETCH_OBJ)) {            
            $qScores->execute(array(':game_id' => $game->id));
            $game->scoreboard = $qScores->fetchAll(\PDO::FETCH_OBJ);
            
            array_push($elements, $game);
        }
        
        return $elements;
    }
}
