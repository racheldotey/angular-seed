<?php namespace API;
require_once dirname(__FILE__) . '/emails.data.php';
require_once dirname(dirname(dirname(__FILE__))) . '/services/api.auth.php';
require_once dirname(dirname(dirname(__FILE__))) . '/services/api.mailer.php';

use \Respect\Validation\Validator as v;


class EmailController {

    private static function makeInviteToken() {
        return hash('sha1', uniqid());
    }
    
    static function sendPlayerInviteEmail($app) {
        $post = $app->request->post();
        if(!v::key('email', v::email())->validate($post)) {
            return $app->render(400,  array('msg' => 'Invalid email. Check your parameters and try again.'));
        }
        $token = self::makeInviteToken();
        $firstName = (v::key('nameFirst', v::stringType())->validate($post)) ? $post['nameFirst'] : NULL;
        $lastName = (v::key('nameFirst', v::stringType())->validate($post)) ? $post['nameFirst'] : NULL;
        $saved = EmailData::insertPlayerInvite(array(
            ":token" => $token, 
            ":name_first" => $firstName, 
            ":name_last" => $lastName, 
            ":email" => $post['email'],
            ":phone" => (v::key('phone', v::stringType())->validate($post)) ? $post['phone'] : NULL, 
            ":created_user_id" => APIAuth::getUserId()
        ));
        if(!$saved) {
            return $app->render(400,  array('msg' => 'Could not create invite. Check your parameters and try again.'));
        }
                
        $isFirstSet = (is_null($firstName)) ? false : $firstName;
        $name = ($isFirstSet === false || is_null($lastName)) ? '' : "{$firstName} {$lastName}";
        $sent = ApiMailer::sendWebsiteSignupInvite($post['email'], $token, $name);
        if($sent) {
            return $app->render(200, array('msg' => "Player invite sent to '{$post['email']}'."));
        } else {
            return $app->render(400, array('msg' => 'Could not send player invite.'));
        }
    }
    
}
