<?php namespace API;
 require_once dirname(dirname(dirname(__FILE__))) . '/services/api.dbconn.php';

class VenueData {
  
    public static function getVenue($id) {
        $venue = DBConn::selectOne("SELECT r.id, r.venue, r.desc, r.created, r.last_updated AS lastUpdated, "
                . "CONCAT(u1.name_first, ' ', u1.name_last) AS createdBy, "
                . "CONCAT(u2.name_first, ' ', u2.name_last) AS updatedBy "
                . "FROM " . DBConn::prefix() . "auth_venues AS r "
                . "JOIN " . DBConn::prefix() . "users AS u1 ON u1.id = r.created_user_id "
                . "JOIN " . DBConn::prefix() . "users AS u2 ON u2.id = r.last_updated_by WHERE r.id = :id;", array(':id' => $id));

        $qFields = DBConn::preparedQuery("SELECT e.id, e.identifier, e.desc "
                . "FROM " . DBConn::prefix() . "auth_fields AS e "
                . "JOIN " . DBConn::prefix() . "auth_lookup_venue_field AS look ON e.id = look.auth_field_id "
                . "WHERE look.auth_venue_id = :id ORDER BY e.identifier;");
        
        $qGroups = DBConn::preparedQuery("SELECT g.id, g.group, g.desc "
                . "FROM " . DBConn::prefix() . "auth_groups AS g "
                . "JOIN " . DBConn::prefix() . "auth_lookup_group_venue AS look ON g.id = look.auth_group_id "
                . "WHERE look.auth_venue_id = :id ORDER BY g.group;");
        
        if($venue) {
            $qGroups->execute(array(':id' => $id));
            $venue->groups = $qGroups->fetchAll(\PDO::FETCH_OBJ);
            
            $qFields->execute(array(':id' => $id));
            $venue->elements = $qFields->fetchAll(\PDO::FETCH_OBJ);
        }
        return $venue;
    }
  
    public static function insertVenue($validVenue) {
        return DBConn::insert("INSERT INTO " . DBConn::prefix() . "auth_venues(venue, slug, desc, created_user_id, last_updated_by) "
                . "VALUES (:venue, :slug, :desc, :created_user_id, :last_updated_by)", $validVenue);
    }
    
    public static function updateVenue($validVenue) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "auth_venues SET venue=:venue, slug=:slug, "
                . "desc=:desc, last_updated_by=:last_updated_by;", $validVenue);
    }
    
    public static function deleteVenue($id) {
        $fields = DBConn::delete("DELETE FROM " . DBConn::prefix() . "auth_lookup_venue_field WHERE auth_venue_id = :id;", array('id' => $id));
        $groups = DBConn::delete("DELETE FROM " . DBConn::prefix() . "auth_lookup_group_venue WHERE auth_venue_id = :id;", array('id' => $id));
        
        return (!$fields || !$groups)  ? false :
            DBConn::delete("DELETE FROM " . DBConn::prefix() . "auth_venues WHERE id = :id LIMIT 1;", array('id' => $id));
    }
}
