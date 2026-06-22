<?php

namespace App\Twig;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('unread_messages', $this->unreadMessages(...)),
        ];
    }

    public function unreadMessages(): int
    {
        return $this->em->getRepository(Message::class)->count(['isRead' => false]);
    }
}
