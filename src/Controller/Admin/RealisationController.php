<?php

namespace App\Controller\Admin;

use App\Entity\Realisation;
use App\Service\Uploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/realisations')]
class RealisationController extends AbstractController
{
    private const MIMES = ['image/jpeg', 'image/png', 'image/webp'];
    private const MAX = 3 * 1024 * 1024;

    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('', name: 'admin_realisations', methods: ['GET'])]
    public function index(): Response
    {
        $realisations = $this->em->getRepository(Realisation::class)
            ->findBy([], ['position' => 'ASC', 'year' => 'DESC']);

        return $this->render('admin/realisations/index.html.twig', ['realisations' => $realisations]);
    }

    #[Route('/new', name: 'admin_realisations_new', methods: ['POST'])]
    public function new(Request $request, Uploader $uploader): Response
    {
        if (!$this->isCsrfTokenValid('realisations', (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('admin_realisations');
        }
        $r = new Realisation();
        $this->hydrate($r, $request, $uploader);
        $this->em->persist($r);
        $this->em->flush();
        $this->addFlash('success', 'Réalisation ajoutée.');

        return $this->redirectToRoute('admin_realisations');
    }

    #[Route('/{id}/edit', name: 'admin_realisations_edit', methods: ['GET', 'POST'])]
    public function edit(Realisation $realisation, Request $request, Uploader $uploader): Response
    {
        if ($request->isMethod('POST')) {
            if ($this->isCsrfTokenValid('realisations', (string) $request->request->get('_token'))) {
                $this->hydrate($realisation, $request, $uploader);
                $this->em->flush();
                $this->addFlash('success', 'Réalisation mise à jour.');
            }
            return $this->redirectToRoute('admin_realisations');
        }

        return $this->render('admin/realisations/edit.html.twig', ['realisation' => $realisation]);
    }

    #[Route('/{id}/toggle', name: 'admin_realisations_toggle', methods: ['POST'])]
    public function toggle(Realisation $realisation, Request $request): Response
    {
        if ($this->isCsrfTokenValid('realisations', (string) $request->request->get('_token'))) {
            $realisation->setIsPublished(!$realisation->isPublished());
            $this->em->flush();
        }

        return $this->redirectToRoute('admin_realisations');
    }

    #[Route('/{id}/delete', name: 'admin_realisations_delete', methods: ['POST'])]
    public function delete(Realisation $realisation, Request $request): Response
    {
        if ($this->isCsrfTokenValid('realisations', (string) $request->request->get('_token'))) {
            $this->em->remove($realisation);
            $this->em->flush();
            $this->addFlash('success', 'Réalisation supprimée.');
        }

        return $this->redirectToRoute('admin_realisations');
    }

    private function hydrate(Realisation $r, Request $request, Uploader $uploader): void
    {
        $req = $request->request;
        $r->setTitle((string) $req->get('title'));
        $r->setDescription((string) $req->get('description'));
        $r->setClient($req->get('client') ?: null);
        $r->setSector($req->get('sector') ?: null);
        $r->setCountry($req->get('country') ?: 'Tchad');
        $r->setYear($req->get('year') ? (int) $req->get('year') : null);
        $r->setIsPublished((bool) $req->get('isPublished', false));
        $r->setPosition((int) $req->get('position', 0));

        $tags = array_values(array_filter(array_map('trim', explode(',', (string) $req->get('tags')))));
        $r->setTags($tags ?: null);

        $file = $request->files->get('image');
        if ($file) {
            $r->setImage($uploader->upload($file, 'realisations', self::MIMES, self::MAX));
        }
    }
}
