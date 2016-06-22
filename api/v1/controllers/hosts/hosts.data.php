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
  /*. "LEFT JOIN " . DBConn::prefix() . "hosts_venues AS hv ON h.id = hv.host_id "*/
  public static function getHost($id)
  {
    $host =  DBConn::selectOne("SELECT h.id,h.trv_users_id,u1.name_first as nameFirst,u1.name_last as nameLast,u1.email, h.address, h.address_b AS addressb, "
      . "h.city, h.state, h.zip, h.phone, h.phone_extension AS phoneExtension, "
      . "h.website, h.facebook_url as facebook, h.created, h.disabled, "
      . "CONCAT(u1.name_first, ' ', u1.name_last) AS createdBy, "
      . "CONCAT(u2.name_first, ' ', u2.name_last) AS updatedBy "     
      . "FROM " . DBConn::prefix() . "hosts AS h "
      . "LEFT JOIN " . DBConn::prefix() . "users AS u1 ON u1.id = h.trv_users_id "
      . "LEFT JOIN " . DBConn::prefix() . "users AS u2 ON u2.id = h.last_updated_by "
      . "WHERE h.id = :id;", array(':id' => $id));
    $qvenues = DBConn::executeQuery("SELECT  v.*,"
      . "hns.trivia_day AS triviaDay, hns.trivia_time AS triviaTime, "
      . "hv.venue_id "
      . "FROM " . DBConn::prefix() . "hosts_venues AS hv "
      . "LEFT JOIN " . DBConn::prefix() . "venues AS v ON v.id = hv.venue_id "
      . "LEFT JOIN " . DBConn::prefix() . "hosts_trivia_nights AS hns ON hns.host_id = hv.host_id and hns.venue_id=hv.venue_id "
      . "WHERE hv.host_id = :id GROUP BY hv.venue_id;", array(':id' => $id));
    
    $venues_response = Array();
    while ($row = $qvenues->fetch(\PDO::FETCH_OBJ))
    {
      $venues_response[] = $row;
    }   
    $host->venues = $venues_response;
    return $host;
  }
  public static function insertHost($validHost) {
    return DBConn::insert("INSERT INTO " . DBConn::prefix() . "hosts(trv_users_id,address, address_b, city, state, zip, website, facebook_url,phone,phone_extension,created_user_id, last_updated_by,accepted_terms) "
      . "VALUES (:trv_users_id,:address, :address_b, :city, :state, :zip,:website, :facebook_url,:phone,:phone_extension,:created_user_id, :last_updated_by,:host_accepted_terms)", $validHost);
  }
  public static function getHostTriviaSchedule($hostId,$venueId) {
    $trivia_nights = DBConn::selectOne("SELECT * from " . DBConn::prefix() . "hosts_trivia_nights where host_id=:host_id and venue_id = :venue_id", array(':host_id' => $hostId,':venue_id' => $venueId));
    return $trivia_nights;
  }
  public static function insertHostTriviaSchedules($validHostSchedules){
    return DBConn::insert("INSERT INTO " . DBConn::prefix() . "hosts_trivia_nights(trivia_day,trivia_time,created_user_id,last_updated_by,venue_id,host_id) "
      . "VALUES (:trivia_day,:trivia_time,:created_user_id,:last_updated_by,:venue_id,:host_id)", $validHostSchedules);
  }
  public static function updateHostTriviaSchedules($validVenueSchedule) {
    return DBConn::update("UPDATE " . DBConn::prefix() . "hosts_trivia_nights SET "
      . " trivia_day=:trivia_day,"
      . " trivia_time=:trivia_time,"
      . " created_user_id=:created_user_id,"
      . " last_updated_by=:last_updated_by"
      . " WHERE host_id=:host_id and venue_id = :venue_id;", $validVenueSchedule);
  }
  public static function manageHostTriviaShcedule($post_array,$hostId,$venueId) {
    $trivia_nights = self::getHostTriviaSchedule($hostId,$venueId);
  
    if(!empty($trivia_nights)) {
      return self::updateHostTriviaSchedules($post_array);
    } else{
      return self::insertHostTriviaSchedules($post_array);
    }
  }
  public static function getHostVenueAssignment($assignment) {
    return DBConn::selectOne("select id FROM " . DBConn::prefix() . "hosts_venues where host_id=:host_id and venue_id=:venue_id", $assignment);
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
    return false;
        //DBConn::delete("DELETE FROM " . DBConn::prefix() . "venues WHERE id = :id LIMIT 1;", array('id' => $id));
  }
  static function disableHost($hostId) {
    return DBConn::update("UPDATE " . DBConn::prefix() . "hosts SET disabled=NOW() WHERE id = :id AND disabled IS NULL;", array(':id' => $hostId));
  }
  static function enableHost($hostId) {
    return DBConn::update("UPDATE " . DBConn::prefix() . "hosts SET disabled=NULL WHERE id = :id AND disabled IS NOT NULL;", array(':id' => $hostId));
  }
  public static function deleteVenueData($hostId,$venueId) {
    $hosts_venues_del_status = DBConn::delete("DELETE FROM " . DBConn::prefix() . "hosts_venues WHERE host_id = :hostId AND venue_id = :venueId LIMIT 1;", array('hostId' => $hostId,'venueId'=>$venueId));
    $hosts_trivia_nights_del_status = DBConn::delete("DELETE FROM " . DBConn::prefix() . "hosts_trivia_nights WHERE host_id = :hostId AND venue_id = :venueId LIMIT 1;", array('hostId' => $hostId,'venueId'=>$venueId));
    if($hosts_venues_del_status &&  $hosts_trivia_nights_del_status)
    {
      return true;
    }
    else{
      return false;
    }
  }
}