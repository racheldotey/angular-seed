<?php namespace API;
require_once dirname(dirname(dirname(__FILE__))) . '/services/api.auth.php';
require_once dirname(dirname(__FILE__)) . '/venues/venues.data.php';
require_once dirname(__FILE__) . '/auth.data.php';
require_once dirname(__FILE__) . '/auth.additionalInfo.data.php';
require_once dirname(__FILE__) . '/auth.controller.native.php';
require_once dirname(__FILE__) . '/auth.controller.facebook.php';

use \Respect\Validation\Validator as v;

class AuthController {
    
    ///// 
    ///// Authentication
    ///// 
            
    /*
     * apiKey, apiToken
     */
    static function isAuthenticated($app) {
        $found = AuthControllerNative::isAuthenticated($app);
        if($found['authenticated']) {
            return $app->render(200, $found);
        }  else {
            return $app->render(401, $found);
        }
    }
            
    ///// 
    ///// Sign Up
    ///// 
            
    /* email, nameFirst, nameLast, password */
    
    static function signup($app) {
        $result = AuthControllerNative::signup($app);
        if($result['registered']) {
            return $app->render(200, $result);
        } else {
            return $app->render(400, $result);
        }
    }
    
    /* email, nameFirst, nameLast, facebookId, accessToken */
    static function facebookSignup($app) {
        $result = AuthControllerFacebook::signup($app);
        if($result['registered']) {
            return $app->render(200, $result);
        } else {
            return $app->render(400, $result);
        }
    }
    
    /* email, nameFirst, nameLast, password */
    static function venueSignup($app) {
        $result = AuthControllerNative::signup($app);
        if(!$result['registered']) {
            return $app->render(400, $result);
        }
        $venue = self::addVenue($app, $result['user']->id);
        if (!$venue) {
            return $app->render(400, "Could not add venue. Check your parameters and try again.");
        }
        self::addVenueGroupToUser($result['user']->id);
        self::addVenueRole($result['user']->id, $venue, 'owner');
        return $app->render(200, $result);
    }
    
    static function venueFacebookSignup($app) {
        $result = AuthControllerFacebook::signup($app);
        if(!$result['registered']) {
            return $app->render(400, $result);
        }
        $venue = self::addVenue($app, $result['user']->id);
        if(!$venue) {
            return $app->render(400, "Could not add venue. Check your parameters and try again.");
        }
        self::addVenueGroupToUser($result['user']->id);
        self::addVenueRole($result['user']->id, $venue, 'owner');
        return $app->render(200, $result);
    }
    
    static function addVenue($app, $userId) {
        $post = $app->request->post();
        
        if(!v::key('venueName', v::stringType())->validate($post) || 
           !v::key('address', v::stringType())->validate($post) || 
           !v::key('city', v::stringType())->validate($post) || 
           !v::key('state', v::stringType())->validate($post) || 
           !v::key('zip', v::stringType())->validate($post)) {
            return false;
        }
        
        $venue = array(
            ':name' => $post['venueName'], 
            ':address' => $post['address'], 
            ':address_b' => (v::key('addressb', v::stringType())->validate($post)) ? $post['addressb'] : '', 
            ':city' => $post['city'], 
            ':state' => $post['state'], 
            ':zip' => $post['zip'], 
            ':phone' => (v::key('phone', v::stringType())->validate($post)) ? $post['phone'] : '', 
            ':website' => (v::key('website', v::stringType())->validate($post)) ? $post['website'] : '', 
            ':facebook_url' => (v::key('facebook', v::stringType())->validate($post)) ? $post['facebook'] : '', 
            ':logo' => (v::key('logo', v::stringType())->validate($post)) ? $post['logo'] : '', 
            ':hours' => (v::key('hours', v::stringType())->validate($post)) ? $post['hours'] : '', 
            ':referral' => (v::key('referralCode', v::stringType())->validate($post)) ? $post['referralCode'] : '', 
            ":created_user_id" => $userId,
            ":last_updated_by" => $userId
        );
        return VenueData::insertVenue($venue);
    }
    
    static function addVenueGroupToUser($userId) {
        $groupId = GroupData::selectGroupIdBySlug('venue-editor');
        if(!$groupId) {
            return false;
        }
        return UserData::insertGroupAssignment(array(
            ':auth_group_id' => $groupId, 
            ':user_id' => $userId,
            ':created_user_id' => $userId
        ));
    }
    
    static function addVenueRole($userId, $venueId, $roleSlug) {
        $role = ($roleSlug === 'owner' || $roleSlug === 'manager' || $roleSlug === 'employee' || $roleSlug === 'guest');
        return VenueData::insertVenueRoleAssignment(array(
            ':venue_id' => $venueId, 
            ':user_id' => $userId,
            ':role' => $role
        ));
    }

    ///// 
    ///// Authentication
    ///// 
              
    /*
     * email, password, remember
     */
    static function login($app) {
        $result = AuthControllerNative::login($app);
        if($result['authenticated']) {
            return $app->render(200, $result);
        } else {
            return $app->render(401, $result);
        }
    }
    
    static function facebookLogin($app) {
        $result = AuthControllerFacebook::login($app);
        if($result['authenticated']) {
            return $app->render(200, $result);
        } else {
            return $app->render(401, $result);
        }
    }
            
    ///// 
    ///// Logout
    ///// 

    /*
     * logout (apiKey)
     */
    static function logout($app) {
        if(AuthControllerNative::logout($app)) {
            return $app->render(200, array('msg' => "User sucessfully logged out." ));
        } else {
            return $app->render(400, array('msg' => "User could not be logged out. Check your parameters and try again." ));
        }
    }
        
    static function changeUserPassword($app) {
        if(AuthControllerNative::updateUserPassword($app)) {
            return $app->render(200, array('msg' => "Password successfully changed." ));
        } else {
            return $app->render(400, array('msg' => "Password could not be changed. Check your parameters and try again." ));
        }
    }

    ///// 
    // System Admin
    // TODO: Create system functions class
    ///// 
    
    // TODO: Add this to Cron Job
    static function deleteExpiredAuthTokens($app) {
        AuthData::deleteExpiredAuthTokens();
        return $app->render(200, array('msg' => "Deleted expired auth tokens." ));
    }
    
}
