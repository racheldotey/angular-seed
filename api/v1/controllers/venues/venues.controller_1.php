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
        
        if(!v::key('venueName', v::stringType())->validate($post) || 
           !v::key('address', v::stringType())->validate($post) || 
           !v::key('city', v::stringType())->validate($post) || 
           !v::key('state', v::stringType())->validate($post) || 
           !v::key('zip', v::stringType())->validate($post))
        {
            return $app->render(400, array('msg' => 'Add role failed. Check your parameters and try again.'));
        }

        if(!v::url()->validate($post["website"])) 
        {
            return $app->render(400, array('msg' => $post["website"].' is not valid URL.')); 
        }

        if(!preg_match('/(?:https?:\/\/)?(?:www\.)?facebook\.com\/(?:(?:\w)*#!\/)?(?:pages\/)?(?:[\w\-]*\/)*([\w\-\.]*)/', $post["facebook"]))
        {
            return $app->render(400, array('msg' => $post["facebook"].' is not valid facebook URL.')); 
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

        if($post['triviaDay'] != '')
        {
            $status = in_array(strtolower($post['triviaDay']), $dayNames);
            if(!$status)
            {
                return $app->render(400, array('msg' => 'Day is not corect.'));
            }
        }

        if($post['triviaTime']!='')
        {
            if(!preg_match('/^(1[0-2]|0?[1-9]):[0-5][0-9] (AM|PM)$/i',$post['triviaTime'])) 
            {
                return $app->render(400, array('msg' => 'Time is not corect.'));
            }
        }

        if($post['phone']!='')
        {
            if (!preg_match( '/^[+]?([\d]{0,3})?[\(\.\-\s]?([\d]{3})[\)\.\-\s]*([\d]{3})[\.\-\s]?([\d]{4})$/', $post["phone"] ) ) 
            {
                return $app->render(400, array('msg' => 'This is not valid US format number.'));
            }
        }


        // Add the verifed venue
        $venueId = VenueData::insertVenue(array(
            ':name' => $post['venueName'], 
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
            ":created_user_id" => APIAuth::getUserId(),
            ":last_updated_by" => APIAuth::getUserId()
            ));



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
            return $app->render(200, array('venue' => $venueId));
        } else {
            return $app->render(400,  array('msg' => 'Could not add venue.'));
        }
    }
    
    static function saveVenue($app, $userId) {
        $post = $app->request->post();
        
        $dayNames = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
        
        if(!v::key('venue', v::stringType())->validate($post) || 
           !v::key('address', v::stringType())->validate($post) || 
           !v::key('city', v::stringType())->validate($post) || 
           !v::key('state', v::stringType())->validate($post) || 
           !v::key('zip', v::stringType())->validate($post)) {
            return $app->render(400, array('msg' => 'Venue update failed. Check your parameters and try again.'));
        } else if(!self::venueUpdatePassword($post, $userId)) {
            return $app->render(400, array('msg' => 'Could not update users password. Check your parameters and try again.'));
        } else if (!v::key('triviaDay', v::stringType())->validate($post) ||
                !in_array(strtolower($post['triviaDay']), $dayNames)) {
            return $app->render(400, array('msg' => 'Invalid day of the week provided. Check your parameters and try again.'));
        } else if (!v::key('triviaTime', v::stringType())->validate($post) ||
                !preg_match('/^(1[0-2]|0?[1-9]):[0-5][0-9] (AM|PM)$/i', $post['triviaTime'])) {
            return $app->render(400, array('msg' => 'Invalid time provided. Check your parameters and try again.'));
        } else if (v::key('phone', v::stringType())->validate($post) &&
                !preg_match( '/^[+]?([\d]{0,3})?[\(\.\-\s]?([\d]{3})[\)\.\-\s]*([\d]{3})[\.\-\s]?([\d]{4})$/', $post["phone"])) {
            return $app->render(400, array('msg' => 'Invalid venue phone provided. Check your parameters and try again.'));
        } else if (v::key('website')->validate($post) && !v::url()->validate($post["website"])) {
            return $app->render(400, array('msg' => 'Invalid venue website url provided. Check your parameters and try again.'));
        } else if (v::key('facebook')->validate($post) && 
                !preg_match('/(?:https?:\/\/)?(?:www\.)?facebook\.com\/(?:(?:\w)*#!\/)?(?:pages\/)?(?:[\w\-]*\/)*([\w\-\.]*)/', $post["facebook"])) {
            return $app->render(400, array('msg' => 'Invalid facebook url provided. Check your parameters and try again.'));
        } 
        
        $venuedata = array(
            ':name' => $post['venue'],
            ':address' => $post['address'],
            ':address_b' => $post['addressb'],
            ':city' => $post['city'],
            ':state' => $post['state'],
            ':zip' => $post['zip'],
            ':phone_extension' => (v::key('phoneExtension')->validate($post)) ? $post['phoneExtension'] : '',
            ':phone' => (v::key('phone')->validate($post)) ? $post['phone'] : '',
            ':website' => (v::key('website')->validate($post)) ? $post['website'] : '',
            ':facebook_url' => (v::key('facebook')->validate($post)) ? $post['facebook'] : '',
            ':logo' => (v::key('logoUrl')->validate($post)) ? $post['logoUrl'] : '',
            ':referral' => (v::key('logoUrl')->validate(referralCode)) ? $post['referralCode'] : '',
            ':last_updated_by' => $userId
        );
        $venueId = VenueData::updateVenue($venuedata, $userId);

        $userdata = array(
            ':id' => $userId,
            ':name_first' => $post['nameFirst'],
            ':name_last' => $post['nameLast'],
            ':email' => $post['email']
         );
        $user =  UserData::updateUser($userdata);

        $venue_reponse['venue']= (object) [];
        $venue_reponse['venue']->id= $venue->id;
        AuthHooks::venue_signup($app, $venue_reponse,true);




//ucwords
            
            if($post['triviaDay']!='' && $post['triviaTime']!=''){

                $venueScheduleId = VenueData::manageVenueTriviaShcedule(array(
                    ':trivia_day' => $post['triviaDay'], 
                    ':trivia_time' => $post['triviaTime'], 
                    ':created_user_id' => $userId,
                    ':last_updated_by' => $userId,
                    ':venue_id' => $venue->id
                    ),$venue->id);
            }


        if( $isValid && isset($user) && $user ) {
            $user = UserData::selectUserById($userId);
            $venue = VenueData::getVenueByUser($userId);
            return $app->render(200, array('user' => $user, 'venue' => $venue));
        } else {
            return $app->render(400,  array('msg' => 'Could not update venue.'));
        }
    }
    
    private static function venueUpdatePassword($post, $userId) {
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

    static function deleteVenue($app, $venueId) {
        if(VenueData::deleteVenue($venueId)) {
            return $app->render(200,  array('msg' => 'Venue has been deleted.'));
        } else {
            return $app->render(400,  array('msg' => 'Could not delete venue. Check your parameters and try again.'));
        }
    }
}

        
        
        /*
         * 
        
        
        
        address
:
"1313 Some Lane"
addressb
:
"Down Stairs"
city
:
"Clifton Park"
created
:
"2016-01-29 14:50:00"
createdBy
:
"Rachel Carbone"
createdByEmail
:
"rachellcarbone@gmail.com"
facebook
:
"http://www.facebook.com/hoppingjoint"
id
:
"1"
logo
:
"data:image/png;base64,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…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"
phone
:
"(555) 555 - 5555"
phoneExtension
:
"123"
referralCode
:
null
state
:
"NY"
triviaDay
:
"Friday"
triviaTime
:
"8:20 PM"
triviaTimeDate
:
Thu May 05 2016 20:20:50 GMT-0400 (Eastern Daylight Time)
venue
:
"Bob's Fish Fry n' Bar"
website
:
"http://www.bobsfishfry.com"
zip
:
"12065"
         */