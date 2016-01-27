<?php namespace API;
 require_once dirname(dirname(dirname(__FILE__))) . '/services/api.dbconn.php';

class GameData {
  
    public static function getGame($id) {
        $game = DBConn::selectOne("SELECT r.id, r.game, r.desc, r.created, r.last_updated AS lastUpdated, "
                . "CONCAT(u1.name_first, ' ', u1.name_last) AS createdBy, "
                . "CONCAT(u2.name_first, ' ', u2.name_last) AS updatedBy "
                . "FROM as_auth_games AS r "
                . "JOIN as_users AS u1 ON u1.id = r.created_user_id "
                . "JOIN as_users AS u2 ON u2.id = r.last_updated_by WHERE r.id = :id;", array(':id' => $id));

        $qFields = DBConn::preparedQuery("SELECT e.id, e.identifier, e.desc "
                . "FROM as_auth_fields AS e "
                . "JOIN as_auth_lookup_game_field AS look ON e.id = look.auth_field_id "
                . "WHERE look.auth_game_id = :id ORDER BY e.identifier;");
        
        $qGroups = DBConn::preparedQuery("SELECT g.id, g.group, g.desc "
                . "FROM as_auth_groups AS g "
                . "JOIN as_auth_lookup_group_game AS look ON g.id = look.auth_group_id "
                . "WHERE look.auth_game_id = :id ORDER BY g.group;");
        
        if($game) {
            $qGroups->execute(array(':id' => $id));
            $game->groups = $qGroups->fetchAll(\PDO::FETCH_OBJ);
            
            $qFields->execute(array(':id' => $id));
            $game->elements = $qFields->fetchAll(\PDO::FETCH_OBJ);
        }
        return $game;
    }
  
    public static function insertGame($validGame) {
        return DBConn::insert("INSERT INTO as_auth_games(game, slug, desc, created_user_id, last_updated_by) "
                . "VALUES (:game, :slug, :desc, :created_user_id, :last_updated_by)", $validGame);
    }
    
    public static function updateGame($validGame) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "auth_games SET game=:game, slug=:slug, "
                . "desc=:desc, last_updated_by=:last_updated_by;", $validGame);
    }
    
    public static function deleteGame($id) {
        $fields = DBConn::delete("DELETE FROM " . DBConn::prefix() . "auth_lookup_game_field WHERE auth_game_id = :id;", array('id' => $id));
        $groups = DBConn::delete("DELETE FROM " . DBConn::prefix() . "auth_lookup_group_game WHERE auth_game_id = :id;", array('id' => $id));
        
        return (!$fields || !$groups)  ? false :
            DBConn::delete("DELETE FROM " . DBConn::prefix() . "auth_games WHERE id = :id LIMIT 1;", array('id' => $id));
    }
}
