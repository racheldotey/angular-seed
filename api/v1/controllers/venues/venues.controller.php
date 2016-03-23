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
