<?php namespace API;

class SignupDB {

    protected $DBConn;
    protected $prefix;

    public function __construct($DBConn) {
        $this->DBConn = $DBConn;
        $this->prefix = $this->DBConn->prefix();
    }
    
    private function selectUserWhere($where, $params) {
        $user = $this->DBConn->selectOne("SELECT id, name_first as nameFirst, name_last as nameLast, email, phone "
                        . "FROM {$this->prefix}users WHERE {$where} LIMIT 1;", $params);
        if ($user) {
            $user = self::selectUserData($user);
        }
        return $user;
    }
    
    public function selectUserById($id) {
        return self::selectUserWhere('id = :id', array(':id' => $id));
    }
    
    public function selectUserByEmail($email) {
        return $this->DBConn->selectColumn("SELECT id FROM {$this->prefix}users WHERE email = :email LIMIT 1;", array(':email' => $email));
    }
    
    public function insertUser($validUser) {
        $userId = $this->DBConn->insert("INSERT INTO {$this->prefix}users(name_first, name_last, email, phone, password, accept_terms, recieve_newsletter) "
                . "VALUES (:name_first, :name_last, :email, :phone, :password, :accept_terms, :recieve_newsletter);", $validUser);
        if($userId) {
            GroupData::addDefaultGroupToUser($userId);
        }
        return $userId;
    }
  
    public function insertFacebookUser($validUser) {
        $userId = $this->DBConn->insert("INSERT INTO {$this->prefix}users(name_first, name_last, email, facebook_id, accept_terms, recieve_newsletter) "
                . "VALUES (:name_first, :name_last, :email, :facebook_id, :accept_terms, :recieve_newsletter);", $validUser);
        if($userId) {
            GroupData::addDefaultGroupToUser($userId);
        }
        return $userId;
    }

    /* Signup Invites */
    
    public function selectSignupInvite($token) {
        return $this->DBConn->selectColumn("SELECT team_id AS teamId FROM {$this->prefix}tokens_signup_invites "
                . "WHERE user_id IS NULL AND response IS NULL AND expires >= NOW() "
                . "AND token = :token LIMIT 1;", array(':token' => $token));
    }
    
    public function updateAcceptSignupInvite($validInvite) {
        return $this->DBConn->update("UPDATE {$this->prefix}tokens_signup_invites "
                . "SET user_id =:user_id, response='accepted', last_visited=NOW() "
                . "WHERE token = :token LIMIT 1;", $validInvite);
    }
}