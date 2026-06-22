<?php

namespace App\Controller\Admin;

use App\Entity\Publication;
use App\Service\Uploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/publications')]
class PublicationController extends AbstractController
{
    private const MIMES = ['image/jpeg', 'image/png', 'image/webp'];
    private const MAX = 3 * 1024 * 1024;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SluggerInterface $slugger,
    ) {
    }

    #[Route('', name: 'admin_publications', methods: ['GET'])]
    public function index(): Response
    {
        $publications = $this->em->getRepository(Publication::class)
            ->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/publications/index.html.twig', ['publications' => $publications]);
    }

    #[Route('/new', name: 'admin_publications_new', methods: ['POST'])]
    public function new(Request $request, Uploader $uploader): Response
    {
        if (!$this->isCsrfTokenValid('publications', (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('admin_publications');
        }
        $pub = new Publication();
        $this->hydrate($pub, $request, $uploader);
        $this->em->persist($pub);
        $this->em->flush();
        $this->addFlash('success', 'Publication créée.');

        return $this->redirectToRoute('admin_publications');
    }

    #[Route('/{id}/edit', name: 'admin_publications_edit', methods: ['GET', 'POST'])]
    public function edit(Publication $pub, Request $request, Uploader $uploader): Response
    {
        if ($request->isMethod('POST')) {
            if ($this->isCsrfTokenValid('publications', (string) $request->request->get('_token'))) {
                $this->hydrate($pub, $request, $uploader);
                $this->em->flush();
                $this->addFlash('success', 'Publication mise à jour.');
            }
            return $this->redirectToRoute('admin_publications');
        }

        return $this->render('admin/publications/edit.html.twig', ['publication' => $pub]);
    }

    #[Route('/{id}/toggle', name: 'admin_publications_toggle', methods: ['POST'])]
    public function toggle(Publication $pub, Request $request): Response
    {
        if ($this->isCsrfTokenValid('publications', (string) $request->request->get('_token'))) {
            $pub->setPublished(!$pub->isPublished());
            $pub->setPublishedAt($pub->isPublished() ? ($pub->getPublishedAt() ?? new \DateTimeImmutable()) : null);
            $this->em->flush();
        }

        return $this->redirectToRoute('admin_publications');
    }

    #[Route('/{id}/delete', name: 'admin_publications_delete', methods: ['POST'])]
    public function delete(Publication $pub, Request $request): Response
    {
        if ($this->isCsrfTokenValid('publications', (string) $request->request->get('_token'))) {
            $this->em->remove($pub);
            $this->em->flush();
            $this->addFlash('success', 'Publication supprimée.');
        }

        return $this->redirectToRoute('admin_publications');
    }

    private function hydrate(Publication $pub, Request $request, Uploader $uploader): void
    {
        $r = $request->request;
        $title = (string) $r->get('title');
        $pub->setTitle($title);
        $pub->setExcerpt($r->get('excerpt') ?: null);
        $pub->setContent((string) $r->get('content'));
        $pub->setCategory($r->get('category') ?: null);
        $pub->setAuthor($r->get('author') ?: 'CESEPEF');

        if (!$pub->getSlug()) {
            $pub->setSlug($this->uniqueSlug($title, $pub));
        }

        $tags = array_values(array_filter(array_map('trim', explode(',', (string) $r->get('tags')))));
        $pub->setTags($tags ?: null);

        $wasPublished = $pub->isPublished();
        $published = (bool) $r->get('published', false);
        $pub->setPublished($published);
        if ($published && !$wasPublished && !$pub->getPublishedAt()) {
            $pub->setPublishedAt(new \DateTimeImmutable());
        }
        if (!$published) {
            $pub->setPublishedAt(null);
        }

        $file = $request->files->get('coverImage');
        if ($file) {
            $pub->setCoverImage($uploader->upload($file, 'blog', self::MIMES, self::MAX));
        }
    }

    private function uniqueSlug(string $title, Publication $current): string
    {
        $base = $this->slugger->slug($title)->lower()->toString() ?: 'publication';
        $slug = $base;
        $repo = $this->em->getRepository(Publication::class);
        $i = 1;
        while (true) {
            $existing = $repo->findOneBy(['slug' => $slug]);
            if (!$existing || $existing === $current) {
                return $slug;
            }
            $slug = $base . '-' . (++$i);
        }
    }
}
