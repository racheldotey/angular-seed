<?php namespace API;

class SignupDB extends RouteDBController {
    
    public function selectUserById($id) {
        $user = $this->DBConn->selectOne("SELECT id, name_first as nameFirst, name_last as nameLast, email, phone "
                        . "FROM {$this->prefix}users WHERE id = :id LIMIT 1;", array(':id' => $id));
        if ($user) {
            $user->displayName = $user->nameFirst;
            $user->roles = $this->DBConn->selectAll("SELECT DISTINCT(gr.auth_role_id) "
                    . "FROM {$this->prefix}auth_lookup_user_group AS ug "
                    . "JOIN {$this->prefix}auth_lookup_group_role AS gr ON ug.auth_group_id = gr.auth_group_id "
                    . "WHERE ug.user_id = :id;", array(':id' => $user->id), \PDO::FETCH_COLUMN);
            
            $user->notifications = array();
        }
        return $user;
    }
    
    public function selectUserByEmail($email) {
        return $this->DBConn->selectColumn("SELECT id FROM {$this->prefix}users WHERE email = :email LIMIT 1;", array(':email' => $email));
    }
    
    public function insertUser($validUser) {
        $userId = $this->DBConn->insert("INSERT INTO {$this->prefix}users(name_first, name_last, email, phone, password, accepted_terms, recieve_newsletter) "
                . "VALUES (:name_first, :name_last, :email, :phone, :password, :accepted_terms, :recieve_newsletter);", $validUser);
        if($userId) {
            $this->addDefaultGroupToUser($userId);
        }
        return $userId;
    }
  
    public function insertFacebookUser($validUser) {
        $userId = $this->DBConn->insert("INSERT INTO {$this->prefix}users(name_first, name_last, email, facebook_id, accepted_terms, recieve_newsletter) "
                . "VALUES (:name_first, :name_last, :email, :facebook_id, :accepted_terms, :recieve_newsletter);", $validUser);
        if($userId) {
            $this->addDefaultGroupToUser($userId);
        }
        return $userId;
    }

    private function addDefaultGroupToUser($userId) {
        $groupId = $this->DBConn->selectColumn("SELECT id FROM {$this->prefix}auth_groups WHERE default_group = 1 AND disabled = 0 LIMIT 1;");
        
        if($groupId) {
            $validGroup = array(
                ':user_id' => $userId, 
                ':auth_group_id' => $groupId, 
                ':created_user_id' => $userId
            );
            return $this->DBConn->insert("INSERT INTO {$this->prefix}auth_lookup_user_group(user_id, auth_group_id, created_user_id) "
                    . "VALUES (:user_id, :auth_group_id, :created_user_id);", $validGroup);
        }
        
        return false;
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