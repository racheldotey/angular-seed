<?php namespace API;
require_once dirname(__FILE__) . '/hosts.controller.php';

class HostRoutes {

    static function addRoutes($app, $authenticateForRole) {

        //* /venue/ routes - registered users only

       $app->post("/hostsdata/update/:userId/",$authenticateForRole('registered-user'), function ($userId) use ($app){
            HostController::updateHostData($app, $userId);
        });
       $app->group('/host', $authenticateForRole('registered-user'), function () use ($app) {

            /*
             * id
             */
            $app->map("/get/:hostId/", function ($hostId) use ($app) {
                HostController::getHost($app, $hostId);
            })->via('GET', 'POST');

			/*
             * id
             */
            $app->map("/getHostByUser/:userId/", function ($userId) use ($app) {
                HostController::getHostByUserId($app, $userId);
            })->via('GET', 'POST');
			
            /*
             * 
             */
            $app->post("/insert/", function () use ($app) {
                HostController::addHost($app);
            });

            /*
             * id, 
             */
            $app->post("/update/:hostId/", function ($hostId) use ($app) {
                HostController::saveHost($app, $hostId);
            });


            /*
             * id
             */
            $app->map("/delete/:hostId/", function ($hostId) use ($app) {
                HostController::deleteHost($app, $hostId);
            })->via('DELETE', 'POST');

			 

         });
}
}