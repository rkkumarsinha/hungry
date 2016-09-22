<?php

class Api extends App_REST {
    function init(){
        parent::init();

        $this->dbConnect();
        
        $this->api->pathfinder
            ->addLocation(array(
                'addons' => array('addons', 'vendor', 'shared'),
                'php' => array('addons', 'vendor')
            ))
            ->setBasePath($this->pathfinder->base_location->getPath()."/..")
        ;    

        $this->add('Controller_PatternRouter')
            ->link('v1/book',array('id','method','arg1','arg2'))
            ->route();

        $this->api->today = date('Y-m-d');
        $this->api->time = date("H:i:s");
        $this->api->name = "hungryApi";
    }

    function send($to="techrakesh91@gmail.com",$subject="Here is the subject",$body="This is the HTML message body <b>in bold!{activation_link}</b>"){

        $config = $this->add('Model_Configuration')->tryLoad(1);

        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = $config['host'];  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = $config['username'];                 // SMTP username
        $mail->Password = $config['password'];                 // SMTP password
        $mail->SMTPSecure = $config['smtp_secure'];             // Enable TLS encryption, `ssl` also accepted
        $mail->Port = $config['port']; 

        $mail->setFrom($config['from_email'], 'Mailer');
        $mail->addReplyTo($config['reply_to'], 'Information');

        $mail->addAddress($to, 'Hungrydunia');     // Add a recipient
        // $mail->addAddress('techrakesh91@gmail.com');               // Name is optional
        // $mail->addCC('cc@example.com');
        // $mail->addBCC('bcc@example.com');
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
        
        // echo $body;
        // exit;

        if(!$mail->send()) {
            return false;
            // echo 'Message could not be sent.';
            // echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            return true;
            // echo 'Message has been sent';
        }
    }
}