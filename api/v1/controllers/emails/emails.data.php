<?php namespace API;
 require_once dirname(dirname(dirname(__FILE__))) . '/services/api.dbconn.php';

class EmailData {
    
    static function insertTeamInvite($validInvite) {
        return DBConn::insert("INSERT INTO " . DBConn::prefix() . "tokens_player_invites"
                . "(token, team_id, user_id, name_first, name_last, email, "
                . "phone, created_user_id, expires) VALUES "
                . "(:token, :team_id, :user_id, :name_first, :name_last, :email, "
                . ":phone, :created_user_id, DATE_ADD( NOW(), INTERVAL 24 HOUR ));", $validInvite);
    }
    
    static function insertPlayerInvite($validInvite) {
        return DBConn::insert("INSERT INTO " . DBConn::prefix() . "tokens_player_invites"
                . "(token, name_first, name_last, email, phone, created_user_id, expires) VALUES "
                . "(:token, :name_first, :name_last, :email, :phone, :created_user_id, "
                . "DATE_ADD( NOW(), INTERVAL 24 HOUR ));", $validInvite);
    }
}
