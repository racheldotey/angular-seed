<?php namespace API;

/* @author  Rachel L Carbone <hello@rachellcarbone.com> */

class EmailService {
    
    /*
     * System Logger Instance
     */
    private $SystemVariables;
    
    /*
     * Database Connection Instance
     */
    private $DBConn;
    
    /*
     * Database Table Prefix String
     */
    private $DBConnPrefix;
    
    /*
     * System Logger Instance
     */
    private $ApiLogging;
    
    /*
     * Email Log File Name String
     */
    private $logFileName;

    public function __construct(\Interop\Container\ContainerInterface $slimContainer) {
        $this->SystemVariables = $slimContainer->get('SystemVariables');

        $this->DBConn = $slimContainer->get('ApiDBConn');
        $this->DBConnPrefix = $this->DBConn->prefix();

        $this->ApiLogging = $slimContainer->get('ApiLogging');
        $this->logFileName = 'mailer_log';
    }

    public function getPhpMailer() {        
        // Setup mailer for sending message
        $mail = new \PHPMailer;
        $mail->isSMTP();
        $mail->isHTML(true);
        
        $mailerSettings = array(
            // Enable SMTP debugging
            // 0 = off (for production use)
            // 1 = client messages
            // 2 = client and server messages
            "SMTP_SMTP_DEBUG" => 'SMTPDebug',
            //Ask for HTML-friendly debug output
            "SMTP_DEBUGOUTPUT" => "Debugoutput",
            // Set the encryption system to use - ssl (deprecated) or tls
            "SMTP_SECURE" => "SMTPSecure",
            // Whether to use SMTP authentication
            "SMTP_AUTH" => "SMTPAuth",
            // Set the hostname of the mail server
            "SMTP_SERVER_HOST" => "Host",
            // Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
            "SMTP_SERVER_PORT" => "Port",
            // Username to use for SMTP authentication - use full email address for gmail
            "SMTP_SERVER_USERNAME" => "Username",
            // Password to use for SMTP authentication
            "SMTP_SERVER_PASSWORD" => "Password"
        );
        
        $success = true;
        foreach ($mailerSettings as $name => $value) {
            $var = $this->SystemVariables->get($name);
            if ($var && $var->disabled != 1) {
                $mail->{$value} = $var->value;
            } else {
                $this->ApiLogging->write(LOG_WARNING, "ERROR RETRIEVING SMTP SETTING <{$name}>", 'error', $this->logFileName);
                $success = false;
            }
        }
        
        return ($success) ? $mail : false;
    }
    
    public function sendEmailFromTemplate($templateId, $recipientEmail, $recipientName = '', $bodyParams = [], $subjectParams = []) {
        if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            return array('error' => true, 'msg' => "The following email address is invalid: '{$recipientEmail}'.");
        }
        
        // Setup mailer for sending message
        $mail = $this->getPhpMailer();
        
        // Add Recipient - include name if it was sent
        if(!$recipientName || $recipientName === '') {
            $mail->addAddress($recipientEmail);
        } else {
            $mail->addAddress($recipientEmail, $recipientName);
        }

        // Retrieve template
        $emailTemplate = $this->selectEmailTemplate($templateId);
        if (!$emailTemplate) {
            $this->ApiLogging->write(LOG_ERR, "ERROR RETRIEVING EMAIL TEMPLATE, templateId <{$templateId}>", 'error', $this->logFileName);
            return array('error' => true, 'msg' => "Error generating email <{$templateId}>");
        }

        // If a from email is set
        if($emailTemplate->fromEmail) {
            $mail->setFrom($emailTemplate->fromEmail, $emailTemplate->fromName);
        }
        
        // If a reply to email is set
        if($emailTemplate->replyEmail) {
            $mail->setFrom($emailTemplate->replyEmail, $emailTemplate->replyName);
        }
        
        // Substitute parameters into template   
        $mail->Subject = $this->replaceTemplateVariables($emailTemplate->subject, $subjectParams);
        $mail->Body = $this->replaceTemplateVariables($emailTemplate->bodyHtml, $bodyParams);
        $mail->AltBody = $this->replaceTemplateVariables($emailTemplate->bodyPlain, $bodyParams);
        
        if ($mail->send()) {
            return (!$recipientName || $recipientName === '') ? array('error' => false, 'msg' => "Success! Email Sent to \"{$recipientEmail}\"") :
                    array('error' => false, 'msg' => "Success! Email Sent to \"{$recipientEmail}, {$recipientName}\"");
        } else {
            // log the error
            $this->ApiLogging->write(LOG_ERR, "EMAIL FAILURE\nError <{$mail->ErrorInfo}>/n Template Id:<{$templateId}> Sender: <{$emailTemplate->replyEmail}, {$emailTemplate->replyName}> Recipient: <{$recipientEmail}, {$recipientName}> Subject: <{$subject}> Body: <{$bodyPlain}>", 'error', $this->logFileName);
            return (!$recipientName || $recipientName === '') ? array('error' => true, 'msg' => "Unknown Error: Error sending email to \"{$recipientEmail}, {$recipientName}\"") : 
                array('error' => true, 'msg' => "Unknown Error: Error sending email to \"{$recipientEmail}, {$recipientName}\"");
        }
    }

    private function replaceTemplateVariables($templateText, $variableArray) {
        // Template substitution is for parms named @EMAIL@, @FIRST_NAME@, etc     
        foreach($variableArray AS $key => $value) {
            $templateText = str_replace($key, $value, $templateText);
        }
        return $templateText;
    }
    
    public function selectEmailTemplate($templateId) {
        $template = $this->DBConn->selectOne("SELECT id, identifier, from_email AS fromEmail, from_name AS fromName, "
                . "reply_email AS replyEmail, reply_name AS replyName, subject, body_html AS bodyHtml, body_plain AS bodyPlain "
                . "FROM {$this->DBConnPrefix}email_templates WHERE identifier = :identifier LIMIT 1;", 
                array(':identifier' => $templateId));
        if (!$template) {
            $this->ApiLogging->write(LOG_ERR, "ERROR RETRIEVING EMAIL TEMPLATE, templateId <{$templateId}>", 'error', $this->logFileName);
            return false;
        }
        return $template;
    }

    private function log($text) {

    }
}