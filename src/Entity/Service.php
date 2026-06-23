<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'service')]
#[ORM\HasLifecycleCallbacks]
class Service
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cible = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $delai = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $livrables = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icon = null;

    #[ORM\Column]
    private bool $highlight = false;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private int $position = 0;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getTitle(): ?string
    {
        return $this->title;
    }
    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }
    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }
    public function getCible(): ?string
    {
        return $this->cible;
    }
    public function setCible(?string $cible): static
    {
        $this->cible = $cible;
        return $this;
    }
    public function getDelai(): ?string
    {
        return $this->delai;
    }
    public function setDelai(?string $delai): static
    {
        $this->delai = $delai;
        return $this;
    }
    public function getLivrables(): ?array
    {
        return $this->livrables;
    }
    public function setLivrables(?array $livrables): static
    {
        $this->livrables = $livrables;
        return $this;
    }
    public function getIcon(): ?string
    {
        return $this->icon;
    }
    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }
    public function isHighlight(): bool
    {
        return $this->highlight;
    }
    public function setHighlight(bool $highlight): static
    {
        $this->highlight = $highlight;
        return $this;
    }
    public function isActive(): bool
    {
        return $this->isActive;
    }
    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }
    public function getPosition(): int
    {
        return $this->position;
    }
    public function setPosition(int $position): static
    {
        $this->position = $position;
        return $this;
    }
}
