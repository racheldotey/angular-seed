<?php namespace API;
 require_once dirname(dirname(dirname(__FILE__))) . '/services/api.dbconn.php';

class LeaderboardData {
    
    static function selectVenueList() {
        return DBConn::selectAll("SELECT v.id, v.name AS joint, v.address, v.address_b AS addressb, "
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
    }

}