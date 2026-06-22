<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Partner;
use App\Entity\Publication;
use App\Entity\Realisation;
use App\Entity\Service;
use App\Entity\TeamMember;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Back-office CESEPEF.
 *
 * Phase 2 : écrans statiques (données d'exemple dans les templates).
 * L'authentification (Phase 5) et le branchement CRUD/BD (Phase 6) viendront ensuite.
 */
#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('', name: 'admin_root')]
    public function root(): RedirectResponse
    {
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(EntityManagerInterface $em): Response
    {
        return $this->render('admin/dashboard/index.html.twig', [
            'publicationsCount' => $em->getRepository(Publication::class)->count([]),
            'membresActifs' => $em->getRepository(TeamMember::class)->count(['isActive' => true]),
            'messagesCount' => $em->getRepository(Message::class)->count([]),
            'messagesNonLus' => $em->getRepository(Message::class)->count(['isRead' => false]),
            'servicesActifs' => $em->getRepository(Service::class)->count(['isActive' => true]),
            'realisationsCount' => $em->getRepository(Realisation::class)->count(['isPublished' => true]),
        ]);
    }

}
