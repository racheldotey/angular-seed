<?php namespace API;
require_once dirname(dirname(dirname(__FILE__))) . '/services/api.dbconn.php';
class VenueData {
    public static function getVenueByUser($userid) {
        $venueId = DBConn::selectColumn("SELECT venue_id FROM " . DBConn::prefix() . "venue_roles "
            . "WHERE user_id = :id AND role = 'owner' LIMIT 1;", array(':id' => $userid));

        return ($venueId) ? self::getVenue($venueId) : false;
    }
    
    public static function getVenue($id) {
        return DBConn::selectOne("SELECT v.id, v.name AS venue, v.address, v.address_b AS addressb, "
            . "v.city, v.state, v.zip, v.phone, v.phone_extension AS phoneExtension, "
            . "v.website, v.facebook_url as facebook, v.logo, v.referral AS referralCode, v.created, v.disabled, "
            . "CONCAT(u1.name_first, ' ', u1.name_last) AS createdBy, "
            . "CONCAT(u2.name_first, ' ', u2.name_last) AS updatedBy, "
            . "vs.trivia_day AS triviaDay, vs.trivia_time AS triviaTime "
            . "FROM " . DBConn::prefix() . "venues AS v "
            . "LEFT JOIN " . DBConn::prefix() . "users AS u1 ON u1.id = v.created_user_id "
            . "LEFT JOIN " . DBConn::prefix() . "users AS u2 ON u2.id = v.last_updated_by "
            . "LEFT JOIN " . DBConn::prefix() . "venues_trivia_schedules AS vs ON vs.venue_id = v.id "
            . "WHERE v.id = :id;", array(':id' => $id));
    }


    public static function getallVenue() {
        $qvenues = DBConn::executeQuery("SELECT v.id, v.name AS venue, v.address, v.address_b AS addressb, "
            . "v.city, v.state, v.zip, v.phone, v.phone_extension AS phoneExtension, "
            . "v.website, v.facebook_url as facebook, v.logo, v.referral AS referralCode, v.created, v.disabled, "
            . "CONCAT(u1.name_first, ' ', u1.name_last) AS createdBy, "
            . "CONCAT(u2.name_first, ' ', u2.name_last) AS updatedBy, "
            . "vs.trivia_day AS triviaDay, vs.trivia_time AS triviaTime "
            . "FROM " . DBConn::prefix() . "venues AS v "
            . "LEFT JOIN " . DBConn::prefix() . "users AS u1 ON u1.id = v.created_user_id "
            . "LEFT JOIN " . DBConn::prefix() . "users AS u2 ON u2.id = v.last_updated_by "
            . "LEFT JOIN " . DBConn::prefix() . "venues_trivia_schedules AS vs ON vs.venue_id = v.id "
            );
        $venues_response = Array();
        while ($row = $qvenues->fetch(\PDO::FETCH_OBJ)) {
            $venues_response[] = $row;
        }
        return $venues_response;
    }
    public static function getVenueTriviaSchedule($id) {
        $venue = DBConn::selectOne("SELECT * from " . DBConn::prefix() . "venues_trivia_schedules where venue_id = :id", array(':id' => $id));
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

    public static function updateVenue($validVenue) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "venues SET name=:name, address=:address, address_b=:address_b, "
            . "city=:city, state=:state, zip=:zip, phone_extension=:phone_extension, phone=:phone, "
            . "website=:website, facebook_url=:facebook_url, logo=:logo, referral=:referral, last_updated_by=:last_updated_by "
            . "WHERE id = :id;", $validVenue);
    }

    public static function updateUser($validUser) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "users SET name_first=:name_first, name_last=:name_last, "
            . "last_updated_by=:last_updated_by WHERE id = :id;", $validUser);
    }
    public static function updateVenueOwner($validVenue) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "venues SET created_by_user_type=:created_by_user_type"
            . " WHERE id = :id;", $validVenue);
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
        /*code to remove trivia day time*/
        return false;//DBConn::delete("DELETE FROM " . DBConn::prefix() . "venues WHERE id = :id LIMIT 1;", array('id' => $id));
    }
    
    
    static function disableVenue($venueId) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "venues SET disabled=NOW() WHERE id = :id AND disabled IS NULL;", array(':id' => $venueId));
    }
    
    static function enableVenue($venueId) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "venues SET disabled=NULL WHERE id = :id AND disabled IS NOT NULL;", array(':id' => $venueId));
    }
}
