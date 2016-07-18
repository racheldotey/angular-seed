<?php namespace API;

 require_once dirname(__FILE__) . '/login/loginController.php';

class AuthRoutes {
    
    public function addRoutes(\Slim\App $slimApp) {
        
        $slimApp->group('/auth', function () {
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
            $this->get('/login', '\API\AuthLoginController:login');
        });
    }

    private function addLoginRoutes(\Slim\App $slimApp) {
        
    }
}