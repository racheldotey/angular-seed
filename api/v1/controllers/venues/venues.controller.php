<?php namespace API;
require_once dirname(__FILE__) . '/venues.data.php';
require_once dirname(dirname(dirname(__FILE__))) . '/services/api.auth.php';
require_once dirname(dirname(__FILE__)) . '/auth/auth.hooks.php';

use \Respect\Validation\Validator as v;


class VenueController {

    static function getVenue($app, $venueId) {
        if(!v::intVal()->validate($venueId)) {
            return $app->render(400,  array('msg' => 'Could not select venue. Check your parameters and try again.'));
        }
        $venue = VenueData::getVenue($venueId);
        if($venue) {
            return $app->render(200, array('venue' => $venue));
        } else {
            return $app->render(400,  array('msg' => 'Could not select venue.'));
        }
    }
    static function getVenueByUser($app, $userId) {
        $venue = VenueData::getVenueByUser($userId);
        if($venue) {
            return $app->render(200, array('venue' => $venue));
        } else {
            return $app->render(400,  array('msg' => 'Could not select venue.'));
        }
    }
    
    static function addVenue($app) {
        
        $post = $app->request->post();
        
        $results = self::venue_validateVenueParams($post);
        if($results['error']) {
            return $app->render(400,  array('msg' => $results['msg']));
        }

        // Add the verifed venue
        $validVenue = self::venue_getVenueArray($post);
        $validVenue[':created_user_id'] = APIAuth::getUserId();
        $venueId = VenueData::insertVenue($validVenue);

        if($venueId) {
            if($post['triviaDay']!='' && $post['triviaTime']!=''){
                $venueScheduleId = VenueData::manageVenueTriviaShcedule(array(
                    ':trivia_day' => $post['triviaDay'], 
                    ':trivia_time' => $post['triviaTime'], 
                    ":created_user_id" => APIAuth::getUserId(),
                    ":last_updated_by" => APIAuth::getUserId(),
                    ':venue_id' => $venueId
                    ),$venueId);
            }
            $venue_reponse['venue']= (object) [];
            $venue_reponse['venue']->id= $venueId;
            AuthHooks::venue_signup($app, $venue_reponse);
            return $app->render(200, array('msg' => 'Venue added successfully.', 'venue' => $venueId));
        } else {
            return $app->render(400,  array('msg' => 'Could not add venue.'));
        }
    }
    
    static function updateVenueData($app, $userId) {
        
        $post = $app->request->post();
        
        $results = self::venue_validateVenueParams($post);
        if($results['error']) {
            return $app->render(400,  array('msg' => $results['msg']));
        } else if(!v::key('venueId', v::intVal())->validate($post)) {
            return $app->render(400,  array('msg' => 'Invalid venue Id.'));
        }
        
        $validVenue = self::venue_getVenueArray($post);
        $validVenue[':id'] = $post['venueId'];
        $venue = VenueData::updateVenue($validVenue, $userId);

        $user =  VenueData::updateUser(array(
            ':id' => $userId,
            ':name_first' => $app->request->post('nameFirst'),
            ':name_last' => $app->request->post('nameLast'),
            ':last_updated_by' => APIAuth::getUserId()
        ));

        $venue_reponse['venue']= (object) [];
        $venue_reponse['venue']->id= $post['venueId'];
        AuthHooks::venue_signup($app, $venue_reponse, true);
 
        if($post['triviaDay']!=='' && $post['triviaTime']!=='') {
            $venueScheduleId = VenueData::manageVenueTriviaShcedule(array(
                ':trivia_day' => $post['triviaDay'], 
                ':trivia_time' => $post['triviaTime'], 
                ':created_user_id' => APIAuth::getUserId(),
                ':last_updated_by' => APIAuth::getUserId(),
                ':venue_id' => $post['venueId']
                ), $post['venueId']);
        }

        if($user && $venue) {
            $user = UserData::selectUserById($userId);
            $venue = VenueData::getVenueByUser($userId);
            return $app->render(200, array('msg' => 'Venue has been saveed.', 'user' => $user, 'venue' => $venue));
        } else {
            return $app->render(400,  array('msg' => 'Could not update venue.'));
        }
    }

    static function saveVenue($app, $venueId) {
        
        $post = $app->request->post();
        
        $results = self::venue_validateVenueParams($post);
        if($results['error']) {
            return $app->render(400,  array('msg' => $results['msg']));
        }
        
        // Add the verifed venue
        $validVenue = self::venue_getVenueArray($post);
        $validVenue[':id'] = $venueId;
        if(!VenueData::updateVenue($validVenue)) {
            return $app->render(400,  array('msg' => 'Could not update joint. Please try again later.'));
        }
        
        if((v::key('disabled', v::stringType()->length(1,5))->validate($post)) && 
                ($post['disabled'] === true || $post['disabled'] === 'true')) {
            VenueData::disableVenue($venueId);
        } else if((v::key('disabled', v::stringType()->length(1,5))->validate($post)) && 
                ($post['disabled'] === false || $post['disabled'] === 'false')) {
            VenueData::enableVenue($venueId);
        }
        
        $venue_reponse['venue']= (object) [];
        $venue_reponse['venue']->id= $venueId;
        AuthHooks::venue_signup($app, $venue_reponse,true);

        $venueScheduleId = VenueData::manageVenueTriviaShcedule(array(                              
            ':trivia_day' => $post['triviaDay'], 
            ':trivia_time' => $post['triviaTime'], 
            ":created_user_id" => APIAuth::getUserId(),
            ":last_updated_by" => APIAuth::getUserId(),
            ':venue_id' => $venueId 
            ),$venueId);

        return $app->render(200, array('msg' => "Venue '{$post['venue']}' was successfully saved.", 'venueScheduled' => $venueScheduleId));
    }

    static function deleteVenue($app, $venueId) {
        if(VenueData::deleteVenue($venueId)) {
            return $app->render(200,  array('msg' => 'Venue has been deleted.'));
        } else {
            return $app->render(400,  array('msg' => 'Could not delete venue. Check your parameters and try again.'));
        }
    }

    private static function venue_validateVenueParams($post) {
        $result = array('error' => false);
        $dayNames = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
        
        if(!v::key('venue', v::stringType())->validate($post) || 
           !v::key('address', v::stringType())->validate($post) || 
           !v::key('city', v::stringType())->validate($post) || 
           !v::key('state', v::stringType())->validate($post) || 
           !v::key('zip', v::stringType())->validate($post)) {
            $result = array('error' => true, 'msg' => 'Invalid joint parameters. Check your imput and try again.');
        } else if(v::key('userId', v::stringType())->validate($post) && !self::venue_updateUserPassword($post, $post['userId'])) {
            $result = array('error' => true, 'msg' => 'Could not update users password. Check your parameters and try again.');
        } else if (!v::key('triviaDay', v::stringType())->validate($post) ||
                !in_array(strtolower($post['triviaDay']), $dayNames)) {
            $result = array('error' => true, 'msg' => 'Invalid day of the week provided. Check your parameters and try again.');
        } else if (!v::key('triviaTime', v::stringType())->validate($post) ||
                !preg_match('/^(1[0-2]|0?[1-9]):[0-5][0-9] (AM|PM)$/i', $post['triviaTime'])) {
            $result = array('error' => true, 'msg' => 'Invalid time provided. Check your parameters and try again.');
        } else if (v::key('phone', v::stringType()->length(10,20))->validate($post) &&
                !preg_match( '/^[+]?([\d]{0,3})?[\(\.\-\s]?([\d]{3})[\)\.\-\s]*([\d]{3})[\.\-\s]?([\d]{4})$/', $post["phone"])) {
            $result = array('error' => true, 'msg' => 'Invalid joint phone provided. Check your parameters and try again.');
        } else if (v::key('website', v::stringType()->length(5,255))->validate($post) && !v::url()->validate($post["website"])) {
            $result = array('error' => true, 'msg' => 'Invalid joint website url provided. Check your parameters and try again.');
        } else if (v::key('facebook', v::stringType()->length(5,255))->validate($post) && 
                !preg_match('/(?:https?:\/\/)?(?:www\.)?facebook\.com\/(?:(?:\w)*#!\/)?(?:pages\/)?(?:[\w\-]*\/)*([\w\-\.]*)/', $post["facebook"])) {
            $result = array('error' => true, 'msg' => 'Invalid facebook url provided. Check your parameters and try again.');
        } 

        return $result;
    }
    
    private static function venue_getVenueArray($post) {
        return array(
            ':name' => $post['venue'], 
            ':address' => $post['address'], 
            ':address_b' => (v::key('addressb', v::stringType())->validate($post)) ? $post['addressb'] : '', 
            ':city' => $post['city'], 
            ':state' => $post['state'], 
            ':zip' => $post['zip'], 
            ':phone' => (v::key('phone', v::stringType())->validate($post)) ? $post['phone'] : '', 
            ':phone_extension' => (v::key('phoneExtension', v::stringType())->validate($post)) ? $post['phoneExtension'] : '', 
            ':website' => (v::key('website', v::stringType())->validate($post)) ? $post['website'] : '', 
            ':facebook_url' => (v::key('facebook', v::stringType())->validate($post)) ? $post['facebook'] : '', 
            ':logo' => (v::key('logo', v::stringType())->validate($post)) ? $post['logo'] : '', 
            ':referral' => (v::key('referralCode', v::stringType())->validate($post)) ? $post['referralCode'] : '', 
            ":last_updated_by" => APIAuth::getUserId()
        );
    }
    
    private static function venue_updateUserPassword($post, $userId) {
        $success = true;

        if(v::key('password', v::stringType())->validate($post) && 
           v::key('passwordB', v::stringType())->validate($post) && $post['password']!='' && $post['passwordB']!='') {
            if(!AuthControllerNative::validatePasswordRequirements($post, 'password')) {
                $success = false;
            } else {
                $data = array(':id' => $userId, ':password' => password_hash($post['password'], PASSWORD_DEFAULT));
                $success = AuthData::updateUserPassword($data);
            }
        }

        return $success;
    }
}
