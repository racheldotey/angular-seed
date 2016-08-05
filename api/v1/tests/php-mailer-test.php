<?php namespace API;

require_once dirname(dirname(__FILE__)) . "/vendor/autoload.php";   // Composer components
require_once dirname(dirname(__FILE__)) . "/services/SystemVariables.php";
require_once dirname(dirname(__FILE__)) . "/services/ApiLogging.php";
require_once dirname(dirname(__FILE__)) . "/services/EmailService.php";
require_once dirname(dirname(__FILE__)) . "/services/ApiConfig.php";
require_once dirname(dirname(__FILE__)) . "/services/ApiDBConn.php";

$ApiConfig = new \API\ApiConfig();
$ApiLogging = new ApiLogging($ApiConfig, 'api_testing');
$ApiDBConn = new ApiDBConn($ApiConfig, $ApiLogging);
$SystemVars = new SystemVariables($ApiDBConn, $ApiLogging);
$EmailService = new EmailService($SystemVars, $ApiLogging);

date_default_timezone_set('America/New_York');
$now = date('l jS \of F Y h:i:s A');

$websiteTitle = ($SystemVars->get('WEBSITE_TITLE')) ? $SystemVars->get('WEBSITE_TITLE') : 'Angular Seed';
$websiteUrl = ($SystemVars->get('WEBSITE_URL')) ? $SystemVars->get('WEBSITE_URL') : 'http://www.angular-seed.dev';

$data = array(
    'recipientName' => 'System Admin',
    'recipientEmail' => $ApiConfig->get('systemAdminEmail'),
    'fromName' => 'System Admin',
    'fromEmail' => $ApiConfig->get('systemAdminEmail'),
    'subject' => "Email Test for {$websiteTitle}",
    'htmlBody' => "<p>This is a test email sent from {$websiteUrl}.</p><p>The time is now: {$now}.</p><p>Have a nice day,<br/>- System Admin</p>"
);
$result = $EmailService->sendEmail($data);

print_r($result);