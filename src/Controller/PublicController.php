<?php

namespace App\Controller;

use App\Entity\Partner;
use App\Entity\Publication;
use App\Entity\Realisation;
use App\Entity\Service;
use App\Entity\TeamMember;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Pages publiques du site vitrine CESEPEF.
 *
 * Phase 1 : les contenus dynamiques (équipe, services, partenaires, blog) sont
 * rendus avec des données en dur dans les templates ; ils seront branchés sur
 * la base de données en Phase 4.
 */
class PublicController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(EntityManagerInterface $em): Response
    {
        return $this->render('home/index.html.twig', [
            'partners' => $em->getRepository(Partner::class)->findBy(['isActive' => true], ['position' => 'ASC', 'name' => 'ASC']),
            'team' => $em->getRepository(TeamMember::class)->findBy(['isActive' => true], ['position' => 'ASC', 'name' => 'ASC']),
            'services' => $em->getRepository(Service::class)->findBy(['isActive' => true], ['position' => 'ASC', 'title' => 'ASC']),
            'articles' => $em->getRepository(Publication::class)->findBy(['published' => true], ['publishedAt' => 'DESC'], 3),
        ]);
    }

    #[Route('/about', name: 'about')]
    public function about(EntityManagerInterface $em): Response
    {
        $members = $em->getRepository(TeamMember::class)
            ->findBy(['isActive' => true], ['position' => 'ASC', 'name' => 'ASC']);

        $gouvernance = [];
        $chefs = [];
        foreach ($members as $m) {
            $dept = mb_strtolower((string) $m->getDepartment());
            $role = mb_strtolower((string) $m->getRole());
            if (str_contains($dept, 'direction') || str_contains($dept, 'gouvernance')) {
                $gouvernance[] = $m;
            } elseif (str_contains($role, 'chef')) {
                $chefs[] = $m;
            }
        }

        return $this->render('about/index.html.twig', [
            'gouvernance' => $gouvernance,
            'chefs' => $chefs,
        ]);
    }

    #[Route('/expertises', name: 'expertises')]
    public function expertises(): Response
    {
        return $this->render('expertises/index.html.twig');
    }

    #[Route('/services', name: 'services')]
    public function services(EntityManagerInterface $em): Response
    {
        return $this->render('services/index.html.twig', [
            'services' => $em->getRepository(Service::class)->findBy(['isActive' => true], ['position' => 'ASC', 'title' => 'ASC']),
        ]);
    }

    #[Route('/realisations', name: 'realisations')]
    public function realisations(EntityManagerInterface $em): Response
    {
        $realisations = $em->getRepository(Realisation::class)
            ->findBy(['isPublished' => true], ['position' => 'ASC', 'year' => 'DESC']);

        $sectors = [];
        foreach ($realisations as $r) {
            if ($r->getSector()) {
                $sectors[$r->getSector()] = true;
            }
        }

        return $this->render('realisations/index.html.twig', [
            'realisations' => $realisations,
            'sectors' => array_keys($sectors),
        ]);
    }

    #[Route('/blog', name: 'blog')]
    public function blog(EntityManagerInterface $em): Response
    {
        return $this->render('blog/index.html.twig', [
            'articles' => $em->getRepository(Publication::class)->findBy(['published' => true], ['publishedAt' => 'DESC']),
        ]);
    }

    #[Route('/blog/{slug}', name: 'blog_detail')]
    public function blogDetail(string $slug, EntityManagerInterface $em): Response
    {
        $repo = $em->getRepository(Publication::class);
        $article = $repo->findOneBy(['slug' => $slug, 'published' => true]);
        if (!$article) {
            throw $this->createNotFoundException('Publication introuvable.');
        }

        $related = [];
        if ($article->getCategory()) {
            $related = $repo->createQueryBuilder('p')
                ->where('p.published = true')
                ->andWhere('p.category = :cat')
                ->andWhere('p.id != :id')
                ->setParameter('cat', $article->getCategory())
                ->setParameter('id', $article->getId())
                ->orderBy('p.publishedAt', 'DESC')
                ->setMaxResults(2)
                ->getQuery()->getResult();
        }

        return $this->render('blog/detail.html.twig', [
            'article' => $article,
            'related' => $related,
        ]);
    }

    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->render('contact/index.html.twig');
    }
}
