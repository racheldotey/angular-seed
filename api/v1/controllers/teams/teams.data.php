<?php namespace API;
 require_once dirname(dirname(dirname(__FILE__))) . '/services/api.dbconn.php';

class TeamData {
  
    static function getTeam($id) {
        $team = DBConn::selectOne("SELECT t.id, t.name, t.created, t.last_updated AS updated, "
                . "CONCAT(u1.name_first, ' ', u1.name_last) AS createdBy, "
                . "CONCAT(u2.name_first, ' ', u2.name_last) AS updatedBy "
                . "FROM " . DBConn::prefix() . "teams AS t "
                . "LEFT JOIN " . DBConn::prefix() . "users AS u1 ON u1.id = t.created_user_id "
                . "LEFT JOIN " . DBConn::prefix() . "users AS u2 ON u2.id = t.last_updated_by WHERE t.id = :id LIMIT 1;", array(':id' => $id));
        
        if($team) {
            $qMembers = DBConn::preparedQuery("SELECT m.joined, u.id, u.name_first AS nameFirst, u.name_last AS nameLast, "
                    . "CONCAT(u.name_first, ' ', u.name_last) AS displayName "
                    . "FROM " . DBConn::prefix() . "team_members AS m "
                    . "LEFT JOIN " . DBConn::prefix() . "users AS u ON u.id = m.user_id "
                    . "WHERE m.team_id = :id;");

            $qGames = DBConn::preparedQuery("SELECT g.id, g.name, g.scheduled, g.max_points, s.score, s.game_winner, "
                    . "v.id AS venueId, v.name AS venue, v.address AS venueAddress, v.address_b AS venueAddressB, "
                    . "v.city AS venueCity, v.state AS stateVenue, v.zip AS venueZip, v.phone AS venuePhone, v.website AS venueWebsite, "
                    . "CONCAT(h.name_first, ' ', h.name_last) AS host, h.id AS hostId "
                    . "FROM " . DBConn::prefix() . "games AS g "
                    . "LEFT JOIN " . DBConn::prefix() . "game_score_teams AS s ON g.id = s.game_id "
                    . "LEFT JOIN " . DBConn::prefix() . "venues AS v ON v.id = g.venue_id "
                    . "LEFT JOIN " . DBConn::prefix() . "users AS h ON h.id = g.host_user_id "
                    . "WHERE s.team_id = :id;");
            
            $qMembers->execute(array(':id' => $id));
            $team->members = $qMembers->fetchAll(\PDO::FETCH_OBJ);
            
            $qGames->execute(array(':id' => $id));
            $team->games = $qGames->fetchAll(\PDO::FETCH_OBJ);
        }
        return $team;
    }
    
    static function selectUserByEmail($email) {
        return DBConn::selectOne("SELECT id, email, CONCAT(name_first, ' ', name_last) AS displayName "
                . "FROM " . DBConn::prefix() . "users WHERE email = :email LIMIT 1;", array(':email' => $email));
    }
    
    static function selectUserById($userId) {
        return DBConn::selectOne("SELECT id, email, CONCAT(name_first, ' ', name_last) AS displayName "
                . "FROM " . DBConn::prefix() . "users WHERE id = :id LIMIT 1;", array(':id' => $userId));
    }


    static function insertTeam($validTeam) {
        $find = $validTeam[':name'] . '%';
        $num = DBConn::selectColumn("SELECT count(id) AS num FROM " . DBConn::prefix() . "teams "
                . "WHERE name LIKE :name;", array(':name' => $find));
        
        if($num > 0) {
            $validTeam[':name'] = $validTeam[':name'] . ' ' . $num;
        }
        
        return DBConn::insert("INSERT INTO " . DBConn::prefix() . "teams(name, home_venue_id, created_user_id, last_updated_by) "
                . "VALUES (:name, :home_venue_id, :created_user_id, :last_updated_by);", $validTeam);
    }
    
    static function updateTeam($validTeam) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "teams SET name=:name, last_updated_by=:last_updated_by "
                . "WHERE id = :id;", $validTeam);
    }
    
    static function addTeamMember($validTeam) {
        return DBConn::insert("INSERT INTO " . DBConn::prefix() . "team_members(user_id, team_id, added_by) "
                . "VALUES (:user_id, :team_id, :added_by);", $validTeam);
    }
    
    static function deleteTeamMember($validTeam) {
        return DBConn::delete("DELETE FROM " . DBConn::prefix() . "team_members "
                . "WHERE user_id = :user_id AND team_id = :team_id;", $validTeam);
    }
    
    static function isUserATeamMember($validTeam) {
        return DBConn::selectAll("SELECT id FROM " . DBConn::prefix() . "team_members "
                . "WHERE user_id = :user_id AND team_id = :team_id;", $validTeam);
    }
    
    static function deleteTeam($id) {
        $fields = DBConn::delete("DELETE FROM " . DBConn::prefix() . "lookup_team_field WHERE team_id = :id;", array('id' => $id));
        $groups = DBConn::delete("DELETE FROM " . DBConn::prefix() . "lookup_group_team WHERE team_id = :id;", array('id' => $id));
        
        return DBConn::delete("DELETE FROM " . DBConn::prefix() . "teams WHERE id = :id LIMIT 1;", array('id' => $id));
    }
}
