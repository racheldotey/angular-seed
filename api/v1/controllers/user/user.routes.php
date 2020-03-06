<?php namespace API;
 require_once dirname(__FILE__) . '/get/getUserController.php';
 require_once dirname(__FILE__) . '/permissions/userPermissionsController.php';
 require_once dirname(__FILE__) . '/update/updateUserController.php';

class UserRoutes {
    // TODO: API Docs
    public function addRoutes($app, $authenticateForRole) {
        
        // /user/id - members can get their own profile
        // TODO: Add 'self' to api middleware and make it accept multiple roles (array)
        $this->map(['GET', 'POST'], '/user/{userId}', '\API\GetUserController:getMemberDataObject')
            ->add(new \API\ApiAuthMiddleware($slimApp->getContainer(), 'member'));

        // id, nameFirst, nameLast, email, phone
        // TODO: Add 'self' to api middleware and make it accept multiple roles (array)
        $this->post('/user/update/{userId}', '\API\UpdateUserController:updateUser')
            ->add(new \API\ApiAuthMiddleware($slimApp->getContainer(), 'member'));
            
        // /user/ routes - admin users only
        $app->group('/user', function () {

            // userId
            $this->map(['GET', 'POST'], '/get/{userId}', '\API\GetUserController:getMemberDataObject');

            // nameFirst, nameLast, email, password
            $this->post('/insert', '\API\UpdateUserController:adminInsertUser');

            // userId
            $this->map(['DELETE', 'POST'], '/delete/{userId}', '\API\UpdateUserController:adminDeleteUser');
            

            // userId, groupId
            $this->post('/assign-group', '\API\UserPermissionsController:assignGroup');
            
            // userId, groupId
            $this->post('/unassign-group', '\API\UserPermissionsController:unassignGroup');
            
        })->add(new \API\ApiAuthMiddleware($slimApp->getContainer(), 'admin'));
    }
}