<?php namespace API;
 require_once dirname(dirname(dirname(__FILE__))) . '/services/api.dbconn.php';

class EmailData {
    
    static function updateInviteLastVisited($token) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "tokens_player_invites SET "
                . "last_visited=NOW() WHERE token = :token LIMIT 1;", array(':token' => $token));
    }
    
    static function selectInviteByToken($token) {
        return DBConn::selectOne("SELECT token, team_id AS teamId, user_id AS userId, "
                . "name_first AS nameFirst, name_last AS nameLast, email, phone, "
                . "created, created_user_id AS invitedBy, expires, last_visited AS lastVisited "
                . "FROM " . DBConn::prefix() . "tokens_player_invites "
                . "WHERE token = :token AND expires >= NOW() LIMIT 1;", array(':token' => $token));
    }
    
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
    
    static function selectUserIdByEmail($email) {
        return DBConn::selectColumn("SELECT id FROM " . DBConn::prefix() . "users "
                . "WHERE email = :email LIMIT 1;", array(':email' => $email));
    }
}
