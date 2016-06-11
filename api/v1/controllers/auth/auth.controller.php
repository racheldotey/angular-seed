<?php

namespace API;

require_once dirname(dirname(dirname(__FILE__))) . '/services/api.auth.php';
require_once dirname(dirname(dirname(__FILE__))) . '/services/api.mailer.php';
require_once dirname(dirname(__FILE__)) . '/venues/venues.data.php';
require_once dirname(dirname(__FILE__)) . '/hosts/hosts.data.php';
require_once dirname(__FILE__) . '/auth.data.php';
require_once dirname(__FILE__) . '/auth.additionalInfo.data.php';
require_once dirname(__FILE__) . '/auth.controller.native.php';
require_once dirname(__FILE__) . '/auth.controller.facebook.php';
require_once dirname(__FILE__) . '/auth.hooks.php';

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
        if ($found['authenticated']) {
            return $app->render(200, $found);
        } else {
            return $app->render(401, $found);
        }
    }

    ///// 
    ///// Sign Up
    ///// 

    /* email, nameFirst, nameLast, password */

    static function signup($app) {
        $result = AuthControllerNative::signup($app);
        if ($result['registered']) {
            AuthHooks::signup($app, $result);
            if (isset($result['user']->teams[0])) {
                ApiMailer::sendWebsiteSignupJoinTeamConfirmation($result['user']->teams[0]->name, $result['user']->email, "{$result['user']->nameFirst} {$result['user']->nameLast}");
            } else {
                ApiMailer::sendWebsiteSignupConfirmation($result['user']->email, "{$result['user']->nameFirst} {$result['user']->nameLast}");
            }
            return $app->render(200, $result);
        } else {
            return $app->render(400, $result);
        }
    }

    /* email, nameFirst, nameLast, facebookId, accessToken */

    static function facebookSignup($app) {
        $result = AuthControllerFacebook::signup($app);
        if ($result['registered']) {
            AuthHooks::signup($app, $result);
            if (isset($result['user']->teams[0])) {
                ApiMailer::sendWebsiteSignupJoinTeamConfirmation($result['user']->teams[0]->name, $result['user']->email, "{$result['user']->nameFirst} {$result['user']->nameLast}");
            } else {
                ApiMailer::sendWebsiteSignupConfirmation($result['user']->email, "{$result['user']->nameFirst} {$result['user']->nameLast}");
            }
            return $app->render(200, $result);
        } else {
            return $app->render(400, $result);
        }
    }

    /* email, nameFirst, nameLast, password */

    static function venueSignup($app,$type="venue") {
        $isValidVenue = self::addVenue($app, 0, true,$type);
        $venue = false;
        if ($isValidVenue) {

            $result = AuthControllerNative::signup($app,$type);
            if (!$result['registered']) {
                return $app->render(400, $result);
            }

            if (isset($result['user']->teams[0])) {
                ApiMailer::sendWebsiteSignupJoinTeamConfirmation($result['user']->teams[0]->name, $result['user']->email, "{$result['user']->nameFirst} {$result['user']->nameLast}");
            } else {
                ApiMailer::sendWebsiteSignupConfirmation($result['user']->email, "{$result['user']->nameFirst} {$result['user']->nameLast}");
            }
            $venue = self::addVenue($app, $result['user']->id);

        }

        if (!$venue) {
            return $app->render(400, array('msg' =>"Could not add venue. Check your parameters and try again."));
        }
        $venue_reponse['venue'] = (object) [];
        $venue_reponse['venue']->id = $venue;
        AuthHooks::venue_signup($app, $venue_reponse);
        self::addVenueGroupToUser($result['user']->id);
        self::addVenueRole($result['user']->id, $venue, 'owner');
        return $app->render(200, $result);
    }

    static function venueFacebookSignup($app) {
        $isValidVenue = self::addVenue($app, 0, true);
        $venue = false;
        if ($isValidVenue) {
            $result = AuthControllerFacebook::signup($app);
            if (!$result['registered']) {
                return $app->render(400, $result);
            }
            if (isset($result['user']->teams[0])) {
                ApiMailer::sendWebsiteSignupJoinTeamConfirmation($result['user']->teams[0]->name, $result['user']->email, "{$result['user']->nameFirst} {$result['user']->nameLast}");
            } else {
                ApiMailer::sendWebsiteSignupConfirmation($result['user']->email, "{$result['user']->nameFirst} {$result['user']->nameLast}");
            }
            $venue = self::addVenue($app, $result['user']->id);
        }
        if (!$venue) {
            return $app->render(400, array('msg' =>"Could not add venue. Check your parameters and try again."));
        }
        $venue_reponse['venue'] = (object) [];
        $venue_reponse['venue']->id = $venue;
        AuthHooks::venue_signup($app, $venue_reponse);
        self::addVenueGroupToUser($result['user']->id);
        self::addVenueRole($result['user']->id, $venue, 'owner');
        return $app->render(200, $result);
    }

    static function addVenue($app, $userId, $onlyValidation = false,$type='venue') {
        $post = $app->request->post();

        if (!v::key('venue', v::stringType())->validate($post) ||
                !v::key('address', v::stringType())->validate($post) ||
                !v::key('city', v::stringType())->validate($post) ||
                !v::key('state', v::stringType())->validate($post) ||
                !v::key('zip', v::stringType())->validate($post)) {

            return false;
        }

        if((!v::key('triviaDay', v::stringType())->validate($post) ||
            !v::key('triviaTime', v::stringType())->validate($post)))
        {
            return false;
        }

        if (v::key('website', v::stringType())->validate($post)){
            if(!v::url()->validate($post["website"])) {
                return $app->render(400, array('msg' => $post["website"] . ' is not valid URL.'));
            }
        }
        if (v::key('facebook', v::stringType())->validate($post)){
            if (!preg_match('/(?:https?:\/\/)?(?:www\.)?facebook\.com\/(?:(?:\w)*#!\/)?(?:pages\/)?(?:[\w\-]*\/)*([\w\-\.]*)/', $post["facebook"])) {
                return $app->render(400, array('msg' => $post["facebook"] . ' is not valid facebook URL.'));
            }
        }


        $dayNames = array(
            'sunday',
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
            );
        if($type=="venue"){
            if ($post['triviaDay'] != '') {
                $status = in_array(strtolower($post['triviaDay']), $dayNames);
                if (!$status) {
                    return $app->render(400, array('msg' => 'Day is not corect.'));
                }
            }
            if ($post['triviaTime'] != '') {
                if (!preg_match('/^(1[0-2]|0?[1-9]):[0-5][0-9] (AM|PM)$/i', $post['triviaTime'])) {
                    return $app->render(400, array('msg' => 'Time is not corect.'));
                }
            }
        }
        if (v::key('phone', v::stringType())->validate($post) && $post['phone'] != '') {
            if (!preg_match('/^[+]?([\d]{0,3})?[\(\.\-\s]?([\d]{3})[\)\.\-\s]*([\d]{3})[\.\-\s]?([\d]{4})$/', $post["phone"])) {
                return $app->render(400, array('msg' => 'This is not valid US format number.'));
            }
        }
        if ($onlyValidation === false) {
            $venue = array(
                ':name' => $post['venue'],
                ':address' => $post['address'],
                ':address_b' => (v::key('addressb', v::stringType())->validate($post)) ? $post['addressb'] : '',
                ':city' => $post['city'],
                ':state' => $post['state'],
                ':zip' => $post['zip'],
                ':phone_extension' => (v::key('phone_extension', v::stringType())->validate($post)) ? $post['phone_extension'] : '',
                ':phone' => (v::key('phone', v::stringType())->validate($post)) ? $post['phone'] : '',
                ':website' => (v::key('website', v::stringType())->validate($post)) ? $post['website'] : '',
                ':facebook_url' => (v::key('facebook', v::stringType())->validate($post)) ? $post['facebook'] : '',
                ':logo' => (v::key('logo', v::stringType())->validate($post)) ? $post['logo'] : '',
                ':referral' => (v::key('referralCode', v::stringType())->validate($post)) ? $post['referralCode'] : '',
                ":created_user_id" => $userId,
                ":last_updated_by" => $userId
                );
            $venueId = VenueData::insertVenue($venue);
            if ($venueId && $type=="venue") {
                if ($post['triviaDay'] != '' && $post['triviaTime'] != '') {
                    $venueScheduleId = VenueData::manageVenueTriviaShcedule(array(
                        ':trivia_day' => $post['triviaDay'],
                        ':trivia_time' => $post['triviaTime'],
                        ":created_user_id" => $userId,
                        ":last_updated_by" => $userId,
                        ':venue_id' => $venueId
                        ), $venueId);
                }
            }
            return $venueId;
        } else {
            return true;
        }
    }
    static function hostSignup($app) {
        $post = $app->request->post();
        $isValidHost=self::addHost($app, 0,0, true);

        if($isValidHost){

            if(!v::key('venueId', v::intVal())->validate($post)) 
            {
                return $app->render(400,  array('msg' => 'Invalid venue Id.'));
            }
            else{

                $result = AuthControllerNative::signup($app);
                if (!$result['registered']) {
                    return $app->render(400, $result);
                }
            }

            $venueId=$post['venueId'];
            $host_id=self::addHost($app, $result['user']->id,$venueId, false);
			self::addHostGroupToUser($result['user']->id);
            $user = UserData::selectUserById( $result['user']->id);
            $host = HostData::getHostByUser($result['user']->id);
            $venue = VenueData::getVenue($venueId);
            
            if (!$host_id) {
                return $app->render(400, array('msg' => "Could not add host. Check your parameters and try again."));
            }
			return $app->render(200, $result);  // as it is returning apikey token and user detail for siginig
            //return $app->render(200, array('msg'=>"Host Added",'user'=>$user,"venue"=>$venue,'host'=>$host));
        }
        else{

            return $app->render(400, array('msg' =>"Could not add host. Check your parameters and try again."));
        }
    }
    static function hostFacebookSignup($app) {
        $post = $app->request->post();
        $isValidHost=self::addHost($app, 0,0, true);

        if($isValidHost){

            $venueId=(v::key('venueId', v::intVal())->validate($post))?$post['venueId']:'';

            if(!v::intVal()->validate($venueId)){
                return $app->render(400,  array('msg' => 'Invalid venue Id.'));
            }
            else{
                $venueId=$post['venueId'];
                $result = AuthControllerNative::signup($app);
                if (!$result['registered']) {
                    return $app->render(400, $result);
                }
            }

            $host_id=self::addHost($app, $result['user']->id,$venueId, false);
			self::addHostGroupToUser($result['user']->id);
            $user = UserData::selectUserById( $result['user']->id);
            $host = HostData::getHostByUser($host_id);
            $venue = VenueData::getVenueByUser($venueId);
            if (!$host_id) {
                return $app->render(400, array('msg' => "Could not add host. Check your parameters and try again."));
            }
            //return $app->render(200, array('msg'=>"Host Added",'user'=>$user,"venue"=>$venue,'host'=>$host));
			return $app->render(200, $result);  // as it is returning apikey token and user detail for siginig
        }
        else{

            return $app->render(400, array('msg' =>"Could not add host. Check your parameters and try again."));
        }
    }
	static function addHostGroupToUser($userId) {
        $groupId = GroupData::selectGroupIdBySlug('game-admin');
        if (!$groupId) {
            return false;
        }
        return UserData::insertGroupAssignment(array(
            ':auth_group_id' => $groupId,
            ':user_id' => $userId,
            ':created_user_id' => $userId
            ));
    }
    static function addHost($app, $userId,$venueId, $onlyValidation = false) {
        $post = $app->request->post();

        if (!v::key('host_accepted_terms', v::stringType())->validate($post) ||
            !v::key('host_address', v::stringType())->validate($post) ||
            !v::key('host_city', v::stringType())->validate($post) ||
            !v::key('host_state', v::stringType())->validate($post) ||
            !v::key('host_zip', v::stringType())->validate($post))
        {

            return false;
        }
        if($post['host_accepted_terms']!='true'){
            return false;
        }

        if (!v::url()->validate($post["host_website"])) {
            return $app->render(400, array('msg' => $post["host_website"] . ' is not valid URL.'));
        }

        if (!preg_match('/(?:https?:\/\/)?(?:www\.)?facebook\.com\/(?:(?:\w)*#!\/)?(?:pages\/)?(?:[\w\-]*\/)*([\w\-\.]*)/', $post["host_facebook"])) {
            return $app->render(400, array('msg' => $post["host_facebook"] . ' is not valid facebook URL.'));
        }
        if (v::key('phone', v::stringType())->validate($post) && $post['phone'] != '') {
            if (!preg_match('/^[+]?([\d]{0,3})?[\(\.\-\s]?([\d]{3})[\)\.\-\s]*([\d]{3})[\.\-\s]?([\d]{4})$/', $post["phone"])) {
                return $app->render(400, array('msg' => 'This is not valid US format number.'));
            }
        }
        if ($onlyValidation === false) {
            $host = array(
                ':address' => $post['host_address'],
                ':address_b' => (v::key('host_addressb', v::stringType())->validate($post)) ? $post['host_addressb'] : '',
                ':city' => $post['host_city'],
                ':state' => $post['host_state'],
                ':zip' => $post['host_zip'],
                ':phone_extension' => (v::key('phone_extension', v::stringType())->validate($post)) ? $post['phone_extension'] : '',
                ':phone' => (v::key('phone', v::stringType())->validate($post)) ? $post['phone'] : '',
                ':website' => (v::key('host_website', v::stringType())->validate($post)) ? $post['host_website'] : '',
                ':facebook_url' => (v::key('host_facebook', v::stringType())->validate($post)) ? $post['host_facebook'] : '',
                ":created_user_id" => $userId,
                ":last_updated_by" => $userId,
                ":trv_users_id"=>$userId,
                ":host_accepted_terms"=>$post['host_accepted_terms']
                );

            $hostId = HostData::insertHost($host);

             //host_venue joint table
            $assignment = array(
                ':host_id' => $hostId,
                ':venue_id' => $venueId,
                ":created_user_id" => $userId,
                ":last_updated_by" => $userId
                );
            HostData::insertHostVenueAssignment($assignment);
            //day time host venue 
            $venueData = VenueData::getVenue($venueId);  
            if($venueData->triviaDay!='' && $venueData->triviaTime!=''){
                $hostScheduleId = HostData::insertHostTriviaSchedules(array(
                    ':host_id' => $hostId,
                    ':venue_id' => $venueId,                    
                    ':trivia_day' => $venueData->triviaDay, 
                    ':trivia_time' => $venueData->triviaTime, 
                    ":created_user_id" => $userId,
                    ":last_updated_by" => $userId
                    ));
            }    
            return $hostId;
        } else {
            return true;
        }
    }   
    static function updateVenueOwner($venueId,$created_by_user_type){
        $assignment=array(
            ":created_by_user_type"=>$created_by_user_type,
            ":id"=>$venueId
            );
        VenueData::updateVenueOwner($assignment);
    }
    static function addVenueGroupToUser($userId) {
        $groupId = GroupData::selectGroupIdBySlug('venue-admin');
        if (!$groupId) {
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
        if ($result['authenticated']) {
            return $app->render(200, $result);
        } else {
            return $app->render(401, $result);
        }
    }

    static function forgotpassword($app) {
        $result = AuthControllerNative::forgotpassword($app);
        if ($result['frgtauthenticated']) {
            return $app->render(200, $result);
        } else {
            return $app->render(400, $result);
        }
    }

    static function getforgotpasswordemail($app) {
        $result = AuthControllerNative::getforgotpasswordemail($app);
        if ($result['frgtauthenticatedemail']) {
            return $app->render(200, $result);
        } else {
            return $app->render(400, $result);
        }
    }

    static function resetpassword($app) {
        $result = AuthControllerNative::resetpassword($app);
        if ($result['resetpasswordauthenticated']) {
            return $app->render(200, $result);
        } else {
            return $app->render(400, $result);
        }
    }

    static function facebookLogin($app) {
        $result = AuthControllerFacebook::login($app);
        if ($result['authenticated']) {
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
        if (AuthControllerNative::logout($app)) {
            return $app->render(200, array('msg' => "User sucessfully logged out."));
        } else {
            return $app->render(400, array('msg' => "User could not be logged out. Check your parameters and try again."));
        }
    }

    static function changeUserPassword($app) {
        $post = $app->request->post();
        if ((!v::key('userId', v::stringType())->validate($post) && !v::key('email', v::stringType())->validate($post)) ||
            !v::key('current', v::stringType())->validate($post))
        {
            return $app->render(400, array('msg' => "Password could not be changed. Check your parameters and try again."));
        } else if (!AuthControllerNative::validatePasswordRequirements($post, 'new')) {
            return $app->render(400, array('msg' => "Invalid Password. Check your parameters and try again."));
        }

        $savedPassword = (v::key('userId', v::stringType())->validate($post)) ? AuthData::selectUserPasswordById($post['userId']) :
        AuthData::selectUserPasswordByEmail($post['email']);

        if (!$savedPassword) {
            return $app->render(400, array('msg' => "User not found. Check your parameters and try again."));
        } else if (!password_verify($post['current'], $savedPassword)) {
            return $app->render(400, array('msg' => "Invalid user password. Unable to verify request."));
        } else {
            if (AuthData::updateUserPassword(array(':id' => $post['userId'], ':password' => password_hash($post['new'], PASSWORD_DEFAULT)))) {
                return $app->render(200, array('msg' => "Password successfully changed."));
            } else {
                return $app->render(400, array('msg' => "Password could not be changed. Try again later."));
            }
        }
    }

    ///// 
    // System Admin
    // TODO: Create system functions class
    ///// 
    // TODO: Add this to Cron Job
    static function deleteExpiredAuthTokens($app) {
        AuthData::deleteExpiredAuthTokens();
        return $app->render(200, array('msg' => "Deleted expired auth tokens."));
    }

}
