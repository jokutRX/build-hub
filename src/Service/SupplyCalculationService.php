<?php

namespace App\Service;

use App\Dto\CreateSupplyRequestDto;

class SupplyCalculationService
{
    // Средняя насыпная плотность популярных стройматериалов (т/м³)
    private const DENSITY_MAP = [
        'песок' => 1.6,
        'щебень' => 1.45,
        'бетон' => 2.4,
        'гравий' => 1.4,
        'цемент' => 1.3,
        'раствор' => 1.8,
        'грунт' => 1.5,
    ];

    /**
     * Обработка строительной логистики и вычислений
     */
    public function processLogistics(CreateSupplyRequestDto $dto): array
    {
        $weightInTons = $this->calculateWeightInTons($dto->title, (float)$dto->quantity, $dto->unit);

        // Авто-определение потребности в спецтехнике, если значение явно не передано
        $unloadingValue = $dto->unloadingEquipment;
        if ($unloadingValue === null) {
            $unloadingValue = ($weightInTons >= 3.5) ? 'Да' : 'Нет';
        } elseif (is_bool($unloadingValue)) {
            $unloadingValue = $unloadingValue ? 'Да' : 'Нет';
        }

        // Расчет рекомендуемой техники и количества рейсов
        $recommendedMachinery = 'Малотоннажный транспорт (Газель / ГАЗ)';
        $tripsCount = 1;

        if ($weightInTons > 0) {
            if ($weightInTons > 20) {
                $recommendedMachinery = 'Тяжелый самосвал 25т / Тонар';
                $tripsCount = (int) ceil($weightInTons / 25);
            } elseif ($weightInTons > 10) {
                $recommendedMachinery = 'Самосвал КАМАЗ 20т';
                $tripsCount = (int) ceil($weightInTons / 20);
            } elseif ($weightInTons >= 3.5) {
                $recommendedMachinery = 'Самосвал 10т / МАЗ';
                $tripsCount = (int) ceil($weightInTons / 10);
            }
        }

        return [
            'unloadingEquipment' => (string) $unloadingValue,
            'calculatedWeightTons' => round($weightInTons, 2),
            'calculationResult' => [
                'calculatedAmount' => round($weightInTons, 2),
                'recommendedMachinery' => $recommendedMachinery,
                'tripsCount' => $tripsCount,
                'note' => $weightInTons > 0 
                    ? sprintf('Расчётный вес: ~%.2f тонн. Требуется %d рейс(ов).', $weightInTons, $tripsCount)
                    : 'Штучный груз / стандартная доставка'
            ]
        ];
    }

    /**
     * Валидация временных окон доставки
     */
    public function validateTimeWindow(?string $start, ?string $end): ?string
    {
        if (!$start || !$end) {
            return null;
        }

        if (strtotime($start) >= strtotime($end)) {
            return 'Время начала доставки не может быть позже или равно времени окончания';
        }

        return null;
    }

    /**
     * Перевод любого объема/количества в тонны для логистических расчетов
     */
    private function calculateWeightInTons(string $title, float $quantity, string $unit): float
    {
        $unitNormalized = mb_strtolower(trim($unit));

        if (in_array($unitNormalized, ['т', 'тонн', 'тонна', 'тонны'])) {
            return $quantity;
        }

        if (in_array($unitNormalized, ['кг', 'килограмм', 'килограммов'])) {
            return $quantity / 1000;
        }

        // Если объем в м³ — считаем по плотности
        if (in_array($unitNormalized, ['м3', 'м³', 'куб', 'кубов', 'кубометров'])) {
            $titleNormalized = mb_strtolower($title);
            foreach (self::DENSITY_MAP as $material => $density) {
                if (str_contains($titleNormalized, $material)) {
                    return $quantity * $density;
                }
            }
            return $quantity * 1.5; // Базовый усредненный коэффициент
        }

        return 0.0; // Для штучных товаров (кирпичи, блоки и т.д.)
    }
}