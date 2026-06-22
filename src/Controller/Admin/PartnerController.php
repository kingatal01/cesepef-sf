<?php

namespace App\Controller\Admin;

use App\Entity\Partner;
use App\Service\Uploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/partenaires')]
class PartnerController extends AbstractController
{
    private const MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'];
    private const MAX = 2 * 1024 * 1024;

    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('', name: 'admin_partenaires', methods: ['GET'])]
    public function index(): Response
    {
        $partners = $this->em->getRepository(Partner::class)
            ->findBy([], ['position' => 'ASC', 'name' => 'ASC']);

        return $this->render('admin/partenaires/index.html.twig', ['partners' => $partners]);
    }

    #[Route('/new', name: 'admin_partenaires_new', methods: ['POST'])]
    public function new(Request $request, Uploader $uploader): Response
    {
        if (!$this->isCsrfTokenValid('partenaires', (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('admin_partenaires');
        }
        $partner = new Partner();
        $this->hydrate($partner, $request, $uploader);
        $this->em->persist($partner);
        $this->em->flush();
        $this->addFlash('success', 'Partenaire ajouté.');

        return $this->redirectToRoute('admin_partenaires');
    }

    #[Route('/{id}/edit', name: 'admin_partenaires_edit', methods: ['GET', 'POST'])]
    public function edit(Partner $partner, Request $request, Uploader $uploader): Response
    {
        if ($request->isMethod('POST')) {
            if ($this->isCsrfTokenValid('partenaires', (string) $request->request->get('_token'))) {
                $this->hydrate($partner, $request, $uploader);
                $this->em->flush();
                $this->addFlash('success', 'Partenaire mis à jour.');
            }
            return $this->redirectToRoute('admin_partenaires');
        }

        return $this->render('admin/partenaires/edit.html.twig', ['partner' => $partner]);
    }

    #[Route('/{id}/toggle', name: 'admin_partenaires_toggle', methods: ['POST'])]
    public function toggle(Partner $partner, Request $request): Response
    {
        if ($this->isCsrfTokenValid('partenaires', (string) $request->request->get('_token'))) {
            $partner->setIsActive(!$partner->isActive());
            $this->em->flush();
        }

        return $this->redirectToRoute('admin_partenaires');
    }

    #[Route('/{id}/delete', name: 'admin_partenaires_delete', methods: ['POST'])]
    public function delete(Partner $partner, Request $request): Response
    {
        if ($this->isCsrfTokenValid('partenaires', (string) $request->request->get('_token'))) {
            $this->em->remove($partner);
            $this->em->flush();
            $this->addFlash('success', 'Partenaire supprimé.');
        }

        return $this->redirectToRoute('admin_partenaires');
    }

    private function hydrate(Partner $partner, Request $request, Uploader $uploader): void
    {
        $r = $request->request;
        $partner->setName((string) $r->get('name'));
        $partner->setWebsite($r->get('website') ?: null);
        $partner->setCategory($r->get('category') ?: null);
        $partner->setIsActive((bool) $r->get('isActive', false));
        $partner->setPosition((int) $r->get('position', 0));

        $file = $request->files->get('logo');
        if ($file) {
            $partner->setLogo($uploader->upload($file, 'brands', self::MIMES, self::MAX));
        }
    }
}
