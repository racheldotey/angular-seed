<?php namespace API;
require_once dirname(dirname(dirname(__FILE__))) . '/services/api.dbconn.php';
class VenueData {
    public static function getVenueByUser($userid) {
        $venue = DBConn::selectOne("SELECT *"
            . "FROM " . DBConn::prefix() . "venues AS r WHERE r.created_user_id = :id;", array(':id' => $userid));
       $triviaSchedule=self::getVenueTriviaSchedule($venue->id);
       $venue->trivia_day=(!empty($triviaSchedule))?$triviaSchedule->trivia_day:null;
       $venue->trivia_time=(!empty($triviaSchedule))?$triviaSchedule->trivia_time:null;

        return $venue;
    }
    
    public static function getVenue($id) {
        $venue = DBConn::selectOne("SELECT r.id, r.venue, r.desc, r.created, r.last_updated AS lastUpdated, "
            . "CONCAT(u1.name_first, ' ', u1.name_last) AS createdBy, "
            . "CONCAT(u2.name_first, ' ', u2.name_last) AS updatedBy "
            . "FROM " . DBConn::prefix() . "venues AS r "
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
    
    public static function getVenueTriviaSchedule($id) 
    {
        $venue = DBConn::selectOne("SELECT * from ".DBConn::prefix()."venues_trivia_schedules where venue_id = :id", array(':id' => $id));
        return $venue;
    }
    
    public static function insertVenue($validVenue) {
        return DBConn::insert("INSERT INTO " . DBConn::prefix() . "venues(name, address, address_b, city, state, zip, phone_extension, phone, website, facebook_url, logo, referral, created_user_id, last_updated_by) "
            . "VALUES (:name, :address, :address_b, :city, :state, :zip,:phone_extension, :phone, :website, :facebook_url, :logo, :referral, :created_user_id, :last_updated_by)", $validVenue);
    }
    
    public static function insertVenueTriviaSchedules($validVenueSchedules) 
    {
        return DBConn::insert("INSERT INTO " . DBConn::prefix() . "venues_trivia_schedules(trivia_day,trivia_time,created_user_id,last_updated_by,venue_id) "
            . "VALUES (:trivia_day,:trivia_time,:created_user_id,:last_updated_by,:venue_id)", $validVenueSchedules);
    }
    
    public static function insertVenueRoleAssignment($assignment) {
        return DBConn::insert("INSERT INTO " . DBConn::prefix() . "venue_roles(venue_id, user_id, role) "
            . "VALUES (:venue_id, :user_id, :role)", $assignment);
    }
    
    public static function updatedataVenue($validVenueData, $venueId){
        DBConn::update("UPDATE " . DBConn::prefix() . "venues SET name=:name, address=:address, address_b=:address_b, city=:city, state=:state, zip=:zip,phone_extension = :phone_extension, phone=:phone, website=:website, facebook_url=:facebook_url,logo=:logo, referral=:referral, last_updated=:last_updated, last_updated_by=:last_updated_by WHERE created_user_id = :created_user_id;", $validVenueData);
        $venue= DBConn::selectOne("SELECT * "
            . "FROM " . DBConn::prefix() . "venues WHERE created_user_id = :created_user_id LIMIT 1;", array(':created_user_id' => $venueId));
        return $venue;
    }
    
    public static function updateVenue($validVenue) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "venues SET name=:name, address=:address, address_b=:address_b, "
            . "city=:city, state=:state, zip=:zip, phone_extension=:phone_extension, phone=:phone, website=:website, facebook_url=:facebook_url, "
            . "logo=:logo, referral=:referral, last_updated_by=:last_updated_by "
            . "WHERE id = :id;", $validVenue);
    }
    
    public static function updateVenueTriviaSchedules($validVenueSchedule) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "venues_trivia_schedules SET "
            . " trivia_day=:trivia_day,"
            . " trivia_time=:trivia_time,"
            . " created_user_id=:created_user_id,"
            . " last_updated_by=:last_updated_by"
            . " WHERE venue_id=:venue_id;", $validVenueSchedule);
    }
    
    public static function manageVenueTriviaShcedule($post_array,$venue_id = 0) {
        $exist_venue_check = self::getVenueTriviaSchedule($venue_id);
       
        if(!empty($exist_venue_check)) {
            return self::updateVenueTriviaSchedules($post_array);
        } else{
            return self::insertVenueTriviaSchedules($post_array);
        }
    }
    
    public static function deleteVenue($id) {
        return false;//DBConn::delete("DELETE FROM " . DBConn::prefix() . "venues WHERE id = :id LIMIT 1;", array('id' => $id));
    }
    
    
    static function disableVenue($venueId) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "venues SET disabled=NOW() WHERE id = :id AND disabled IS NULL;", array(':id' => $venueId));
    }
    
    static function enableVenue($venueId) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "venues SET disabled=NULL WHERE id = :id AND disabled IS NOT NULL;", array(':id' => $venueId));
    }
}
