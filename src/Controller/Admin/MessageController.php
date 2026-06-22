<?php

namespace App\Controller\Admin;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/messages')]
class MessageController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('', name: 'admin_messages', methods: ['GET'])]
    public function index(): Response
    {
        $messages = $this->em->getRepository(Message::class)
            ->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/messages/index.html.twig', ['messages' => $messages]);
    }

    #[Route('/read-all', name: 'admin_messages_read_all', methods: ['POST'])]
    public function readAll(Request $request): Response
    {
        if ($this->isCsrfTokenValid('messages', (string) $request->request->get('_token'))) {
            foreach ($this->em->getRepository(Message::class)->findBy(['isRead' => false]) as $m) {
                $m->setIsRead(true);
            }
            $this->em->flush();
        }

        return $this->redirectToRoute('admin_messages');
    }

    #[Route('/{id}/read', name: 'admin_messages_read', methods: ['POST'])]
    public function read(Message $message, Request $request): Response
    {
        if ($this->isCsrfTokenValid('messages', (string) $request->request->get('_token'))) {
            $message->setIsRead(!$message->isRead());
            $this->em->flush();
        }

        return $this->redirectToRoute('admin_messages');
    }

    #[Route('/{id}/star', name: 'admin_messages_star', methods: ['POST'])]
    public function star(Message $message, Request $request): Response
    {
        if ($this->isCsrfTokenValid('messages', (string) $request->request->get('_token'))) {
            $message->setIsStarred(!$message->isStarred());
            $this->em->flush();
        }

        return $this->redirectToRoute('admin_messages');
    }

    #[Route('/{id}/delete', name: 'admin_messages_delete', methods: ['POST'])]
    public function delete(Message $message, Request $request): Response
    {
        if ($this->isCsrfTokenValid('messages', (string) $request->request->get('_token'))) {
            $this->em->remove($message);
            $this->em->flush();
            $this->addFlash('success', 'Message supprimé.');
        }

        return $this->redirectToRoute('admin_messages');
    }
}
