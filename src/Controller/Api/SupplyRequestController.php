<?php

namespace App\Controller\Api;

use App\Dto\CreateSupplyRequestDto;
use App\Entity\SupplyRequest;
use App\Repository\SupplyRequestRepository;
use App\Service\SupplyCalculationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/requests')]
class SupplyRequestController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(Request $request, SupplyRequestRepository $repository): JsonResponse
    {
        $dateParam = $request->query->get('date');

        if ($dateParam) {
            try {
                $date = new \DateTimeImmutable($dateParam);
                $requests = $repository->findByDate($date);
            } catch (\Exception $e) {
                // В случае невалидной даты отдаем последние 100 записей
                $requests = $repository->findBy([], ['createdAt' => 'DESC'], 100);
            }
        } else {
            // Ограничиваем выборку 100 записями по умолчанию
            $requests = $repository->findBy([], ['createdAt' => 'DESC'], 100);
        }

        return $this->json($requests);
    }

    #[Route('', methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntityManagerInterface $em,
        SupplyCalculationService $calculationService
    ): JsonResponse {
        $rawContent = $request->getContent();
        
        // Поддержка фолбеков наименований
        $data = json_decode($rawContent, true) ?? [];
        if (isset($data['object']) && !isset($data['site'])) {
            $data['site'] = $data['object'];
        }
        if (isset($data['amount']) && !isset($data['quantity'])) {
            $data['quantity'] = $data['amount'];
        }

        /** @var CreateSupplyRequestDto $dto */
        $dto = $serializer->deserialize(json_encode($data), CreateSupplyRequestDto::class, 'json');

        // 1. Стандартная валидация DTO по аннотациям/атрибутам
        $errors = $validator->validate($dto);
        $errorMessages = [];

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
        }

        // 2. Строительная валидация временного окна доставки
        $timeError = $calculationService->validateTimeWindow($dto->deliveryTimeStart, $dto->deliveryTimeEnd);
        if ($timeError) {
            $errorMessages['deliveryTimeEnd'] = $timeError;
        }

        // Если есть хоть одна ошибка — отдаем 400
        if (!empty($errorMessages)) {
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // 3. Вычисление логистических параметров
        $logistics = $calculationService->processLogistics($dto);

        // 4. Маппинг DTO в Entity
        $supplyRequest = new SupplyRequest();
        $supplyRequest->setTitle($dto->title);
        $supplyRequest->setSite($dto->site);
        $supplyRequest->setQuantity((float) $dto->quantity);
        $supplyRequest->setUnit($dto->unit);
        $supplyRequest->setPriority($dto->priority);
        $supplyRequest->setDeliveryTimeStart($dto->deliveryTimeStart);
        $supplyRequest->setDeliveryTimeEnd($dto->deliveryTimeEnd);
        $supplyRequest->setUnloadingEquipment($logistics['unloadingEquipment']);
        $supplyRequest->setCalculationResult($logistics['calculationResult'] ?? null);

        $em->persist($supplyRequest);
        $em->flush();

        return $this->json($supplyRequest, Response::HTTP_CREATED);
    }

    #[Route('/bulk-status', methods: ['POST'])]
    public function bulkStatus(
        Request $request,
        SupplyRequestRepository $repository,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];
        $ids = $data['ids'] ?? [];
        $status = $data['status'] ?? null;

        if (empty($ids) || !$status) {
            return $this->json(['error' => 'Параметры ids и status обязательны'], Response::HTTP_BAD_REQUEST);
        }

        $requests = $repository->findBy(['id' => $ids]);
        foreach ($requests as $supplyRequest) {
            $supplyRequest->setStatus($status);
        }

        $em->flush();

        return $this->json(['success' => true, 'updatedCount' => count($requests)]);
    }

    #[Route('/merge-trip', methods: ['POST'])]
    public function mergeTrip(
        Request $request,
        SupplyRequestRepository $repository,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];
        $ids = $data['ids'] ?? [];

        if (empty($ids)) {
            return $this->json(['error' => 'Массив ids не может быть пустым'], Response::HTTP_BAD_REQUEST);
        }

        $requests = $repository->findBy(['id' => $ids]);
        
        // Генерация простого ID рейса (в реальном проекте может быть отдельной сущностью Trip)
        $tripId = time();

        foreach ($requests as $supplyRequest) {
            $supplyRequest->setTripId($tripId);
            $supplyRequest->setStatus('IN_TRIP');
        }

        $em->flush();

        return $this->json(['success' => true, 'tripId' => $tripId, 'mergedCount' => count($requests)]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(SupplyRequest $supplyRequest, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($supplyRequest);
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}