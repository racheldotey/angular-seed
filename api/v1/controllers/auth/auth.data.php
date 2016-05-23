<?php namespace API;
 require_once dirname(dirname(dirname(__FILE__))) . '/services/api.dbconn.php';

class AuthData {
    
  
    static function insertUser($validUser) {
        $userId = DBConn::insert("INSERT INTO as_users(name_first, name_last, email, phone, password) "
                . "VALUES (:name_first, :name_last, :email, :phone, :password);", $validUser);
        if($userId) {
            GroupData::addDefaultGroupToUser($userId);
        }
        return $userId;
    }
  
    static function insertFacebookUser($validUser) {
        $userId = DBConn::insert("INSERT INTO as_users(name_first, name_last, email, facebook_id) "
                . "VALUES (:name_first, :name_last, :email, :facebook_id);", $validUser);
        if($userId) {
            GroupData::addDefaultGroupToUser($userId);
        }
        return $userId;
    }
    
    static function insertAuthToken($validToken) {
        return DBConn::insert('INSERT INTO ' . DBConn::prefix() . 'tokens_auth(identifier, token, user_id, expires, ip_address, user_agent) '
                . 'VALUES (:identifier, :token, :user_id, :expires, :ip_address, :user_agent);', $validToken);
    }
    
    static function insertLoginLocation($validLog) {
        return DBConn::insert('INSERT INTO ' . DBConn::prefix() . 'logs_login_location(user_id, ip_address, user_agent) '
                . 'VALUES (:user_id, :ip_address, :user_agent);', $validLog);
    }
    
    static function deleteAuthToken($identifier) {
        return DBConn::delete('DELETE FROM ' . DBConn::prefix() . 'tokens_auth WHERE identifier = :identifier;', $identifier);
    }
    
    static function deleteExpiredAuthTokens() {
        return DBConn::executeQuery('DELETE FROM ' . DBConn::prefix() . 'tokens_auth WHERE expires < NOW();');
    }
    
    static function updateUserFacebookId($validUser) {
        return DBConn::update("UPDATE as_users SET facebook_id = :facebook_id WHERE id = :id;", $validUser);
    }
    
    static function updateforgotpassworddata($forgotpwdperms) {
        return DBConn::update("UPDATE as_users SET usertoken = :usertoken,fortgotpassword_duration = :fortgotpassword_duration WHERE email = :email;", $forgotpwdperms);
    }
        
    static function updateUserPassword($validUser) {
        return DBConn::update("UPDATE as_users SET password = :password WHERE id = :id;", $validUser);
    }

    /* Select User */

    static function selectUserById($id) {
        return self::selectUserWhere('id = :id', array(':id' => $id));
    }
    
    static function selectUserByFacebookId($facebookId) {
        return self::selectUserWhere('facebook_id = :facebook_id', array(':facebook_id' => $facebookId));
    }
    
    static function selectUserAndPasswordByEmail($email) {
        $user = DBConn::selectOne("SELECT id, name_first as nameFirst, name_last as nameLast, email, phone, password "
                        . "FROM as_users WHERE email = :email AND disabled IS NULL LIMIT 1;", array(':email' => $email));
        if ($user) {
            $user = self::selectUserData($user);
        }
        return $user;
    }

    static function selectUserByUsertoken($usertoken) {
        return DBConn::selectOne("SELECT email FROM as_users "
                . "WHERE usertoken = :usertoken LIMIT 1;", array(':usertoken' => $usertoken));
    }

    static function selectUsertokenExpiry($email) {
        return DBConn::selectOne("SELECT fortgotpassword_duration FROM as_users "
                . "WHERE email = :email LIMIT 1;", array(':email' => $email));
    }
    
    private static function selectUserWhere($where, $params) {
        $user = DBConn::selectOne("SELECT id, name_first as nameFirst, name_last as nameLast, email, phone "
                        . "FROM as_users WHERE {$where} LIMIT 1;", $params);
        if ($user) {
            $user = self::selectUserData($user);
        }
        return $user;
    }
    
    static function selectUserByIdentifierToken($identifier) {
        $user = DBConn::selectOne("SELECT u.id, name_first AS nameFirst, name_last AS nameLast, "
                . "email, phone, token AS apiToken, identifier AS apiKey "
                . "FROM as_tokens_auth AS t "
                . "JOIN as_users AS u ON u.id = t.user_id "
                . "WHERE identifier = :identifier AND t.expires > NOW() "
                . "AND u.disabled IS NULL;", array(':identifier' => $identifier));
        if($user) {
            $user = self::selectUserData($user);
        }
        return $user;
    }
    
    private static function selectUserData($user) {
        if ($user) {
            $user->displayName = $user->nameFirst;
            $user->roles = DBConn::selectAll("SELECT DISTINCT(gr.auth_role_id) "
                    . "FROM as_auth_lookup_user_group AS ug "
                    . "JOIN as_auth_lookup_group_role AS gr ON ug.auth_group_id = gr.auth_group_id "
                    . "WHERE ug.user_id = :id;", array(':id' => $user->id), \PDO::FETCH_COLUMN);
            
            $user->teams = DBConn::selectAll("SELECT t.id, t.name, m.joined, IFNULL(g.id, 0) AS gameId, IFNULL(g.name, '') AS game, "
                    . "IFNULL(g.scheduled, 'false') AS gameScheduled, IFNULL(g.game_started, 'false') AS gameStarted, IFNULL(g.game_ended, 'false') AS gameEnded "
                    . "FROM as_teams AS t "
                    . "LEFT JOIN as_team_members AS m ON m.team_id = t.id "
                    . "LEFT JOIN as_games AS g ON t.current_game_id = g.id "
                    . "WHERE m.user_id = :user_id ORDER BY t.name;", array(':user_id' => $user->id));
            
            $user->notices = array('teamInvites' => 
                    DBConn::selectAll("SELECT i.token, i.created, i.expires, "
                            . "i.team_id AS teamId, t.name AS teamName, i.user_id AS userId, "
                            . "CONCAT(u.name_first, ' ', u.name_last) AS fromPlayer, u.id AS fromPlayerId "
                            . "FROM as_tokens_player_invites AS i "
                            . "LEFT JOIN as_teams AS t ON t.id = i.team_id "
                            . "LEFT JOIN as_users AS u ON u.id = i.created_user_id "
                            . "WHERE user_id = :id AND response IS NULL AND expires > NOW() "
                            . "AND i.team_id NOT IN (SELECT m.team_id FROM as_team_members AS m WHERE i.user_id = m.user_id);", 
                            array(':id' => $user->id))
                    );
        }
        return $user;
    }
    
    /* Password Updating */

    static function resetUserPassword($resetpwdperms) {
        return DBConn::update("UPDATE as_users SET password = :password, usertoken = :usertoken,fortgotpassword_duration = :fortgotpassword_duration  WHERE email = :email;", $resetpwdperms);
    }
    
    static function selectUserPasswordById($userId) {
        return DBConn::selectColumn("SELECT password FROM as_users WHERE id = :id LIMIT 1;", array(':id' => $userId));
    }
    
    static function selectUserPasswordByEmail($email) {
        return DBConn::selectColumn("SELECT password FROM as_users WHERE email = :email LIMIT 1;", array(':email' => $email));
    }
    
    // Player invite
    
    static function selectSignupInvite($token) {
        return DBConn::selectColumn("SELECT team_id AS teamId FROM as_tokens_player_invites "
                . "WHERE user_id IS NULL AND response IS NULL AND expires >= NOW() "
                . "AND token = :token LIMIT 1;", array(':token' => $token));
    }
    
    static function updateAcceptSignupInvite($validInvite) {
        return DBConn::update("UPDATE as_tokens_player_invites "
                . "SET user_id =:user_id, response='accepted', last_visited=NOW() "
                . "WHERE token = :token LIMIT 1;", $validInvite);
    }
    
    static function updateAcceptSignupTeamInvite($validInvite) {        
        DBConn::insert("INSERT INTO as_team_members(user_id, team_id, added_by) "
                . "VALUES (:user_id, :team_id, :added_by);", 
                array(':user_id' => $validInvite[':user_id'],  ':team_id' => $validInvite[':team_id'], ':added_by' => $validInvite[':user_id']));
        
        return DBConn::update("UPDATE as_tokens_player_invites SET "
                . "response='accepted', user_id = :user_id WHERE token = :token "
                . "AND team_id = :team_id AND response IS NULL AND expires >= NOW() LIMIT 1;", $validInvite);
    }
}
