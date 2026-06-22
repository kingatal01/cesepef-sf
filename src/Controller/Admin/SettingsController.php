<?php

namespace App\Controller\Admin;

use App\Entity\AdminUser;
use App\Entity\SiteSetting;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin/parametres')]
class SettingsController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('', name: 'admin_parametres', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/parametres/index.html.twig', [
            'settings' => $this->settingsMap(),
        ]);
    }

    #[Route('/site', name: 'admin_parametres_site', methods: ['POST'])]
    public function saveSite(Request $request): Response
    {
        if ($this->isCsrfTokenValid('parametres', (string) $request->request->get('_token'))) {
            $repo = $this->em->getRepository(SiteSetting::class);
            foreach ($repo->findAll() as $setting) {
                $val = $request->request->get($setting->getSettingKey());
                if ($val !== null) {
                    $setting->setValue((string) $val);
                }
            }
            $this->em->flush();
            $this->addFlash('success', 'Paramètres enregistrés.');
        }

        return $this->redirectToRoute('admin_parametres');
    }

    #[Route('/compte', name: 'admin_parametres_compte', methods: ['POST'])]
    public function saveAccount(Request $request, UserPasswordHasherInterface $hasher): Response
    {
        if ($this->isCsrfTokenValid('parametres', (string) $request->request->get('_token'))) {
            /** @var AdminUser $user */
            $user = $this->getUser();
            $user->setName((string) $request->request->get('name'));
            $user->setEmail((string) $request->request->get('email'));
            $password = (string) $request->request->get('password');
            if ($password !== '') {
                $user->setPassword($hasher->hashPassword($user, $password));
            }
            $this->em->flush();
            $this->addFlash('success', 'Compte mis à jour.');
        }

        return $this->redirectToRoute('admin_parametres');
    }

    /** @return array<string,string> */
    private function settingsMap(): array
    {
        $map = [];
        foreach ($this->em->getRepository(SiteSetting::class)->findAll() as $s) {
            $map[$s->getSettingKey()] = $s->getValue();
        }

        return $map;
    }
}
