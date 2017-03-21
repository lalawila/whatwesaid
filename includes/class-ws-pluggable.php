<?php
class WS_Pluggable {
    public static function ws_mail( $to, $subject, $message, $headers = '', $attachments =array()) {
        $phpmailer = new PHPMailer(true);

        $phpmailer->isSMTP();
        $phpmailer->Host = 'hwsmtp.exmail.qq.com';
        $phpmailer->Port = 465;
        $phpmailer->SMTPAuth = true;
        $phpmailer->SMTPSecure = 'ssl';
        $phpmailer->Username = 'hello@whatwesaid.xyz';
        $phpmailer->Password = 'Lq931110';

        $phpmailer->setFrom('hello@whatwesaid.xyz', 'whatwesaid');
        $phpmailer->addAddress('251875969@qq.com', 'Joe User');

        $phpmailer->Subject = 'Here is the subject';
        $phpmailer->Body = 'This is the HTML message body <b>in bold!</b>';
        $phpmailer->AltBody = 'This is the body in plain text for non-HTML mail clients';

        if (!$phpmailer->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $phpmailer->ErrorInfo;
        } else {
            echo 'Message has been sent';
        }


    }
}
