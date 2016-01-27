<?php namespace API;
 require_once dirname(__FILE__) . '/venues.controller.php';

class VenueRoutes {
    
    static function addRoutes($app, $authenticateForRole) {
            
        //* /venue/ routes - registered users only
        
        $app->group('/venue', $authenticateForRole('registered-user'), function () use ($app) {
            
            $app->map("/get/:venueId/", function ($venueId) use ($app) {
                VenueController::getVenue($app, $venueId);
            })->via('GET', 'POST');

            $app->post("/insert/", function () use ($app) {
                VenueController::addVenue($app);
            });

            $app->post("/update/:venueId/", function ($venueId) use ($app) {
                VenueController::saveVenue($app, $venueId);
            });

            $app->map("/delete/:venueId/", function ($venueId) use ($app) {
                VenueController::deleteVenue($app, $venueId);
            })->via('DELETE', 'POST');
            
        });
    }
}