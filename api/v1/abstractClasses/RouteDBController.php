<?php namespace API;

/* @author  Rachel L Carbone <hello@rachellcarbone.com> */

abstract class RouteDBController {

    /*
     * System Database Helper Instance
     */
    protected $DBConn;
    
    /*
     * Database Table Prefix
     */
    protected $prefix;

    public function __construct(\API\ApiDBConn $ApiDBConn) {
        $this->DBConn = $ApiDBConn;
        $this->prefix = $ApiDBConn->prefix();
    }
    
    public function selectMemberDataByUserId($id, $getDisabled = false) {
        $disabled = ($getDisabled) ? '' : 'AND disabled IS NULL ';
        $user = $this->DBConn->selectOne("SELECT id, name_first as nameFirst, name_last as nameLast, email, phone, u.email_verified AS emailVerified "
                        . "FROM {$this->prefix}users WHERE id = :id {$disabled}LIMIT 1;", array(':id' => $id));
        if ($user) {
            $user->displayName = $user->nameFirst;
            
            $user->groups = $this->DBConn->selectAll("SELECT DISTINCT(l.auth_group_id) AS id, g.group, g.desc, l.created AS assigned "
                . "FROM {$this->prefix}auth_groups AS g "
                . "JOIN {$this->prefix}auth_lookup_user_group AS l ON g.id = l.auth_group_id "
                . "WHERE l.user_id = :id GROUP BY g.id ORDER BY g.group;", array(':id' => $id));
            $user->roles = $this->DBConn->selectAll("SELECT DISTINCT(r.auth_role_id) "
                . "FROM {$this->prefix}auth_lookup_user_group AS g "
                . "JOIN {$this->prefix}auth_lookup_group_role AS r ON g.auth_group_id = r.auth_group_id "
                . "WHERE g.user_id = :id;", array(':id' => $user->id), \PDO::FETCH_COLUMN);
            
            $user->notifications = array();
        }
        
        return $user;
    }
}