<?php

namespace App\Controller\Admin;

use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/services')]
class ServiceController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('', name: 'admin_services', methods: ['GET'])]
    public function index(): Response
    {
        $services = $this->em->getRepository(Service::class)
            ->findBy([], ['position' => 'ASC', 'title' => 'ASC']);

        return $this->render('admin/services/index.html.twig', ['services' => $services]);
    }

    #[Route('/new', name: 'admin_services_new', methods: ['POST'])]
    public function new(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('services', (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('admin_services');
        }
        $service = new Service();
        $this->hydrate($service, $request);
        $this->em->persist($service);
        $this->em->flush();
        $this->addFlash('success', 'Offre ajoutée.');

        return $this->redirectToRoute('admin_services');
    }

    #[Route('/{id}/edit', name: 'admin_services_edit', methods: ['GET', 'POST'])]
    public function edit(Service $service, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            if ($this->isCsrfTokenValid('services', (string) $request->request->get('_token'))) {
                $this->hydrate($service, $request);
                $this->em->flush();
                $this->addFlash('success', 'Offre mise à jour.');
            }
            return $this->redirectToRoute('admin_services');
        }

        return $this->render('admin/services/edit.html.twig', ['service' => $service]);
    }

    #[Route('/{id}/toggle', name: 'admin_services_toggle', methods: ['POST'])]
    public function toggle(Service $service, Request $request): Response
    {
        if ($this->isCsrfTokenValid('services', (string) $request->request->get('_token'))) {
            $service->setIsActive(!$service->isActive());
            $this->em->flush();
        }

        return $this->redirectToRoute('admin_services');
    }

    #[Route('/{id}/delete', name: 'admin_services_delete', methods: ['POST'])]
    public function delete(Service $service, Request $request): Response
    {
        if ($this->isCsrfTokenValid('services', (string) $request->request->get('_token'))) {
            $this->em->remove($service);
            $this->em->flush();
            $this->addFlash('success', 'Offre supprimée.');
        }

        return $this->redirectToRoute('admin_services');
    }

    private function hydrate(Service $service, Request $request): void
    {
        $r = $request->request;
        $service->setTitle((string) $r->get('title'));
        $service->setDescription((string) $r->get('description'));
        $service->setCible($r->get('cible') ?: null);
        $service->setDelai($r->get('delai') ?: null);
        $service->setHighlight((bool) $r->get('highlight', false));
        $service->setIsActive((bool) $r->get('isActive', false));
        $service->setPosition((int) $r->get('position', 0));

        $livrables = array_values(array_filter(array_map(
            'trim',
            preg_split('/\r\n|\r|\n/', (string) $r->get('livrables')) ?: []
        )));
        $service->setLivrables($livrables ?: null);
    }
}
