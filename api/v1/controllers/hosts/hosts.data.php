<?php namespace API;
require_once dirname(dirname(dirname(__FILE__))) . '/services/api.dbconn.php';
class HostData 
{
    public static function getHostByUser($userid) 
    {
        $hostId = DBConn::selectColumn("SELECT id FROM " . DBConn::prefix() . "hosts "
            . "WHERE trv_users_id = :id LIMIT 1;", array(':id' => $userid));
        return ($hostId) ? self::getHost($hostId) : false;
    }
    public static function getHost($id){
        return DBConn::selectOne("SELECT h.id,h.trv_users_id,u1.name_first as nameFirst,u1.name_last as nameLast,u1.email, h.address, h.address_b AS addressb, "
            . "h.city, h.state, h.zip, h.phone, h.phone_extension AS phoneExtension, "
            . "h.website, h.facebook_url as facebook, h.created, h.disabled, "
            . "CONCAT(u1.name_first, ' ', u1.name_last) AS createdBy, "
            . "CONCAT(u2.name_first, ' ', u2.name_last) AS updatedBy, "
            . "hns.trivia_day AS triviaDay, hns.trivia_time AS triviaTime "
            . "FROM " . DBConn::prefix() . "hosts AS h "
            . "LEFT JOIN " . DBConn::prefix() . "users AS u1 ON u1.id = h.trv_users_id "
            . "LEFT JOIN " . DBConn::prefix() . "users AS u2 ON u2.id = h.last_updated_by "
            . "LEFT JOIN " . DBConn::prefix() . "hosts_trivia_nights AS hns ON hns.host_id = h.id "
            . "WHERE h.id = :id;", array(':id' => $id));
    }
    public static function insertHost($validHost) {
        return DBConn::insert("INSERT INTO " . DBConn::prefix() . "hosts(trv_users_id,address, address_b, city, state, zip, website, facebook_url,phone,phone_extension,created_user_id, last_updated_by,accepted_terms) "
            . "VALUES (:trv_users_id,:address, :address_b, :city, :state, :zip,:website, :facebook_url,:phone,:phone_extension,:created_user_id, :last_updated_by,:host_accepted_terms)", $validHost);
    }
    public static function insertHostTriviaSchedules($validHostSchedules){
        return DBConn::insert("INSERT INTO " . DBConn::prefix() . "hosts_trivia_nights(trivia_day,trivia_time,created_user_id,last_updated_by,venue_id,host_id) "
            . "VALUES (:trivia_day,:trivia_time,:created_user_id,:last_updated_by,:venue_id,:host_id)", $validHostSchedules);
    }
    public static function insertHostVenueAssignment($assignment) {
        return DBConn::insert("INSERT INTO " . DBConn::prefix() . "hosts_venues(host_id,venue_id, created_user_id,last_updated_by) "
            . "VALUES (:host_id,:venue_id,:created_user_id,:last_updated_by)", $assignment);
    }
    public static function updateHost($validHost){
        return DBConn::update("UPDATE " . DBConn::prefix() . "hosts SET address=:address, address_b=:address_b, city=:city, state=:state, zip=:zip, phone=:phone,  phone_extension=:phone_extension, website=:website, facebook_url=:facebook_url,last_updated_by=:last_updated_by WHERE id = :id;", $validHost);
    }
    public static function updateUser($validUser) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "users SET name_first=:name_first, name_last=:name_last, "
            . "last_updated_by=:last_updated_by WHERE id = :id;", $validUser);
    }
    public static function deleteHost($id) {
        /*delete host day & time*/
        /*if host is owner then delete venue and venue day and time*/
        return false;//DBConn::delete("DELETE FROM " . DBConn::prefix() . "venues WHERE id = :id LIMIT 1;", array('id' => $id));
    }
    static function disableHost($hostId) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "hosts SET disabled=NOW() WHERE id = :id AND disabled IS NULL;", array(':id' => $hostId));
    }
    static function enableHost($hostId) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "hosts SET disabled=NULL WHERE id = :id AND disabled IS NOT NULL;", array(':id' => $hostId));
    }
}
