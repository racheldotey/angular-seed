<?php namespace API;

class AuthenticationDB extends RouteDBController {
    
    public function selectUserByIdentifierToken($identifier) {
        $user = $this->DBConn->selectOne("SELECT u.id, name_first AS nameFirst, name_last AS nameLast, "
                . "email, phone, token AS apiToken, identifier AS apiKey "
                . "FROM {$this->prefix}tokens_auth AS t "
                . "JOIN {$this->prefix}users AS u ON u.id = t.user_id "
                . "WHERE identifier = :identifier AND t.expires > NOW() "
                . "AND u.disabled IS NULL;", array(':identifier' => $identifier));
        if($user) {
            $user = $this->selectUserData($user);
        }
        return $user;
    }
    
    public function selectUserAndPasswordByEmail($email) {
        $user = $this->DBConn->selectOne("SELECT id, name_first as nameFirst, name_last as nameLast, email, phone, password "
                        . "FROM {$this->prefix}users WHERE email = :email AND disabled IS NULL LIMIT 1;", array(':email' => $email));
        if ($user) {
            $user = $this->selectUserData($user);
        }
        return $user;
    }

    private function selectUserData($user) {
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

    public function deleteAuthToken($identifier) {
        return $this->DBConn->delete("DELETE FROM {$this->prefix}tokens_auth WHERE identifier = :identifier;", $identifier);
    }
}