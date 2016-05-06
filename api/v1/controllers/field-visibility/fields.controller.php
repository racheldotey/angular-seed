<?php namespace API;
require_once dirname(__FILE__) . '/fields.data.php';
require_once dirname(dirname(__FILE__)) . '/roles/roles.data.php';
require_once dirname(dirname(dirname(__FILE__))) . '/services/api.auth.php';

use \Respect\Validation\Validator as v;


class FieldController {

    static function getField($app, $fieldId) {
        if(!v::intVal()->validate($fieldId)) {
            return $app->render(400,  array('msg' => 'Could not select field. Check your parameters and try again.'));
        }
        $field = FieldData::getField($fieldId);
        if($field) {
            return $app->render(200, array('field' => $field));
        } else {
            return $app->render(400,  array('msg' => 'Could not select field.'));
        }
    }
    
    static function addField($app) {
        if(!v::key('identifier', v::stringType())->validate($app->request->post()) || 
           !v::key('type', v::stringType())->validate($app->request->post()) || 
           !v::key('desc', v::stringType())->validate($app->request->post())) {
            // Validate input parameters
            return $app->render(400, array('msg' => 'Add failed. Check your parameters and try again.'));
        } else if(strtolower($app->request->post('type')) != 'state' && strtolower($app->request->post('type')) != 'element') {
            return $app->render(400, array('msg' => 'Add failed. Invalid field type. Only "state" and "element" are allowed.'));
        }
        
        // Verify a unique slug
        $identifier = self::makeSlug($app->request->post('identifier'));
        $existing = FieldData::getByIdentifier($identifier);
        if ($existing) {
            return $app->render(400,  array('msg' => 'Could not add field. A field with that name already exists.', 'field' => $existing));
        }
        
        // Add Cariable
        $data = array (
            ':identifier' => $identifier,
            ":type" => strtolower($app->request->post('type')),
            ":desc" => $app->request->post('desc'),
            ":created_user_id" => APIAuth::getUserId(),
            ":last_updated_by" => APIAuth::getUserId()
        );
        
        $fieldId = FieldData::insertField($data);
        if($fieldId) {
            RoleData::addAdminRoleToNewField($fieldId);
            $field = FieldData::getField($fieldId);
            return $app->render(200, array('field' => $field));
        } else {
            return $app->render(400,  array('msg' => 'Could not add field.'));
        }
    }
    
    static function saveField($app, $fieldId) {
        if(!v::intVal()->validate($fieldId) || 
           !v::key('identifier', v::stringType())->validate($app->request->post()) || 
           !v::key('type', v::stringType())->validate($app->request->post()) || 
           !v::key('desc', v::stringType())->validate($app->request->post())) {
            // Validate input parameters
            return $app->render(400, array('msg' => 'Update failed. Check your parameters and try again.'));
        } else if(strtolower($app->request->post('type')) != 'state' && strtolower($app->request->post('type')) != 'element') {
            return $app->render(400, array('msg' => 'Update failed. Invalid field type. Only "state" and "element" are allowed.'));
        }
        
        // Verify a unique slug
        $identifier = self::makeSlug($app->request->post('identifier'));
        $existing = FieldData::getByIdentifier($identifier, $fieldId);
        if ($existing) {
            return $app->render(400,  array('msg' => 'Could not update field. A field with that name already exists.', 'field' => $existing));
        }
        
        // Add Cariable
        $data = array (
            ':id' => $fieldId,
            ':identifier' => $identifier,
            ":type" => strtolower($app->request->post('type')),
            ":desc" => $app->request->post('desc'),
            ":last_updated_by" => APIAuth::getUserId()
        );
        
        $saved = FieldData::updateField($data);
        if($saved) {
            RoleData::addAdminRoleToNewField($fieldId);
            $field = FieldData::getField($fieldId);
            return $app->render(200, array('field' => $field));
        } else {
            return $app->render(400,  array('msg' => 'Could not update field.'));
        }
    }
    
    static function initializeField($app, $fieldId) {
        if(!v::intVal()->validate($fieldId)) {
           return $app->render(400, array('msg' => 'Field initialization failed. Check your parameters and try again.'));
        }
        
        $saved = FieldData::updateFieldInitialize(array(':id' => $fieldId, ':initialized' => date('Y-m-d H:i:s')));
        if($saved) {
            $field = FieldData::getField($fieldId);
            return $app->render(200, array('field' => $field));
        } else {
            return $app->render(400,  array('msg' => 'Could not initialize field.'));
        }
    }
    
    static function deleteField($app, $fieldId) {
        if(!v::intVal()->validate($fieldId)) {
            return $app->render(400,  array('msg' => 'Could not delete field. Check your parameters and try again.'));
        } else if(FieldData::deleteField($fieldId)) {
            return $app->render(200,  array('msg' => 'Field has been deleted.'));
        } else {
            return $app->render(400,  array('msg' => 'Could not delete field.'));
        }
    }
    
    static function makeSlug($string) {
        return preg_replace('/[^a-zA-Z0-9-_.]/', '', str_replace(' ', '.', strtolower(trim($string))));
    }
    
    static function unassignRole($app) {
        if(!v::key('fieldId', v::stringType())->validate($app->request->post()) || 
           !v::key('roleId', v::stringType())->validate($app->request->post())) {
            return $app->render(400,  array('msg' => 'Could not unassign role from field. Check your parameters and try again.'));
        } 
        
        $data = array (
            ':auth_field_id' => $app->request->post('fieldId'),
            ':auth_role_id' => $app->request->post('roleId')
        );
        
        if(FieldData::deleteRoleAssignment($data)) {
            return $app->render(200,  array('msg' => 'Role has been unassigned from field.'));
        } else {
            return $app->render(400,  array('msg' => 'Could not unassign role from field.'));
        }
    }
    
    static function assignRole($app) {
        if(!v::key('fieldId', v::stringType())->validate($app->request->post()) || 
           !v::key('roleId', v::stringType())->validate($app->request->post())) {
            return $app->render(400,  array('msg' => 'Could not assign role from field. Check your parameters and try again.'));
        }
        
        $data = array (
            ':auth_field_id' => $app->request->post('fieldId'),
            ':auth_role_id' => $app->request->post('roleId'),
            ":created_user_id" => APIAuth::getUserId()
        );
        
        if(FieldData::insertRoleAssignment($data)) {
            return $app->render(200,  array('msg' => 'Role has been assigned to field.'));
        } else {
            return $app->render(400,  array('msg' => 'Could not assign role to field.'));
        }
    }
    
    static function getVisibilityKey($app) {
        $data = FieldData::selectVisibilityKey();
        $key = ($data) ? $data : array();
        return $app->render(200, array('key' => $key));
    }
    
    static function initVisibilityElement($app) {
        if(!v::key('elementIdentifier', v::stringType())->validate($app->request->post())) {
            return $app->render(400,  array('msg' => 'Could not assign role from field. Check your parameters and try again.'));
        }
        
        /*
        
        
// Parameterize Inputs
        $pram_element = (isset($data['elementIdentifier'])) ? $data['elementIdentifier'] : false;

// Validate Inputs
        if (!$pram_element) {
            syslog(LOG_ERR, "BAD INPUT PARAMETERS: \n" . print_r($data, true) . "\n");
            Exit_Error_REST_API("Bad Input Parameters");
        } else {
            syslog(LOG_INFO, "GOOD INPUT PARAMETERS: \n" . print_r($data, true) . "\n");
        }

        $selectQuery = 'SELECT `id`, `identifier`, `desc`, `initialized` FROM `auth-element` WHERE `identifier` = ? LIMIT 1;';

        $selected = ExecuteSQL($selectQuery, array($pram_element));

        if ($selected && isset($selected[0])) {
            // Initialize Element
            $who = getCurrentUsername($data);
            $when = getCurrentTimeInZulu();

            $query = 'UPDATE `auth-element` SET `initialized`=?, `last_updated_by`=?,`last_updated_ts`=? WHERE `id` = ?;';
            ExecuteSQL($query, array('1', $who, $when, $selected[0]->id));

            $selected = ExecuteSQL($selectQuery, array($pram_element));

            if ($selected[0]->initialized === "1") {
                // Get full element roles data
                $sthRoles = PrepareSQL('SELECT auth_role_id AS id FROM `auth-lookup-role-element` WHERE auth_element_id = ? ORDER BY auth_role_id;');
                $sthRoles->execute($subQuery, array($selected[0]->id));
                $selected[0]->roles = $sthRoles->fetchAll(PDO::FETCH_COLUMN);
                $selected[0]->initialized = 1;

                echo json_encode($selected[0]);
            } else {
                Exit_Error_REST_API("Could not initialize element with that identifier.");
            }
        } else {
            syslog(LOG_ERR, "BAD REQUEST");
            Exit_Error_REST_API("Could not find an element with that identifier. Element was not initialized.");
        }
        */
        
    }
}
