<?php

namespace App\Controller;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact/envoi', name: 'contact_submit', methods: ['POST'])]
    public function submit(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('contact', (string) $request->request->get('_token'))) {
            $this->addFlash('contact_error', 'Session expirée, merci de réessayer.');
            return $this->redirectToRoute('contact');
        }

        $r = $request->request;
        $name = trim((string) $r->get('name'));
        $email = trim((string) $r->get('email'));
        $message = trim((string) $r->get('message'));

        if (!$r->get('rgpd')) {
            $this->addFlash('contact_error', 'Veuillez accepter la politique de confidentialité.');
            return $this->redirectToRoute('contact');
        }
        if ($name === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('contact_error', 'Merci de renseigner un nom, un email valide et un message.');
            return $this->redirectToRoute('contact');
        }

        $msg = (new Message())
            ->setName($name)
            ->setEmail($email)
            ->setPhone($r->get('phone') ?: null)
            ->setSubject($r->get('subject') ?: null)
            ->setService($r->get('service') ?: null)
            ->setMessage($message);
        $em->persist($msg);
        $em->flush();

        $this->addFlash('contact_success', 'Merci pour votre message. Notre équipe vous répondra sous 48 heures ouvrées.');

        return $this->redirectToRoute('contact');
    }
}
