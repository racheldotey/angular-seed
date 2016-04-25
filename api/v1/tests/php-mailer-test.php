<?php namespace API\Test;

require_once dirname(dirname(__FILE__)) . "/vendor/autoload.php";   // Composer components

/**
 * This example shows settings to use when sending via Google"s Gmail servers.
 */


//Create a new PHPMailer instance
$mail = new \PHPMailer;
//Tell PHPMailer to use SMTP
$mail->isSMTP();
//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
$mail->SMTPDebug = 2;
//Ask for HTML-friendly debug output
$mail->Debugoutput = "html";



//Set the hostname of the mail server
$mail->Host = "mail.triviaculture.com";

// use
// $mail->Host = gethostbyname("smtp.gmail.com");
// if your network does not support SMTP over IPv6
//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
$mail->Port = 587;
//Set the encryption system to use - ssl (deprecated) or tls
$mail->SMTPSecure = "tls";
//Whether to use SMTP authentication
$mail->SMTPAuth = true;
//Username to use for SMTP authentication - use full email address for gmail
$mail->Username = "communications@triviajoint.com";
//Password to use for SMTP authentication
$mail->Password = "Tr1v1@#1";
//Set who the message is to be sent from
$mail->setFrom("communications@triviajoint.com", "TriviaJoint.com");
//Set an alternative reply-to address
$mail->addReplyTo("communications@triviajoint.com", "TrivaJoint Communications");
//Set who the message is to be sent to
$mail->addAddress("james.morgenstein@vistrada.com", "James TrivaJoint-Email-Test");
$mail->addAddress("rachel.dotey@gmail.com", "Rachel TrivaJoint-Email-Test");
$mail->addAddress("rachellcarbone@gmail.com", "Rachel TrivaJoint-Email-Test");
//Set the subject line


$now = date('l jS \of F Y h:i:s A');

$mail->Subject = "PHPMailer TriviaJoint.com SMTP test {$now}";
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$mail->msgHTML("<h1>If you can read this the test worked!</h1><h2>{$now}</h2><p>This is a <b>html-text</b> message body.</p>");
//Replace the plain text body with one created manually
$mail->AltBody = "If you can read this the test worked! This is a plain-text message body. Now = {$now}";

if(!$mail->smtpConnect()) {
    echo 'Could not connect!!!!!!!!!!!';
} else {
    echo 'Connected!!!!!!!!!!!';
}

//send the message, check for errors
if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message sent!";
}