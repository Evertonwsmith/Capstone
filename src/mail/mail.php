<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

class mail
{

    private $sender_email, $password, $recipient, $subject, $body, $port, $filename, $cid, $email_type, $email_images;

    public function __construct($sender_email, $password, $email_type = null)
    {
        $this->sender_email = $sender_email;
        $this->password = $password;
        $this->email_type = $email_type;
    }

    public function get_sender_email()
    {
        return $this->sender_email;
    }

    public function get_password()
    {
        return $this->password;
    }

    public function get_recipient()
    {
        return $this->recipient;
    }

    public function get_subject()
    {
        return $this->subject;
    }

    public function get_body()
    {
        return $this->body;
    }

    public function get_port()
    {
        return $this->port;
    }

    public function get_filename()
    {
        return $this->filename;
    }

    public function get_cid()
    {
        return $this->cid;
    }

    public function get_email_type()
    {
        return $this->email_type;
    }

    public function get_email_images()
    {
        return $this->email_images;
    }

    public function set_recipient($recipient)
    {
        $this->recipient = $recipient;
    }

    public function set_subject($subject)
    {
        $this->subject = $subject;
    }

    public function set_body($body)
    {
        $this->body = $body;
    }

    public function set_port($port)
    {
        $this->port = $port;
    }

    public function set_filename($filename)
    {
        $this->filename = $filename;
    }

    public function set_cid($cid)
    {
        $this->cid = $cid;
    }

    public function set_email_images($images)
    {
        $this->email_images = $images;
    }

    public function send_mail($BCCs = null)
    {
        $check = $this->check_inputs();
        if ($check == false) {
            return "Email could not be sent. There are variables that are missing";
        } else {
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->SMTPDebug = 0;
                $mail->SMTPAuth = TRUE;
                $mail->SMTPSecure = "tls";
                $mail->Port = $this->get_port();
                $mail->Host = 'smtp.gmail.com';
                $mail->isHTML(true);
                $mail->Username = $this->get_sender_email();
                $mail->Password = $this->get_password();
                $mail->Mailer = 'smtp';
                $mail->SetFrom('no-reply@capstonesleepovers.com', 'no-reply@capstonesleepovers.com');
                $mail->Subject = $this->get_subject();
                $this->check_email_type($mail);
                if ($this->get_filename() !== null && $this->get_cid() !== null) {
                    $mail->addEmbeddedImage($this->get_filename(), $this->get_cid());
                }
                if (!empty($this->get_email_images())) {
                    foreach ($this->get_email_images() as $cid => $path) {
                        $mail->addEmbeddedImage($path, $cid);
                    }
                }
                $mail->Body = $this->get_body();
                $mail->AddAddress($this->get_recipient());
                if (isset($BCCs)) {
                    foreach ($BCCs as $email => $name) {
                        $mail->addBCC($email, $name);
                    }
                }
                return $mail->Send();
            } catch (Exception $e) {
                return "Error: " . $e->getMessage();
            }
        }
        return "success";
    }

    public function check_inputs()
    {
        if ($this->get_sender_email() == null) {
            return false;
        }
        if ($this->get_password() == null) {
            return false;
        }
        if ($this->get_recipient() == null) {
            return false;
        }
        if ($this->get_subject() == null) {
            return false;
        }
        if ($this->get_body() == null) {
            return false;
        }
        if ($this->get_port() == null) {
            return false;
        }
        return true;
    }

    private function check_email_type($mail)
    {
        switch ($this->get_email_type()) {
            case ("confirm_order"):
                $mail->addEmbeddedImage("../src/mail/mail_templates/Sleepovers-Logo-and-Name-White.jpg", "sleepovers_logo");
                break;
        }
    }
}
