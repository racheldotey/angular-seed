<?php namespace API;

 require_once dirname(__FILE__) . '/authentication/authenticationController.php';
 require_once dirname(__FILE__) . '/password-managment/passwordManagmentController.php';
 require_once dirname(__FILE__) . '/signup/signupController.php';

/* @author  Rachel L Carbone <hello@rachellcarbone.com> */

class AuthRoutes {
    
    public function addRoutes(\Slim\App $slimApp) {
        
        $slimApp->group('/auth', function () {

            //////////////////////////////
            // Authentication Controller /
            //////////////////////////////

            /**
             * @api {post} /auth/authenticate Confirm api key and token pair represents an active user login session.
             * @apiName Authenticate
             * @apiGroup Auth
             *
             * @apiParam {String} apiKey User session key.
             * @apiParam {String} apiToken User session unhashed token.
             *
             * @apiSuccessExample {json} Success-Response:
             *      HTTP/1.1 200: OK
             *      {
             *          "data": {
             *              "authenticated": true,
             *              "sessionLifeHours": 1
             *              "user": {
             *                  "id": "28",
             *                  "nameFirst": "Rachel",
             *                  "nameLast": "Testing",
             *                  "email": "racheltest@testing.com",
             *                  "displayName": "Rachel",
             *                  "roles": ['3'],
             *                  "apiKey": "caf02551768a09e1aed8946ecacce3b01f253884a08bded1f1a76520b8f0c4e847914a1daea072ab957582a2c32beceacd62b5e6842f18ef2b21a3f13b16c374",
             *                  "apiToken": "c88e7640de8f34c18d7d07d6d0a26b0d9896f188766e445bac32a44cb275ba89"
             *              }
             *          },
             *          "meta": {
             *              "error": false,
             *              "status": 200
             *          }
             *      }
             */
            $this->post('/authenticate', '\API\AuthenticationController:isAuthenticated');

            /**
             * @api {post} /auth/login Standard user login.
             * @apiName Login
             * @apiGroup Auth
             *
             * @apiParam {String} email User email address.
             * @apiParam {String} passowrd User unencrypted password.
             * 
             * @apiSuccessExample {json} Success-Response:
             *      HTTP/1.1 200: OK
             *      {
             *          "data": {
             *              "authenticated": true,
             *              "sessionLifeHours": 1
             *              "user": {
             *                  "id": "28",
             *                  "nameFirst": "Rachel",
             *                  "nameLast": "Testing",
             *                  "email": "racheltest@testing.com",
             *                  "displayName": "Rachel",
             *                  "roles": ['3'],
             *                  "apiKey": "caf02551768a09e1aed8946ecacce3b01f253884a08bded1f1a76520b8f0c4e847914a1daea072ab957582a2c32beceacd62b5e6842f18ef2b21a3f13b16c374",
             *                  "apiToken": "c88e7640de8f34c18d7d07d6d0a26b0d9896f188766e445bac32a44cb275ba89"
             *              }
             *          },
             *          "meta": {
             *              "error": false,
             *              "status": 200
             *          }
             *      }
             */
            $this->post('/login', '\API\AuthenticationController:login');

            /* email, nameFirst, nameLast, facebookId, accessToken */
            $this->post("/login/facebook", '\API\AuthenticationController:facebookLogin');

            $this->post('/logout', '\API\AuthenticationController:logout');

            //////////////////////////////
            // Signup Controller /////////
            //////////////////////////////
            
            /**
             * @api {post} /auth/signup Standard user signup.
             * @apiName Signup
             * @apiGroup Auth
             *
             * @apiParam {String} email User email address.
             * @apiParam {String} passowrd User unencrypted password.
             * @apiParam {String} nameFirst User first name.
             * @apiParam {String} nameLast User last name.
             * @apiParam {Integer} teamId optional Team to add the new player too.
             *
             * @apiSuccessExample {json} Success-Response:
             *      HTTP/1.1 200: OK
             *      {
             *          "data": {
             *              "registered": true,
             *              "sessionLifeHours": 1
             *              "user": {
             *                  "id": "28",
             *                  "nameFirst": "Rachel",
             *                  "nameLast": "Testing",
             *                  "email": "racheltest@testing.com",
             *                  "displayName": "Rachel",
             *                  "roles": ['3'],
             *                  "apiKey": "caf02551768a09e1aed8946ecacce3b01f253884a08bded1f1a76520b8f0c4e847914a1daea072ab957582a2c32beceacd62b5e6842f18ef2b21a3f13b16c374",
             *                  "apiToken": "c88e7640de8f34c18d7d07d6d0a26b0d9896f188766e445bac32a44cb275ba89"
             *              }
             *          },
             *          "meta": {
             *              "error": false,
             *              "status": 200
             *          }
             *      }
             */
            
            $this->post('/signup', '\API\SignupController:signup');
            
            /* email, nameFirst, nameLast, facebookId, accessToken */

            $this->post('/signup/facebook', '\API\SignupController:facebookSignup');

            //////////////////////////////
            // Signup Controller  ////////
            //////////////////////////////
            
            $this->post('/forgot-password/request-email', '\API\PasswordManagmentController:requestResetEmail');
            
            $this->post('/forgot-password/validate-token', '\API\PasswordManagmentController:validateResetToken');
            
            $this->post('/forgot-password/change-password', '\API\PasswordManagmentController:changeUserPassword');
            
        })->add(new \API\ApiAuthMiddleware($slimApp->getContainer(), 'public'));
    }
}