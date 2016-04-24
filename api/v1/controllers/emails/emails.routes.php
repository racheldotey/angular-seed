<?php namespace API;
 require_once dirname(__FILE__) . '/emails.controller.php';

class EmailRoutes {
    
    static function addRoutes($app, $authenticateForRole) {
        
        $app->group('/send-email', $authenticateForRole('public'), function () use ($app) {
            
            /*
             * email
             */
            $app->map("/invite-player/", function () use ($app) {
                EmailController::sendPlayerInviteEmail($app);
            })->via('GET', 'POST');
            
        });
    }
}