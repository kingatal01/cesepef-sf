<?php

namespace App\Command;

use App\Entity\AdminUser;
use App\Entity\Partner;
use App\Entity\Service;
use App\Entity\SiteSetting;
use App\Entity\TeamMember;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:db:seed', description: 'Peuple la base avec les données initiales CESEPEF (idempotent).')]
class SeedCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // ── Admin ────────────────────────────────────────────────
        $repo = $this->em->getRepository(AdminUser::class);
        if (!$repo->findOneBy(['email' => 'admin@cesepef.org'])) {
            $admin = (new AdminUser())
                ->setEmail('admin@cesepef.org')
                ->setName('ADOUMBEYE Amine')
                ->setRoles(['ROLE_ADMIN']);
            $admin->setPassword($this->hasher->hashPassword($admin, 'Cesepef2024!'));
            $this->em->persist($admin);
            $io->writeln('Admin créé : admin@cesepef.org');
        }

        // ── Équipe ───────────────────────────────────────────────
        $team = [
            ['ADOUMBEYE Amine', 'Gérant / Expert principal', 'Direction / Gouvernance', 1],
            ['DEYAM MANDEYE Blandine', 'Associée-cofondatrice', 'Direction / Gouvernance', 2],
            ['NDOFETE Timothée', 'Associé-cofondateur', 'Direction / Gouvernance', 3],
            ['DINGUAMADJI Japhet', 'Associé-cofondateur', 'Direction / Gouvernance', 4],
            ['Honoré AÏGONGUÉ', 'Chef de département', 'Suivi & Évaluation', 5],
            ['ALYO MBAINAIKOU Ghislain', 'Chef de département', 'Études & Recherches', 6],
            ['MBAIADOUM NGARGUINEM Rodrigue', 'Chef de département', 'Formation', 7],
            ['MASTOG Ngarmadji', 'Chef de département', 'Conseil & Projets', 8],
        ];
        $tRepo = $this->em->getRepository(TeamMember::class);
        foreach ($team as [$name, $role, $dept, $pos]) {
            if (!$tRepo->findOneBy(['name' => $name])) {
                $this->em->persist((new TeamMember())
                    ->setName($name)->setRole($role)->setDepartment($dept)->setPosition($pos));
            }
        }

        // ── Services ─────────────────────────────────────────────
        $services = [
            ['Évaluation 360°', "Évaluation indépendante alignée OCDE-CAD, défendable auprès des financeurs et utile pour les décisions de prolongation, de mise à l'échelle ou de réorientation.", "Bailleurs, agences d'exécution, ONG", '8 à 16 semaines', ["Rapport principal d'évaluation", 'Note de synthèse exécutive (8 pages)', 'Base de données nettoyée', 'Atelier de restitution'], false, 1],
            ['Système SERA clé en main', 'Système de suivi évaluation redevabilité apprentissage opérationnel avec indicateurs SMART, outils mobiles, tableaux de bord et équipe formée.', 'Projets en démarrage, ONG', '6 à 12 semaines', ["Manuel SERA & base d'indicateurs", 'Formulaires KoboToolbox configurés', 'Tableau de bord Power BI', "Formation initiale de l'équipe"], true, 2],
            ['Étude de référence (Baseline)', 'Valeurs de départ fiables sur tous les indicateurs clés, servant de référence pour les évaluations futures et la redevabilité auprès des bailleurs.', 'Projets exigeant une mesure ex-ante', '10 à 16 semaines', ['Rapport baseline complet', 'Base de données', "Fiches d'indicateurs", "Matrice d'évaluation"], false, 3],
            ['Étude sectorielle ou de filière', "Compréhension en profondeur d'un secteur ou d'une filière, identification des opportunités et goulots d'étranglement, recommandations stratégiques fondées.", 'Ministères, bailleurs, fondations, entreprises', '10 à 20 semaines', ["Rapport d'étude approfondi", 'Cartographie des acteurs', 'Analyse de la chaîne de valeur', 'Recommandations priorisées'], false, 4],
            ['Formations certifiantes & sur mesure', 'Équipes opérationnellement compétentes, capables de piloter projets et systèmes SERA de manière autonome. Présentiel, en ligne ou hybride.', 'ONG, administrations, agences onusiennes', '2 jours à 4 semaines', ['GAR & cadre logique', 'Collecte mobile de données', 'Analyse statistique R/Stata/SPSS', 'Gestion du cycle de projet'], false, 5],
            ['Assistance technique aux appels à projets', 'Maximiser les chances de remporter un financement grâce à une proposition conforme aux exigences du bailleur, méthodologiquement solide et financièrement réaliste.', 'ONG, consortiums, opérateurs privés', 'Sur délai bailleur', ['Proposition technique complète', 'Proposition financière', 'CVs structurés', 'Annexes méthodologiques'], false, 6],
            ['Diagnostic territorial & développement local', "Diagnostic complet d'un territoire (économie, social, environnement, gouvernance) et plan d'action priorisé pour son développement.", 'Collectivités locales, agences de développement', 'Variable', ['Diagnostic territorial complet', "Plan d'action priorisé", 'Cartographie des ressources', 'Atelier de validation'], false, 7],
        ];
        $sRepo = $this->em->getRepository(Service::class);
        foreach ($services as [$title, $desc, $cible, $delai, $livrables, $highlight, $pos]) {
            if (!$sRepo->findOneBy(['title' => $title])) {
                $this->em->persist((new Service())
                    ->setTitle($title)->setDescription($desc)->setCible($cible)->setDelai($delai)
                    ->setLivrables($livrables)->setHighlight($highlight)->setPosition($pos));
            }
        }

        // ── Partenaires ──────────────────────────────────────────
        $partners = [
            ['Banque Mondiale', 'Multilatéral', 1],
            ['Banque Africaine de Développement', 'Multilatéral', 2],
            ['Union Européenne', 'Bilatéral', 3],
            ['AFD (Agence Française de Développement)', 'Bilatéral', 4],
            ['GIZ', 'Bilatéral', 5],
            ['UNICEF', 'Nations Unies', 6],
            ['PNUD', 'Nations Unies', 7],
            ['PAM', 'Nations Unies', 8],
        ];
        $pRepo = $this->em->getRepository(Partner::class);
        foreach ($partners as [$name, $cat, $pos]) {
            if (!$pRepo->findOneBy(['name' => $name])) {
                $this->em->persist((new Partner())->setName($name)->setCategory($cat)->setPosition($pos));
            }
        }

        // ── Paramètres ───────────────────────────────────────────
        $settings = [
            ['site_name', 'CESEPEF Sarl', 'Nom du site', 'general'],
            ['site_email', 'info@cesepef.org', 'Email principal', 'contact'],
            ['site_phone1', '+235 66 38 64 14', 'Téléphone 1', 'contact'],
            ['site_phone2', '+235 68 82 62 43', 'Téléphone 2', 'contact'],
            ['site_address', "7ᵉ Arrondissement, Quartier Abéna, axe Hôtel Mirande, N'Djamena, Tchad", 'Adresse', 'contact'],
            ['site_nif', '9031479', 'NIF', 'legal'],
            ['site_capital', '1 000 000 FCFA', 'Capital social', 'legal'],
        ];
        $setRepo = $this->em->getRepository(SiteSetting::class);
        foreach ($settings as [$key, $value, $label, $group]) {
            $existing = $setRepo->findOneBy(['settingKey' => $key]);
            if ($existing) {
                $existing->setValue($value);
            } else {
                $this->em->persist((new SiteSetting())
                    ->setSettingKey($key)->setValue($value)->setLabel($label)->setGroupName($group));
            }
        }

        $this->em->flush();
        $io->success('Seed terminé avec succès.');

        return Command::SUCCESS;
    }
}
