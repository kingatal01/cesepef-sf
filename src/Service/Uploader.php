<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Upload d'images vers public/images/<sous-dossier>.
 * Reproduit les contraintes du projet Next.js (mime + taille).
 */
class Uploader
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/public')]
        private readonly string $publicDir,
    ) {
    }

    /**
     * @param string[] $allowedMimes
     * @return string Chemin web (ex: /images/team/xxxx.jpg)
     *
     * @throws \RuntimeException si le fichier est invalide
     */
    public function upload(UploadedFile $file, string $subdir, array $allowedMimes, int $maxBytes): string
    {
        if (!in_array($file->getMimeType(), $allowedMimes, true)) {
            throw new \RuntimeException('Format de fichier non autorisé.');
        }
        if ($file->getSize() > $maxBytes) {
            throw new \RuntimeException('Fichier trop volumineux (max ' . round($maxBytes / 1048576, 1) . ' Mo).');
        }

        $ext = $file->guessExtension() ?: 'bin';
        $filename = uniqid('', true) . '-' . bin2hex(random_bytes(3)) . '.' . $ext;
        $targetDir = $this->publicDir . '/images/' . $subdir;

        try {
            $file->move($targetDir, $filename);
        } catch (FileException $e) {
            throw new \RuntimeException('Échec de l\'enregistrement du fichier.', 0, $e);
        }

        return '/images/' . $subdir . '/' . $filename;
    }
}
