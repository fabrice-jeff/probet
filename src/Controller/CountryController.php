<?php

namespace App\Controller;

use App\Repository\CountryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[Route('/api')]
#[OA\Tag(name: "Country")]
class CountryController extends AbstractController
{
    public function __construct(private readonly CountryRepository $countryRepository)
    {
    }

    #[Route('/country', name: 'app_country', methods: ['GET'])]
    #[OA\Get(
        summary: 'Cette route permet de récuperer la liste des pays',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Les différents pays',
                content: new OA\JsonContent(
                    example: [
                        [
                            'id' => 1,
                            'name' => 'France',
                            'code' => 'COU-MmwD1LXq-77A66A4',
                            'created_at' => '2023-08-01T12:00:00Z'
                        ],
                        [
                            'id' => 2,
                            'name' => 'Bénin',
                            'code' => 'COU-58tV1kd4-D4374A7',
                            'created_at' => '2023-08-02T14:30:00Z'
                        ]
                    ],
                )
            )
        ]
    )]
    public function index(): JsonResponse
    {
        $countries =  $this->countryRepository->findAll();
        return $this->json( $countries, Response::HTTP_OK, [], [
            'groups' => ['country.index' ,'code', 'created_at']
        ]);
    }
}
