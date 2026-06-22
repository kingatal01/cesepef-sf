<?php

namespace App\Controller\Admin;

use App\Entity\TeamMember;
use App\Service\Uploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/equipe')]
class EquipeController extends AbstractController
{
    private const MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    private const MAX = 2 * 1024 * 1024;

    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('', name: 'admin_equipe', methods: ['GET'])]
    public function index(): Response
    {
        $members = $this->em->getRepository(TeamMember::class)
            ->findBy([], ['position' => 'ASC', 'name' => 'ASC']);

        return $this->render('admin/equipe/index.html.twig', ['members' => $members]);
    }

    #[Route('/new', name: 'admin_equipe_new', methods: ['POST'])]
    public function new(Request $request, Uploader $uploader): Response
    {
        if (!$this->isCsrfTokenValid('equipe', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('admin_equipe');
        }

        $member = new TeamMember();
        $this->hydrate($member, $request, $uploader);
        $this->em->persist($member);
        $this->em->flush();
        $this->addFlash('success', 'Membre ajouté.');

        return $this->redirectToRoute('admin_equipe');
    }

    #[Route('/{id}/edit', name: 'admin_equipe_edit', methods: ['GET', 'POST'])]
    public function edit(TeamMember $member, Request $request, Uploader $uploader): Response
    {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('equipe', (string) $request->request->get('_token'))) {
                $this->addFlash('error', 'Jeton CSRF invalide.');
                return $this->redirectToRoute('admin_equipe_edit', ['id' => $member->getId()]);
            }
            $this->hydrate($member, $request, $uploader);
            $this->em->flush();
            $this->addFlash('success', 'Membre mis à jour.');

            return $this->redirectToRoute('admin_equipe');
        }

        return $this->render('admin/equipe/edit.html.twig', ['member' => $member]);
    }

    #[Route('/{id}/toggle', name: 'admin_equipe_toggle', methods: ['POST'])]
    public function toggle(TeamMember $member, Request $request): Response
    {
        if ($this->isCsrfTokenValid('equipe', (string) $request->request->get('_token'))) {
            $member->setIsActive(!$member->isActive());
            $this->em->flush();
        }

        return $this->redirectToRoute('admin_equipe');
    }

    #[Route('/{id}/delete', name: 'admin_equipe_delete', methods: ['POST'])]
    public function delete(TeamMember $member, Request $request): Response
    {
        if ($this->isCsrfTokenValid('equipe', (string) $request->request->get('_token'))) {
            $this->em->remove($member);
            $this->em->flush();
            $this->addFlash('success', 'Membre supprimé.');
        }

        return $this->redirectToRoute('admin_equipe');
    }

    private function hydrate(TeamMember $member, Request $request, Uploader $uploader): void
    {
        $r = $request->request;
        $member->setName((string) $r->get('name'));
        $member->setRole((string) $r->get('role'));
        $member->setDepartment($r->get('department') ?: null);
        $member->setEmail($r->get('email') ?: null);
        $member->setLinkedin($r->get('linkedin') ?: null);
        $member->setBio($r->get('bio') ?: null);
        $member->setPosition((int) $r->get('position', 0));
        $member->setIsActive((bool) $r->get('isActive', false));

        $file = $request->files->get('photo');
        if ($file) {
            $member->setPhoto($uploader->upload($file, 'team', self::MIMES, self::MAX));
        }
    }
}
