<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateSupplyRequestDto
{
    #[Assert\NotBlank(message: 'Заголовок обязателен')]
    public string $title;

    #[Assert\NotBlank(message: 'Укажите строительный объект')]
    public string $site;

    #[Assert\NotNull(message: 'Укажите количество')]
    #[Assert\Positive(message: 'Количество должно быть больше 0')]
    public float $quantity;

    #[Assert\NotBlank]
    public string $unit = 'шт';

    #[Assert\NotBlank]
    public string $priority = 'MEDIUM';

    // --- Новые поля логистики ---

    public ?string $deliveryTimeStart = null;

    public ?string $deliveryTimeEnd = null;

    /**
     * Может приходить как boolean (true/false) с Vue, так и null/string
     */
    public mixed $unloadingEquipment = null;
}