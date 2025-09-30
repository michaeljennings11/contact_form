<?php

session_start();

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader (created by composer, not included with PHPMailer)
require 'vendor/autoload.php';

//Import email config variables
require './email-config.php';

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST["name"]);
    $email = htmlspecialchars($_POST["email"]);
    $subject = htmlspecialchars($_POST["subject"]);
    $message = htmlspecialchars($_POST["message"]);

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo "Must fill out all fields.";
        header("Location: {$_SERVER["HTTP_REFERER"]}");  // redirect to previous page
        exit(0);
    }

    $body = "Name: $name <br>"."email: $email <br>"." Message: $message";

    try {
        //Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = $mailHost;                     //Set the SMTP server to send through (e.g. 'smtp.gmail.com)
        //$mail->Host       = 'smtp.example.com';                     //Set the SMTP server to send through (e.g. 'smtp.gmail.com)
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = $mailUsername;                     //SMTP username
        //$mail->Username   = 'user@example.com';                     //SMTP username
        $mail->Password   = $mailPassword;                               //SMTP password
        //$mail->Password   = 'secret';                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom($mailFromAddress, $mailFromName);
        //$mail->setFrom('from@example.com', 'Mailer');
        $mail->addAddress($mailRecipientAddress, $mailRecipientName);     //Add a recipient
        //$mail->addAddress('ellen@example.com');               //Name is optional
        $mail->addReplyTo($email, $name);
        //$mail->addCC('cc@example.com');
        //$mail->addBCC('bcc@example.com');

        //Attachments
        //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = "Inquiry from {$name}";
        $mail->Body    = $body;
        //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        if ($mail->send()) {
            $_SESSION['status'] =  'Thank you for contacting!';
            header("Location: {$_SERVER["HTTP_REFERER"]}");  // redirect to previous page
            exit(0);
        } else {
            $_SESSION['status'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }



} else {
    header("Location: {$_SERVER["HTTP_REFERER"]}");  // redirect to previous page
}
