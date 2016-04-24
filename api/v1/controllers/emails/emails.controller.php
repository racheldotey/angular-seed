<?php namespace API;
require_once dirname(dirname(dirname(__FILE__))) . '/services/api.mailer.php';

use \Respect\Validation\Validator as v;


class EmailController {

    static function sendPlayerInviteEmail($app) {
        $post = $app->request->post();
        if(!v::key('email', v::email())->validate($post)) {
            return $app->render(400,  array('msg' => 'Invalid email. Check your parameters and try again.'));
        }
        $sent = ApiMailer::sendWebsiteSignupInvite($post['email']);
        if($sent) {
            return $app->render(200, array('msg' => "Player invite sent to '{$post['email']}'."));
        } else {
            return $app->render(400, array('msg' => 'Could not send player invite.'));
        }
    }
    
}
