<?php

namespace App\Entity;

use App\Repository\SupplyRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity(repositoryClass: SupplyRequestRepository::class)]
class SupplyRequest implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $site = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $quantity = null;

    #[ORM\Column(length: 50)]
    private ?string $unit = null;

    #[ORM\Column(length: 20)]
    private ?string $priority = null;

    // --- НОВЫЕ ПОЛЯ ЛОГИСТИКИ И РАЗГРУЗКИ ---

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $deliveryTimeStart = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $deliveryTimeEnd = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $unloadingEquipment = null;

    // ----------------------------------------

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }

    public function getSite(): ?string { return $this->site; }
    public function setSite(string $site): static { $this->site = $site; return $this; }

    public function getQuantity(): ?float { return $this->quantity; }
    public function setQuantity(float $quantity): static { $this->quantity = $quantity; return $this; }

    public function getUnit(): ?string { return $this->unit; }
    public function setUnit(string $unit): static { $this->unit = $unit; return $this; }

    public function getPriority(): ?string { return $this->priority; }
    public function setPriority(string $priority): static { $this->priority = $priority; return $this; }

    // --- ГЕТТЕРЫ И СЕТТЕРЫ ДЛЯ ЛОГИСТИКИ ---

    public function getDeliveryTimeStart(): ?string { return $this->deliveryTimeStart; }
    public function setDeliveryTimeStart(?string $deliveryTimeStart): static { $this->deliveryTimeStart = $deliveryTimeStart; return $this; }

    public function getDeliveryTimeEnd(): ?string { return $this->deliveryTimeEnd; }
    public function setDeliveryTimeEnd(?string $deliveryTimeEnd): static { $this->deliveryTimeEnd = $deliveryTimeEnd; return $this; }

    public function getUnloadingEquipment(): ?string { return $this->unloadingEquipment; }
    public function setUnloadingEquipment(?string $unloadingEquipment): static { $this->unloadingEquipment = $unloadingEquipment; return $this; }

    // ----------------------------------------

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    /**
     * Сериализация объекта в JSON для фронтенда
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'site' => $this->site,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'priority' => $this->priority,
            'deliveryTimeStart' => $this->deliveryTimeStart,
            'deliveryTimeEnd' => $this->deliveryTimeEnd,
            'unloadingEquipment' => $this->unloadingEquipment,
            'createdAt' => $this->createdAt?->format(\DateTimeInterface::ATOM),
        ];
    }
}