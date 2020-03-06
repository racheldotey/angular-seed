<?php namespace API;

class UpdateUserDB extends RouteDBController {
    
    public function selectOtherUsersWithEmail($email, $id = 0) {
        return DBConn::selectAll("SELECT id FROM " . DBConn::prefix() . "users WHERE email = :email AND id != :id;", 
                    array(':email' => $email, ':id' => $id), \PDO::FETCH_COLUMN);
    }
  
    public function insertUser($validUser) {
        $userId = DBConn::insert("INSERT INTO " . DBConn::prefix() . "users(name_first, name_last, email, password) "
                . "VALUES (:name_first, :name_last, :email, :password);", $validUser);
        if($userId) {
            GroupData::addDefaultGroupToUser($userId);
        }
        return $userId;
    }
    
    public function updateUser($validUser) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "users SET name_first=:name_first, "
                . "name_last=:name_last, email=:email, phone=:phone WHERE id = :id;", $validUser);
    }
    
    public function disableUser($userId) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "users SET disabled=NOW() WHERE id = :id AND disabled IS NULL;", array(':id' => $userId));
    }
    
    public function enableUser($userId) {
        return DBConn::update("UPDATE " . DBConn::prefix() . "users SET disabled=NULL WHERE id = :id AND disabled IS NOT NULL;", array(':id' => $userId));
    }
    
    public function deleteUser($userId) {
        $deleted = GroupData::deleteUserGroups($userId);
        return (!$deleted) ? false :
            DBConn::delete("DELETE FROM " . DBConn::prefix() . "users WHERE id = :id LIMIT 1;", array(':id' => $userId));
    }
}
