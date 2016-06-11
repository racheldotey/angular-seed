<?php namespace API;
require_once dirname(__FILE__) . '/venues.controller.php';

class VenueRoutes {

    static function addRoutes($app, $authenticateForRole) {

        //* /venue/ routes - registered users only

     $app->post("/venuesdata/update/:userId/",$authenticateForRole('registered-user'), function ($userId) use ($app) {
        VenueController::updateVenueData($app, $userId);
     });
     $app->post('/venue/list/', $authenticateForRole('public'), function () use ($app) {
        VenueController::getVenueList($app);
     });
     $app->group('/venue', $authenticateForRole('registered-user'), function () use ($app) {

            /*
             * id
             */
            $app->map("/get/:venueId/", function ($venueId) use ($app) {
                VenueController::getVenue($app, $venueId);
            })->via('GET', 'POST');

            /*
             * 
             */
           /* $app->map("/list/", function () use ($app) {
                VenueController::getVenueList($app);
            })->via('GET', 'POST');*/


            /*
             * 
             */
            $app->post("/insert/", function () use ($app) {
                VenueController::addVenue($app);
            });

            /*
             * id, 
             */
            $app->post("/update/:venueId/", function ($venueId) use ($app) {
                VenueController::saveVenue($app, $venueId);
            });


            /*
             * id
             */
            $app->map("/delete/:venueId/", function ($venueId) use ($app) {
                VenueController::deleteVenue($app, $venueId);
            })->via('DELETE', 'POST');

			 /*
             * id
             */
            $app->map("/getbyuser/:userId/", function ($userId) use ($app) {
                VenueController::getVenueByUser($app, $userId);
            })->via('GET', 'POST');

        });
		 $app->group('/simplelist', $authenticateForRole('public'), function () use ($app) {
			$app->map("/publicvenues", function () use ($app) {
                VenueController::getVenueList($app);
            })->via('GET', 'POST');
		});	
 }
}