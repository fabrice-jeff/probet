<?php

namespace App\Services;

use App\Entity\Actor;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class NotificationServices
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function notifyAccountCreation(Actor $actor): void
    {
        $user = $actor->getUser();
        $email = (new TemplatedEmail())
            ->from(new Address('admin@provbet.com', 'Provbet'))
            ->to($user->getEmail())
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Création de compte')
            ->htmlTemplate('mail/accountCreation.html.twig')
            ->context([
                'lastName' => $actor->getLastName(),
                'firstName' => $actor->getFirstName(),
                'mail' => $user->getEmail(),
            ]);
        $this->mailer->send($email);
    }

    public function notifyForgetPassword(Actor $actor, String $code): void
    {
        $user = $actor->getUser();
        $email = (new TemplatedEmail())
            ->from(new Address('admin@provbet.com', 'Provbet'))
            ->to($user->getEmail())
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Réinitialisation de mot de passe')
            ->htmlTemplate('mail/forget_password.html.twig')
            ->context([
                'verification_code' => $code,
                'mail' => $user->getEmail(),
            ]);
        $this->mailer->send($email);
    }

}