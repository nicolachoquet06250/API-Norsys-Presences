<?php

namespace DI\wrappers;

use DI\decorators\Injectable;
use PHPMailer\PHPMailer\{
	SMTP, PHPMailer
};

#[Injectable]
class Mailer {
	public function send(array $to, string $body, string $object, ...$attachments) {
		if (!defined('EMAIL_HOST') || !defined('EMAIL_PORT') || !defined('EMAIL')|| !defined('EMAIL_PASSWORD') || !defined('EMAIL_NAME')) {
			return true;
		}

		$mail = new PHPMailer();  // Cree un nouvel objet PHPMailer
		$mail->IsSMTP(); // active SMTP
		$mail->isHTML(true);
		$mail->SMTPDebug = SMTP::DEBUG_OFF;  // debogage: 1 = Erreurs et messages, 2 = messages seulement
		$mail->SMTPAuth = true;  // Authentification SMTP active
		$mail->SMTPSecure = false; // Gmail REQUIERT Le transfert securise
		if (defined('EMAIL_ENCRIPTION') && constant('EMAIL_ENCRIPTION')) {
			$mail->SMTPSecure = constant('EMAIL_ENCRIPTION');
		}
		$mail->Host = constant('EMAIL_HOST');
		$mail->Port = constant('EMAIL_PORT');
		$mail->Username = constant('EMAIL');
		$mail->Password = constant('EMAIL_PASSWORD');
		$mail->SetFrom($mail->Username, constant('EMAIL_NAME'));
		$mail->Subject = $object;
		$mail->Body = $body;
		if (is_string($to)) {
			$mail->AddAddress($to);
		} elseif (is_array($to)) {
			foreach ($to as $email) {
				$mail->AddAddress($email);
			}
		}
		$mail->CharSet = 'UTF-8';

		foreach ($attachments as $attachment) {
			$mail->addAttachment($attachment);
		}

		if(!$mail->Send()) {
			return 'Mail error: '.$mail->ErrorInfo;
		} else {
			return true;
		}
	}
}