<?php namespace API;
require_once dirname(__FILE__) . '/hosts.data.php';
require_once dirname(dirname(__FILE__)) . '/venues/venues.controller.php';
require_once dirname(dirname(__FILE__)) . '/venues/venues.data.php';
require_once dirname(dirname(dirname(__FILE__))) . '/services/api.auth.php';
require_once dirname(dirname(__FILE__)) . '/auth/auth.hooks.php';
use \Respect\Validation\Validator as v;
class HostController 
{
    static function getHost($app, $hostId) {
        if(!v::intVal()->validate($hostId)) {
            return $app->render(400,  array('msg' => 'Could not select host. Check your parameters and try again.'));
        }
        $host = HostData::getHost($hostId);
        if($host) {
            return $app->render(200, array('host' => $host));
        } else {
            return $app->render(400,  array('msg' => 'Could not select host.'));
        }
    }
    //region to get host information by user , for profile host information fetching
    static function getHostByUserId($app, $userId) {
        if(!v::intVal()->validate($userId)) {
            return $app->render(400,  array('msg' => 'Could not select user. Check your parameters and try again.'));
        }
        $host= HostData::getHostByUser($userId);
        if($host) {
            return $app->render(200, array('host' => $host));
        } else {
            return $app->render(200,  array('msg' => 'Could not select host.'));
        }
    }
    //
    static function addHost($app) 
    {
        $post = $app->request->post();
        $results = self::hosts_validateHostParams($post);
        if($results['error']) 
        {
            return $app->render(400,  array('msg' => $results['msg']));
        }
        $venueIds=$post['venueIds'];
        if(!empty($venueIds))
        {
            /*Add the verifed host*/
            $validHost = self::host_getHostArray($post);
            $validHost[':created_user_id'] = APIAuth::getUserId();
            $validHost[':trv_users_id'] = APIAuth::getUserId();
            $validHost[':host_accepted_terms'] = 'y';
            $hostId = HostData::insertHost($validHost);
            if($hostId) 
            {
                foreach ($post['venueIds'] as $key => $venueId)
                {
                    $assignment = array(
                        ':host_id' => $hostId,
                        ':venue_id' => $venueId,
                        ":created_user_id" =>  APIAuth::getUserId(),
                        ":last_updated_by" =>  APIAuth::getUserId()
                        );
                    HostData::insertHostVenueAssignment($assignment);
                    $venueData = VenueData::getVenue($venueId); 
                    if($venueData->triviaDay!='' && $venueData->triviaTime!='')
                    {
                        $hostScheduleId = HostData::insertHostTriviaSchedules(array(
                            ':host_id' => $hostId,     
                            ':venue_id' => $venueId,                    
                            ':trivia_day' => $venueData->triviaDay, 
                            ':trivia_time' => $venueData->triviaTime, 
                            ":created_user_id" => APIAuth::getUserId(),
                            ":last_updated_by" => APIAuth::getUserId()
                            ));
                    }     
                }
                return $app->render(200, array('host' => $hostId));
            } else {
                return $app->render(400,  array('msg' => 'Could not add host.'));
            }
        }
        else{
            return $app->render(400,  array('msg' => 'Invalid venue Id.'));
        }
        /*check for venue id ends*/
    }
    static function updateHostData($app, $userId) 
    {
        $post = $app->request->post();
        
        $host_user=HostData::getHostByUser($userId);
        
        if(!v::key('hostId', v::intVal())->validate($post) && $host_user) 
        {
            return $app->render(400,  array('msg' => 'Invalid host Id.'));
        }
        if($host_user){
            $results = self::hosts_validateHostParams($post);
        }
        else{
            $results = self::hosts_validateHostParams($post);
        }
        if($results['error']) 
        {
            return $app->render(400,  array('msg' => $results['msg']));
        } 
        if($host_user){
            $validHost = self::host_getHostArray($post);
            $validHost[':id'] = $post['hostId'];
            $hostId=$post['hostId'];
            $host = HostData::updateHost($validHost);  
        }
        else{
            $validHost = self::host_getHostArray($post);
            $validHost[':created_user_id'] = $userId;
            $validHost[':trv_users_id'] = $userId;
            $validHost[':host_accepted_terms'] = 'y';
            $host = $hostId = HostData::insertHost($validHost);  
        }
        $user =  HostData::updateUser(array(
            ':id' => $userId,
            ':name_first' => $app->request->post('nameFirst'),
            ':name_last' => $app->request->post('nameLast'),
            ':last_updated_by' => APIAuth::getUserId()
            ));
        if($user && $host) 
        {
            $venue=false;
            $user = UserData::selectUserById($userId);
            $host = HostData::getHost($hostId);
            if($hostId>0 && v::key('venueIds', v::arrayVal())->validate($post) && !empty($post['venueIds']) ){
                foreach ($post['venueIds'] as $key => $venueId)
                {
                    $assignment_condition=array(
                        ':host_id' => $hostId,     
                        ':venue_id' => $venueId
                        );
                    $host_assignment_exists=HostData::getHostVenueAssignment($assignment_condition);
                    if(!$host_assignment_exists){
                        $assignment = array(
                            ':host_id' => $hostId,
                            ':venue_id' => $venueId,
                            ":created_user_id" =>  APIAuth::getUserId(),
                            ":last_updated_by" =>  APIAuth::getUserId()
                            );
                        HostData::insertHostVenueAssignment($assignment);
                        $venueData = VenueData::getVenue($venueId); 
                        if($venueData->triviaDay!='' && $venueData->triviaTime!='')
                        {
                            $hostScheduleId = HostData::insertHostTriviaSchedules(array(
                                ':host_id' => $hostId,     
                                ':venue_id' => $venueId,                    
                                ':trivia_day' => $venueData->triviaDay, 
                                ':trivia_time' => $venueData->triviaTime, 
                                ":created_user_id" => APIAuth::getUserId(),
                                ":last_updated_by" => APIAuth::getUserId()
                                ));
                        }
                    }
                }
            }
            if((v::key('disabled', v::stringType()->length(1,5))->validate($post)) && 
                ($post['disabled'] === true || $post['disabled'] === 'true')) 
            {
                HostData::disableHost($validHost[':id']);
            } 
            else if((v::key('disabled', v::stringType()->length(1,5))->validate($post)) && 
                ($post['disabled'] === false || $post['disabled'] === 'false')) 
            {
                HostData::enableHost($validHost[':id']);
            }
            return $app->render(200, array('msg' => 'Host has been saved.', 'user' => $user, 'host' => $host));
        }
        else {
            return $app->render(400,  array('msg' => 'Could not update host.'));
        }
    }
    static function saveHost($app, $hostId) {
        $post = $app->request->post();
        $results = self::hosts_validateHostParams($post);
        if($results['error']) {
            return $app->render(400,  array('msg' => $results['msg']));
        }
        $validHost = self::host_getHostArray($post);
        $validHost[':id'] = $hostId;
        if(!HostData::updateHost($validHost)) {
            return $app->render(400,  array('msg' => 'Could not update host. Please try again later.'));
        }
        if((v::key('disabled', v::stringType()->length(1,5))->validate($post)) && 
            ($post['disabled'] === true || $post['disabled'] === 'true')) 
        {
            HostData::disableHost($hostId);
        } 
        else if((v::key('disabled', v::stringType()->length(1,5))->validate($post)) && 
            ($post['disabled'] === false || $post['disabled'] === 'false')) 
        {
            HostData::enableHost($hostId);
        }
        return $app->render(200, array('msg' => "Host was successfully saved.", 'hostId' => $hostId));
    }
    static function updateTrivia($app, $hostId) {
        $dayNames = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
        $post = $app->request->post();

        if(!v::key('venueId', v::intVal())->validate($post))  
        {
            return $app->render(400,  array('msg' => 'Invalid Venue Id.'));
        }
        $venueId=$post['venueId'];
        $venue = VenueData::getVenue($venueId);
        if(!$venue) {
            return $app->render(400,  array('msg' => 'Could not select venue.'));
        }

        if ((!v::key('triviaDay', v::stringType())->validate($post)) || (v::key('triviaDay', v::stringType())->validate($post) && !in_array(strtolower($post['triviaDay']), $dayNames))) {
           return $app->render(400,array('msg' => 'Invalid day of the week provided. Check your parameters and try again.'));
        } else if ((!v::key('triviaTime', v::stringType())->validate($post)) ||
            (v::key('triviaTime', v::stringType())->validate($post) && !preg_match('/^(1[0-2]|0?[1-9]):[0-5][0-9] (AM|PM)$/i', $post['triviaTime']))) {
             return $app->render(400,array('error' => true, 'msg' => 'Invalid time provided. Check your parameters and try again.'));
        } 

        $venueScheduleId = HostData::manageHostTriviaShcedule(array(
            ':trivia_day' => $post['triviaDay'], 
            ':trivia_time' => $post['triviaTime'], 
            ":created_user_id" => APIAuth::getUserId(),
            ":last_updated_by" => APIAuth::getUserId(),
            ':venue_id' => $venueId,
            ':host_id' => $hostId
            ),$hostId,$venueId);
        if($venueScheduleId){
            return $app->render(200, array('msg' => "Host was successfully saved.", 'hostId' => $hostId));
        }
        else{
            return $app->render(400,  array('msg' => 'Could not select venue.'));
        }
        
    }
    static function removeVenue($app,$hostId)
    {
        $post = $app->request->post();

        if(!v::intVal()->validate($hostId)) {
            return $app->render(400,  array('msg' => 'Could not select host. Check your parameters and try again.'));
        }
        $host = HostData::getHost($hostId);
        if($host) 
        {
            $venueId = $post['venueId'];

            $assignment_condition=array(
                ':host_id' => $hostId,     
                ':venue_id' => $venueId
                );
            $host_assignment_exists=HostData::getHostVenueAssignment($assignment_condition);

            if($host_assignment_exists)
            {
                $host_delete_process = HostData::deleteVenueData($hostId,$venueId);

                if(!$host_assignment_exists)
                {
                    return $app->render(400,  array('msg' => 'Venue not exist.Check your parameters and try again.'));
                }
                else
                {
                    return $app->render(200,  array('msg' => 'Venue has been deleted.'));
                }
            }
            else{
                return $app->render(400,  array('msg' => 'Could not select venue with this host. Check your parameters and try again.'));
            }
        } 
        else 
        {
            return $app->render(400,  array('msg' => 'Could not select host.'));
        }
    }
    static function deleteHost($app, $hostId) {
        if(HostData::deleteHost($hostId)) {
            return $app->render(200,  array('msg' => 'Host has been deleted.'));
        } else {
            return $app->render(400,  array('msg' => 'Could not delete host. Check your parameters and try again.'));
        }
    }
    private static function hosts_validateHostParams($post) 
    {
        $result = array('error' => false);
        if(!v::key('host_address', v::stringType())->validate($post) || 
            !v::key('host_city', v::stringType())->validate($post) || 
            !v::key('host_state', v::stringType())->validate($post) || 
            !v::key('host_zip', v::stringType())->validate($post)) 
        {
            $result = array('error' => true, 'msg' => 'Invalid host parameters. Check your imput and try again.');
        }
        else if(v::key('userId', v::stringType())->validate($post) && !self::host_updateUserPassword($post, $post['userId'])) 
        {
            $result = array('error' => true, 'msg' => 'Could not update users password. Check your parameters and try again.');
        }  
        else if (v::key('phone', v::stringType()->length(10,20))->validate($post) &&
            !preg_match( '/^[+]?([\d]{0,3})?[\(\.\-\s]?([\d]{3})[\)\.\-\s]*([\d]{3})[\.\-\s]?([\d]{4})$/', $post["phone"]))
        {
            $result = array('error' => true, 'msg' => 'Invalid joint phone provided. Check your parameters and try again.');
        } 
        else if (v::key('host_website', v::stringType()->length(5,255))->validate($post) && !v::url()->validate($post["host_website"]))
        {
            $result = array('error' => true, 'msg' => 'Invalid joint website url provided. Check your parameters and try again.');
        } 
        else if (v::key('host_facebook', v::stringType()->length(5,255))->validate($post) && 
            !preg_match('/(?:https?:\/\/)?(?:www\.)?facebook\.com\/(?:(?:\w)*#!\/)?(?:pages\/)?(?:[\w\-]*\/)*([\w\-\.]*)/', $post["host_facebook"])) 
        {
            $result = array('error' => true, 'msg' => 'Invalid facebook url provided. Check your parameters and try again.');
        } 


        if(v::key('venueIds', v::arrayVal())->validate($post) && !empty($post['venueIds']))
        {
            foreach ($post['venueIds'] as $i => $venueId) {
                if(!v::intVal()->validate($venueId)){
                    $result = array('error' => true, 'msg' => 'Invalid venue Ids');
                }
                else{
                    $venueExists=VenueData::getVenue($venueId);
                    if(!$venueExists)
                    {
                        $result = array('error' => true, 'msg' => 'Invalid venue Id');
                    }
                }
            }
        }
        return $result;
    }
    private static function host_getHostArray($post) {
        return array(
            ':address' => $post['host_address'], 
            ':address_b' => (v::key('host_addressb', v::stringType())->validate($post)) ? $post['host_addressb'] : '', 
            ':city' => $post['host_city'], 
            ':state' => $post['host_state'], 
            ':zip' => $post['host_zip'], 
            ':phone' => (v::key('phone', v::stringType())->validate($post)) ? $post['phone'] : '', 
            ':phone_extension' => (v::key('phone_extension', v::stringType())->validate($post)) ? $post['phone_extension'] : '', 
            ':website' => (v::key('host_website', v::stringType())->validate($post)) ? $post['host_website'] : '', 
            ':facebook_url' => (v::key('host_facebook', v::stringType())->validate($post)) ? $post['host_facebook'] : '',        
            ":last_updated_by" => APIAuth::getUserId()
            );
    }
    private static function host_updateUserPassword($post, $userId) {
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
