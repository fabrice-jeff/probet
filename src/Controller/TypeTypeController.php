<?php

namespace App\Controller;

use App\Repository\TypeBetRepository;
use App\Repository\TypeTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[Route('/api')]
#[OA\Tag(name: "Type")]
class TypeTypeController extends AbstractController
{
    private TypeBetRepository $typeBetRepository;
    private TypeTypeRepository $typeTypeRepository;

    public function __construct(TypeBetRepository $typeBetRepository, TypeTypeRepository $typeTypeRepository)
    {
        $this->typeBetRepository = $typeBetRepository;
        $this->typeTypeRepository = $typeTypeRepository;
    }

    #[Route('/type_bet', name: 'type_bet', methods: ['GET'])]
    #[OA\Get(
        summary: 'Cette route permet d\'obtenir les différents types de paris',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Les types de paris d\'un tirage',
                content: new OA\JsonContent(
                    example: [

                    ],
                )
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: 'Utilisateur non authentifié',
                content: new OA\JsonContent(
                    example: ['message' => 'Vous n\'est pas autorisé à faire cette action']
                )
            )
        ]
    )]
    public function findtypeBet(): JsonResponse
    {
        $typeBets = $this->typeBetRepository->findBy(["deleted" =>false]);
        return $this->json($typeBets, Response::HTTP_OK, [], [
            'groups' => ['type_bet.index' ,'code', 'created_at']
        ]);
    }

    #[Route('/type_transaction', name: 'type_transaction', methods: ['GET'])]
    #[OA\Get(
        summary: 'Cette route permet d\'obtenir les différents types de transaction',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Les types de paris d\'un tirage',
                content: new OA\JsonContent(
                    example: [

                    ],
                )
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: 'Utilisateur non authentifié',
                content: new OA\JsonContent(
                    example: ['message' => 'Vous n\'est pas autorisé à faire cette action']
                )
            )
        ]
    )]
    public function findTypeTransaction(): JsonResponse
    {
        $typeBets = $this->typeTypeRepository->findAll();
        return $this->json($typeBets, Response::HTTP_OK, [], [
            'groups' => ['type_type.index' ,'code', 'created_at']
        ]);
    }

}
