<?php namespace API;
 require_once dirname(__FILE__) . '/user.controller.php';

class UserRoutes {
    // TODO: API Docs
    static function addRoutes($app, $authenticateForRole) {
        
        //* /user/id - members can get their own profile
        
        $app->map("/user/get/:userId/", $authenticateForRole('registered-user'), function ($userId) use ($app) {
            UserController::selectUser($app, $userId);
        })->via('GET', 'POST');

        /*
         * id, nameFirst, nameLast, email, phone
         */
        $app->post("/user/update/:userId/", $authenticateForRole('registered-user'), function ($userId) use ($app) {
            UserController::updateUser($app, $userId);
        });
            
        //* /user/ routes - admin users only

        $app->group('/user', $authenticateForRole('admin'), function () use ($app) {

            /*
             * nameFirst, nameLast, email, password
             */
            $app->post("/insert/", function () use ($app) {
                UserController::insertUser($app);
            });

            /*
             * id
             */
            $app->map("/delete/:userId/", function ($userId) use ($app) {
                UserController::deleteUser($app, $userId);
            })->via('DELETE', 'POST');
            
            /*
             * userId, groupId
             */
            $app->post("/unassign-group/", function () use ($app) {
                UserController::unassignGroup($app);
            });
            
            /*
             * userId, groupId
             */
            $app->post("/assign-group/", function () use ($app) {
                UserController::assignGroup($app);
            });
            
        });
    }
}