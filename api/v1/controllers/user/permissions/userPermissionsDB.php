<?php namespace API;

class UserPermissionsDB extends RouteDBController {
    
    static function insertGroupAssignment($data) {
        return DBConn::insert("INSERT INTO " . DBConn::prefix() . "auth_lookup_user_group(`auth_group_id`, `user_id`, `created_user_id`) "
                . "VALUES (:auth_group_id, :user_id, :created_user_id);", $data);
    }
                    
    static function deleteGroupAssignment($data) {
        return DBConn::delete("DELETE FROM " . DBConn::prefix() . "auth_lookup_user_group "
                . "WHERE user_id = :user_id AND auth_group_id = :auth_group_id;", $data);
    }
}
