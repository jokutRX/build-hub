<?php

namespace App\Controller\Api;

use App\Entity\SupplyRequest;
use App\Repository\SupplyRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['error' => 'Некорректный формат JSON'], Response::HTTP_BAD_REQUEST);
        }

        // Поддерживаем ключи и со стороны Symfony (site, quantity), и со стороны Vue (object, amount)
        $title    = $data['title'] ?? null;
        $site     = $data['site'] ?? $data['object'] ?? null;
        $quantity = $data['quantity'] ?? $data['amount'] ?? null;

        // Проверка заполнения обязательных полей
        if (empty($title) || empty($site) || empty($quantity)) {
            return $this->json([
                'error' => 'Заполните обязательные поля (наименование, объект и количество)'
            ], Response::HTTP_BAD_REQUEST);
        }

        $supplyRequest = new SupplyRequest();
        $supplyRequest->setTitle($title);
        $supplyRequest->setSite($site);
        $supplyRequest->setQuantity((float) $quantity);
        $supplyRequest->setUnit($data['unit'] ?? 'шт');
        $supplyRequest->setPriority($data['priority'] ?? 'MEDIUM');

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