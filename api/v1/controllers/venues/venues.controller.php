<?php namespace API;
require_once dirname(__FILE__) . '/venues.data.php';
require_once dirname(dirname(dirname(__FILE__))) . '/services/api.auth.php';

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
        
        if(!v::key('venueName', v::stringType())->validate($post) || 
           !v::key('address', v::stringType())->validate($post) || 
           !v::key('city', v::stringType())->validate($post) || 
           !v::key('state', v::stringType())->validate($post) || 
           !v::key('zip', v::stringType())->validate($post)) {
            return $app->render(400, array('msg' => 'Add role failed. Check your parameters and try again.'));
        }
        
        // Add the verifed venue
        $venueId = VenueData::insertVenue(array(
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
            ":created_user_id" => APIAuth::getUserId(),
            ":last_updated_by" => APIAuth::getUserId()
        ));
        if($venueId) {
            return $app->render(200, array('venue' => $venueId));
        } else {
            return $app->render(400,  array('msg' => 'Could not add venue.'));
        }
    }
    
    static function updateVenueData($app, $userId) {
        $post=$app->request->post();
        $isValid=true;
        if(v::key('password', v::stringType())->validate($post) && 
           v::key('passwordB', v::stringType())->validate($post) && $post['password']!='' && $post['passwordB']!='')
        {
            if(!AuthControllerNative::validatePasswordRequirements($post, 'password'))
            {
                $isValid=false;
            }
            else{
                $data = array(
                    ':id' => $userId,
                    ':password' => password_hash($post['password'], PASSWORD_DEFAULT)
                    );

                $passwordChanged=AuthData::updateUserPassword($data);
                if(!$passwordChanged) {
                    $isValid=false;
                }
            }
        }
        if($isValid){
            $venuedata = array(
                ':created_user_id' => $userId,
                ':name' => $app->request->post('venueName'),
                ':address' => $app->request->post('address'),
                ':address_b' => $app->request->post('addressb'),
                ':city' => $app->request->post('city'),
                ':state' => $app->request->post('state'),
                ':zip' => $app->request->post('zip'),
                ':phone' => $app->request->post('phone'),
                ':website' => $app->request->post('website'),
                ':facebook_url' => $app->request->post('facebook'),
                ':hours' => $app->request->post('hours'),
                ':logo' => $app->request->post('logoUrl'),
                ':referral' => $app->request->post('referralCode'),
                ':last_updated' => date('Y-m-d H:i:s'),
                ':last_updated_by' => $userId
                );

            $userdata = array(
             ':id' => $userId,
             ':name_first' => $app->request->post('nameFirst'),
             ':name_last' => $app->request->post('nameLast'),
             ':email' => $app->request->post('email')
             );
            $user =  UserData::updateUser($userdata);
            $venue = VenueData::updatedataVenue($venuedata, $userId);
        }

        if( $isValid && isset($user) && $user ) {
            $user = UserData::selectUserById($userId);
            return $app->render(200, array('user' => $user, 'venue' => $venue));
        } else {
            return $app->render(400,  array('msg' => 'Could not update venue.'));
        }
    }

    static function saveVenue($app, $venueId) {
        $post = $app->request->post();
        
        if(!v::intVal()->validate($venueId) ||
           !v::key('venueName', v::stringType())->validate($post) || 
           !v::key('address', v::stringType())->validate($post) || 
           !v::key('city', v::stringType())->validate($post) || 
           !v::key('state', v::stringType())->validate($post) || 
           !v::key('zip', v::stringType())->validate($post)) {
            return $app->render(400, array('msg' => 'Add role failed. Check your parameters and try again.'));
        }
        
        // Add the verifed venue
        $venue = VenueData::updateVenue(array(
            ':id' => $venueId,
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
            ":last_updated_by" => APIAuth::getUserId()
        ));
        if($venue) {
            return $app->render(200, array('venue' => $venue));
        } else {
            return $app->render(400,  array('msg' => 'Could not update venue.'));
        }
    }

    static function deleteVenue($app, $venueId) {
        if(VenueData::deleteVenue($venueId)) {
            return $app->render(200,  array('msg' => 'Venue has been deleted.'));
        } else {
            return $app->render(400,  array('msg' => 'Could not delete venue. Check your parameters and try again.'));
        }
    }
}
