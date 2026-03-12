<?php

namespace App\Controller;

use App\Repository\ActorRepository;
use App\Repository\BetRepository;
use App\Repository\StatusRepository;
use App\Repository\TypeTypeRepository;
use App\Repository\UserRepository;
use App\Request\BalanceRequest;
use App\Utils\Constants\AppValuesConstants;
use App\Utils\Constants\FixedValuesConstants;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
#[Route('/api')]
#[OA\Tag(name: "Balance Sheet")]
class BilanController extends AbstractController
{
    private UserRepository $userRepository;
    private BetRepository $betRepository;
    private StatusRepository $statusRepository;
    private TypeTypeRepository $typeTypeRepository;
    private ActorRepository $actorRepository;
    public function __construct(UserRepository $userRepository, BetRepository $betRepository, StatusRepository $statusRepository, TypeTypeRepository $typeTypeRepository, ActorRepository $actorRepository)
    {
        $this->userRepository = $userRepository;
        $this->betRepository = $betRepository;
        $this->statusRepository = $statusRepository;
        $this->typeTypeRepository = $typeTypeRepository;
        $this->actorRepository = $actorRepository;
    }
    #[Route('/user/balance_sheet_general', name: 'balance_sheet_general', methods: ['GET'])]
    #[OA\Get(
        summary: 'Cette route permet de récupérer le bilan sur les actvités de la plateforme',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Bilan sur les activités de la plateforme',
                content: new OA\JsonContent(
                    example: [
                    ],
                )
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: 'Utilisateur non authentifié',
                content: new OA\JsonContent(
                    example: ['message' => 'Non authentifié']
                )
            )
        ]
    )]
    public function balanceSheetGeneral(): JsonResponse
    {
        if($this->getUser()->getRoles()[0] !== AppValuesConstants::ROLE_SUPER_ADMIN && $this->getUser()->getRoles()[0] !== AppValuesConstants::ROLE_ASSISTANT_ADMIN){
            return new JsonResponse([
                'message' => "Vous n'avez pas le rôle réquis pour annoncer une boule"
            ], Response::HTTP_FORBIDDEN);
        }
        $userArray = [];
        $marchantArray = [];
        $assistantArray = [];
        $users = $this->userRepository->findBy(['deleted' => false]);
        foreach ($users as $value){
            if($value->getRoles()[0] !== AppValuesConstants::ROLE_SUPER_ADMIN){
                $userArray[] = $value;
            }
            if($value->getRoles()[0] === AppValuesConstants::ROLE_USER_MERCHANT){
                $marchantArray[]= $value;
            }
            if($value->getRoles()[0] === AppValuesConstants::ROLE_ASSISTANT_ADMIN){
                $assistantArray[]= $value;
            }
        }
        $bilan = [
            'nombre_abonnes' => count($userArray),
            'nombre_marchant' => count($marchantArray),
            'nombre_assistant' => count($assistantArray)
        ];

        return $this->json($bilan, Response::HTTP_OK, [], [
            'groups' => ['user.index','code' , 'actor.index', 'country.index']
        ]);
    }

    #[Route('/user/balance_user', name: 'balance_bet_user', methods: ['GET'])]
    #[OA\Get(
        summary: 'Cette route permet de récupérer le statistique sur le niveau des paris des joueur',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Statistique sur le pari des joueurs',
                content: new OA\JsonContent(
                    example: [
                    ],
                )
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: 'Utilisateur non authentifié',
                content: new OA\JsonContent(
                    example: ['message' => 'Non authentifié']
                )
            )
        ]
    )]
    public function balanceBetUser(): JsonResponse
    {
        if($this->getUser()->getRoles()[0] !== AppValuesConstants::ROLE_SUPER_ADMIN && $this->getUser()->getRoles()[0] !== AppValuesConstants::ROLE_ASSISTANT_ADMIN){
            return new JsonResponse([
                'message' => "Vous n'avez pas le rôle réquis pour annoncer une boule"
            ], Response::HTTP_FORBIDDEN);
        }

        if($this->getUser()->getRoles()[0] !== AppValuesConstants::ROLE_SUPER_ADMIN) {
            return new JsonResponse([
                'message' => 'Vous n\'avez pas le rôle réquis',
            ], Response::HTTP_FORBIDDEN);
        }
        $bets = $this->betRepository->findBetByPeriod(FixedValuesConstants::TYPE_PERIOD_MONTH);
        $betCountsByActor = [];
        foreach ($bets as $bet) {
            $actor = $this->actorRepository->findOneBy(['user' => $bet->getInsertBy(), 'deleted' =>false]);
            $lastName =  $actor->getLastName();
            $firstName = $actor->getFirstName();
            if (!isset($betCountsByActor["$lastName $firstName"])) {
                $betCountsByActor["$lastName $firstName"] = [
                    'identifier' => $actor->getIdentifier(),
                    'number_bet' => 0
                ];
            }
            $betCountsByActor["$lastName $firstName"]['number_bet']++;
        }

        return $this->json($betCountsByActor, Response::HTTP_OK, [], [
            'groups' => ['user.index','code' , 'actor.index', 'country.index']
        ]);
    }




    #[Route('/user/balance_sheet_by_period', name: 'balance_sheet_by_period', methods: ['POST'])]
    #[OA\Post(
        summary: 'Cette route permet de récupérer le bilan sur les paris en fonction de la période' ,
        requestBody: new OA\RequestBody(
            description: 'Corps de la requete',
            content: new OA\JsonContent(
                example: [
                    'period' => 'string',
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Bilan sur les paris en fonction de la période',
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
    public function balanceSheetByPeriod(BalanceRequest $request): JsonResponse
    {
        if($this->getUser()->getRoles()[0] !== AppValuesConstants::ROLE_SUPER_ADMIN && $this->getUser()->getRoles()[0] !== AppValuesConstants::ROLE_ASSISTANT_ADMIN){
            return new JsonResponse([
                'message' => "Vous n'avez pas le rôle réquis pour annoncer une boule"
            ], Response::HTTP_FORBIDDEN);
        }
        $typePeriod = $this->typeTypeRepository->findOneBy(['reference' => $request->period]);
        if($this->getUser()->getRoles()[0] !== AppValuesConstants::ROLE_SUPER_ADMIN) {
            return new JsonResponse([
                'message' => 'Vous n\'avez pas le rôle réquis',
            ], Response::HTTP_FORBIDDEN);
        }
        if($typePeriod == null) {
            return new JsonResponse([
                'message' => 'Cette période n\'existe pas',
            ], Response::HTTP_FORBIDDEN);
        }
        $statusBetWin = $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_BET_WIN]);
        $statusBetLost = $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_BET_LOST]);
        $bets = $this->betRepository->findBetByPeriod($typePeriod->getReference(), );
        $numberBetWinArray = [];
        $numberBetLostArray = [];
        $amount = 0;
        $amountWin = 0;
        foreach ($bets as $value){
            $amount += $value->getAmount();
            if($value->getStatus() == $statusBetWin){
                $amountWin += $value->getAmountWon();
                $numberBetWinArray[] = $value;
            }
            if($value->getStatus() == $statusBetLost){
                $numberBetLostArray[] = $value;
            }
        }
        $turnover = $amount ;
        $profit = $turnover - $amountWin;
        $bilan = [
            'number_bets' => count($bets),
            'number_bet_win' => count($numberBetWinArray),
            'number_bet_lost' => count($numberBetLostArray),
            'turnover' =>  $turnover,
            'profit' =>  $profit,
        ];
        return $this->json($bilan, Response::HTTP_OK, [], [
            'groups' => ['user.index','code' , 'actor.index', 'country.index']
        ]);
    }
}
