<?php namespace API;
 require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/services/api.dbconn.php';

class AuthData {
    
  
    static function insertUser($validUser) {
        $userId = DBConn::insert("INSERT INTO " . DBConn::prefix() . "users(name_first, name_last, email, phone, password) "
                . "VALUES (:name_first, :name_last, :email, :phone, :password);", $validUser);
        if($userId) {
            GroupData::addDefaultGroupToUser($userId);
        }
        return $userId;
    }
  
    static function insertFacebookUser($validUser) {
        $userId = DBConn::insert("INSERT INTO " . DBConn::prefix() . "users(name_first, name_last, email, facebook_id) "
                . "VALUES (:name_first, :name_last, :email, :facebook_id);", $validUser);
        if($userId) {
            GroupData::addDefaultGroupToUser($userId);
        }
        return $userId;
    }
    
    static function deleteAuthToken($identifier) {
        return DBConn::delete('DELETE FROM ' . DBConn::prefix() . 'tokens_auth WHERE identifier = :identifier;', $identifier);
    }
    
    static function deleteExpiredAuthTokens() {
        return DBConn::executeQuery('DELETE FROM ' . DBConn::prefix() . 'tokens_auth WHERE expires < NOW();');
    }
    
    static function updateUserFacebookId($validUser) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "users SET facebook_id = :facebook_id WHERE id = :id;", $validUser);
    }
    
    static function updateforgotpassworddata($forgotpwdperms) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "users SET usertoken = :usertoken,fortgotpassword_duration = :fortgotpassword_duration WHERE email = :email;", $forgotpwdperms);
    }
        
    static function updateUserPassword($validUser) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "users SET password = :password WHERE id = :id;", $validUser);
    }

    /* Select User */

    static function selectUserById($id) {
        return self::selectUserWhere('id = :id', array(':id' => $id));
    }
    
    static function selectUserByFacebookId($facebookId) {
        return self::selectUserWhere('facebook_id = :facebook_id', array(':facebook_id' => $facebookId));
    }

    static function selectUserByUsertoken($usertoken) {
        return DBConn::selectOne("SELECT email FROM " . DBConn::prefix() . "users "
                . "WHERE usertoken = :usertoken LIMIT 1;", array(':usertoken' => $usertoken));
    }

    static function selectUsertokenExpiry($email) {
        return DBConn::selectOne("SELECT fortgotpassword_duration FROM " . DBConn::prefix() . "users "
                . "WHERE email = :email LIMIT 1;", array(':email' => $email));
    }
    
    private static function selectUserWhere($where, $params) {
        $user = DBConn::selectOne("SELECT id, name_first as nameFirst, name_last as nameLast, email, phone "
                        . "FROM " . DBConn::prefix() . "users WHERE {$where} LIMIT 1;", $params);
        if ($user) {
            $user = self::selectUserData($user);
        }
        return $user;
    }
    
    
    /* Password Updating */

    static function resetUserPassword($resetpwdperms) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "users SET password = :password, usertoken = :usertoken,fortgotpassword_duration = :fortgotpassword_duration  WHERE email = :email;", $resetpwdperms);
    }
    
    static function selectUserPasswordById($userId) {
        return DBConn::selectColumn("SELECT password FROM " . DBConn::prefix() . "users WHERE id = :id LIMIT 1;", array(':id' => $userId));
    }
    
    static function selectUserPasswordByEmail($email) {
        return DBConn::selectColumn("SELECT password FROM " . DBConn::prefix() . "users WHERE email = :email LIMIT 1;", array(':email' => $email));
    }
    
    // Player invite
    
    static function selectSignupInvite($token) {
        return DBConn::selectColumn("SELECT team_id AS teamId FROM " . DBConn::prefix() . "tokens_player_invites "
                . "WHERE user_id IS NULL AND response IS NULL AND expires >= NOW() "
                . "AND token = :token LIMIT 1;", array(':token' => $token));
    }
    
    static function updateAcceptSignupInvite($validInvite) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "tokens_player_invites "
                . "SET user_id =:user_id, response='accepted', last_visited=NOW() "
                . "WHERE token = :token LIMIT 1;", $validInvite);
    }
    
    static function updateAcceptSignupTeamInvite($validInvite) {        
        DBConn::insert("INSERT INTO " . DBConn::prefix() . "team_members(user_id, team_id, added_by) "
                . "VALUES (:user_id, :team_id, :added_by);", 
                array(':user_id' => $validInvite[':user_id'],  ':team_id' => $validInvite[':team_id'], ':added_by' => $validInvite[':user_id']));
        
        return DBConn::update("UPDATE " . DBConn::prefix() . "tokens_player_invites SET "
                . "response='accepted', user_id = :user_id WHERE token = :token "
                . "AND team_id = :team_id AND response IS NULL AND expires >= NOW() LIMIT 1;", $validInvite);
    }
}
