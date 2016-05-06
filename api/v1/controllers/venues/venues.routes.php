<?php namespace API;
 require_once dirname(__FILE__) . '/venues.controller.php';

class VenueRoutes {
    
    static function addRoutes($app, $authenticateForRole) {
            
        //* /venue/ routes - registered users only
        
	$app->post("/venuesdata/update/:userId/",$authenticateForRole('registered-user'), function ($venueId) use ($app) {
            VenueController::updateVenueData($app, $venueId);
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
    }
}