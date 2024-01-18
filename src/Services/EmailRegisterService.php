<?php

namespace App\Services;

use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mailer\MailerInterface;

class EmailRegisterService
{
    public function __construct(private UrlGeneratorInterface $urlGeneratorInterface, private MailerInterface $mailer)
    {

    }

    public function sendEmail(string $userEmail, string $tokenValue): void
    {
        if ($userEmail) {
            $linkOnRoute = $this->urlGeneratorInterface->generate('email_confirmation', ['token' => $tokenValue], UrlGeneratorInterface::ABSOLUTE_URL);

            $email = (new Email())
                ->from('kovalenko@app.com')
                ->to($userEmail)
                ->subject('Confirm your email address!')
                ->text('Confirm your email!')
                ->html('<a href="' . $linkOnRoute . '">Click to confirm your email</a>');
            $this->mailer->send($email);
        }
    }
}