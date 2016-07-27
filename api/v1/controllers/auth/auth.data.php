<?php namespace API;
 require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/services/api.dbconn.php';

class AuthData {
    
  
    
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
}
