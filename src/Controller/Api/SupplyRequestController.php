<?php

namespace App\Controller\Api;

use App\Dto\CreateSupplyRequestDto;
use App\Entity\SupplyRequest;
use App\Repository\SupplyRequestRepository;
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
    public function index(SupplyRequestRepository $repository): JsonResponse
    {
        $requests = $repository->findBy([], ['createdAt' => 'DESC']);
        return $this->json($requests);
    }

    #[Route('', methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntityManagerInterface $em
    ): JsonResponse {
        $rawContent = $request->getContent();
        
        // Поддержка фолбеков наименований (если с фронта пришло `object` вместо `site` или `amount` вместо `quantity`)
        $data = json_decode($rawContent, true) ?? [];
        if (isset($data['object']) && !isset($data['site'])) {
            $data['site'] = $data['object'];
        }
        if (isset($data['amount']) && !isset($data['quantity'])) {
            $data['quantity'] = $data['amount'];
        }

        /** @var CreateSupplyRequestDto $dto */
        $dto = $serializer->deserialize(json_encode($data), CreateSupplyRequestDto::class, 'json');

        // Валидация DTO
        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // Маппинг DTO в Entity
        $supplyRequest = new SupplyRequest();
        $supplyRequest->setTitle($dto->title);
        $supplyRequest->setSite($dto->site);
        $supplyRequest->setQuantity((float) $dto->quantity);
        $supplyRequest->setUnit($dto->unit);
        $supplyRequest->setPriority($dto->priority);
        $supplyRequest->setDeliveryTimeStart($dto->deliveryTimeStart);
        $supplyRequest->setDeliveryTimeEnd($dto->deliveryTimeEnd);

        // Безопасная конвертация bool/mixed в string для Entity (где колонка varchar(50))
        if (is_bool($dto->unloadingEquipment)) {
            $unloadingValue = $dto->unloadingEquipment ? 'Да' : 'Нет';
        } elseif (is_string($dto->unloadingEquipment)) {
            $unloadingValue = $dto->unloadingEquipment;
        } else {
            $unloadingValue = null;
        }
        $supplyRequest->setUnloadingEquipment($unloadingValue);

        $em->persist($supplyRequest);
        $em->flush();

        return $this->json($supplyRequest, Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(SupplyRequest $supplyRequest, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($supplyRequest);
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}