<?php

namespace App\Controller;

use App\Entity\BallDrawn;
use App\Entity\Bet;
use App\Entity\CoupleDrawn;
use App\Entity\Draw;
use App\Repository\ActorRepository;
use App\Repository\BallDrawnRepository;
use App\Repository\BallRepository;
use App\Repository\BetRepository;
use App\Repository\CountryRepository;
use App\Repository\CoupleDrawnRepository;
use App\Repository\DrawRepository;
use App\Repository\GameRepository;
use App\Repository\StatusRepository;
use App\Repository\TypeBetRepository;
use App\Request\PlayRequest;
use App\Services\AppServices;
use App\Utils\Constants\AppValuesConstants;
use App\Utils\Constants\FixedValuesConstants;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[Route('/api')]
#[OA\Tag(name: "Play")]
class PlayController extends AbstractController
{
    private BallRepository $ballRepository;
    private ActorRepository $actorRepository;
    private EntityManagerInterface $manager;
    private StatusRepository $statusRepository;
    private GameRepository $gameRepository;
    private DrawRepository $drawRepository;
    private BetRepository $betRepository;
    private BallDrawnRepository $ballDrawnRepository;
    private TypeBetRepository $typeBetRepository;
    private AppServices $appServices;
    private CoupleDrawnRepository $coupleDrawnRepository;
    private CountryRepository $countryRepository;

    public function __construct(
        BallRepository $ballRepository,
        ActorRepository $actorRepository,
        EntityManagerInterface $manager,
        StatusRepository $statusRepository,
        GameRepository $gameRepository,
        DrawRepository $drawRepository,
        BetRepository $betRepository,
        BallDrawnRepository $ballDrawnRepository,
        TypeBetRepository $typeBetRepository,
        AppServices $appServices,
        CoupleDrawnRepository $coupleDrawnRepository,
        CountryRepository $countryRepository
    ) {
        $this->ballRepository = $ballRepository;
        $this->actorRepository = $actorRepository;
        $this->manager = $manager;
        $this->statusRepository = $statusRepository;
        $this->gameRepository = $gameRepository;
        $this->drawRepository = $drawRepository;
        $this->betRepository = $betRepository;
        $this->ballDrawnRepository = $ballDrawnRepository;
        $this->typeBetRepository = $typeBetRepository;
        $this->appServices = $appServices;
        $this->coupleDrawnRepository = $coupleDrawnRepository;
        $this->countryRepository = $countryRepository;
    }

    #[Route('/draw/history', name: 'history_draw', methods: ['GET'])]
    #[OA\Get(
        summary: 'Cette route permet d\'obtenir l\'historique des tirages validés',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Les paris d\'un tirage',
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
    public function findHistoryDraw(): JsonResponse
    {
        $user = $this->getUser();
        $actor = $this->actorRepository->findOneBy(['deleted' => false, 'user' => $user]);
        $status = $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_VALIDATED]);
        $draws = null;
        if ($user->getRoles()[0] == AppValuesConstants::ROLE_SUPER_ADMIN || $user->getRoles()[0] == AppValuesConstants::ROLE_ASSISTANT_ADMIN) {
            $draws = $this->drawRepository->findBy(['deleted' => false, 'status' => $status,]);

        } else {
            $draws = $this->drawRepository->findBy(['deleted' => false, 'status' => $status, 'actor' => $actor]);
        }
        $drawsArray = [];
        foreach ($draws as $draw) {
            $betArray = [];
            $bets = $this->betRepository->findBy(['deleted' => false, 'draw' => $draw]);
            foreach ($bets as $bet) {
                $balls = [];
                if (
                    $bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_PERM2
                    || $bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_PERM3
                ) {
                    $coupleDrawns = $this->coupleDrawnRepository->findBy(['deleted' => false, 'bet' => $bet]);
                    foreach ($coupleDrawns as $coupleDrawn) {
                        $ballsCouple = json_decode($coupleDrawn->getBalls());
                        foreach ($ballsCouple as $value) {
                            if (!in_array($value, $balls)) {
                                $balls[] = $value;
                            }
                        }
                    }
                } else {
                    $ballsDrawns = $this->ballDrawnRepository->findBy(['deleted' => false, 'bet' => $bet]);
                    foreach ($ballsDrawns as $ballDrawn) {
                        $balls[] = $ballDrawn->getBall()->getId();
                    }
                }
                $betArray[] = [
                    'amount' => $bet->getAmount(),
                    'potentials_gains' => $bet->getGains(),
                    'typeBet' => $bet->getTypeBet(),
                    'createdAt' => $bet->getCreatedAt(),
                    'balls' => $balls,
                ];
            }
            $drawsArray[] = [
                'draw' => $draw,
                'bets' => $betArray,
            ];
        }
        return $this->json($drawsArray, Response::HTTP_OK, [], [
            'groups' => ['ball_drawn.index', 'ball.index', 'draw.index', 'bet.index', 'code', 'created_at', 'type_bet.index', 'actor.index', 'status.index']
        ]);
    }

    #[Route('/user/draw/result/history', name: 'user_history_draw_result', methods: ['GET'])]
    #[OA\Get(
        summary: 'Cette route permet d\'obtenir l\'historique des résultats de tirages(gagné ou perdu de l\'utilisateur connecté',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Les résultats de paris',
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
    public function historyDrawResult(): JsonResponse
    {
        $user = $this->getUser();
        $actor = $this->actorRepository->findOneBy(['user' => $user, 'deleted' => false]);
        $status = $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_VALIDATED]);
        $draws = $this->drawRepository->findBy(['deleted' => false, 'status' => $status, 'actor' => $actor]);
        $drawsArray = [];
        foreach ($draws as $draw) {
            $betArray = [];
            $bets = $this->betRepository->findBetPlay($draw);
            foreach ($bets as $bet) {
                $balls = [];
                if (
                    $bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_PERM2
                    || $bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_PERM3
                ) {
                    $coupleDrawns = $this->coupleDrawnRepository->findBy(['deleted' => false, 'bet' => $bet]);
                    foreach ($coupleDrawns as $coupleDrawn) {
                        $ballsCouple = json_decode($coupleDrawn->getBalls());
                        foreach ($ballsCouple as $value) {
                            if (!in_array($value, $balls)) {
                                $balls[] = $value;
                            }
                        }
                    }
                } else {
                    $ballsDrawns = $this->ballDrawnRepository->findBy(['deleted' => false, 'bet' => $bet]);
                    foreach ($ballsDrawns as $ballDrawn) {
                        $balls[] = $ballDrawn->getBall()->getId();
                    }
                }
                $betArray[] = [
                    'amount' => $bet->getAmount(),
                    'potentials_gains' => $bet->getGains(),
                    'typeBet' => $bet->getTypeBet(),
                    'createdAt' => $bet->getCreatedAt(),
                    'balls' => $balls,
                    'gain' => $bet->getAmountWon(),
                    'status' => $bet->getStatus()->getName(),
                ];
            }
            $drawsArray[] = [
                'draw' => $draw,
                'bets' => $betArray,
            ];
        }
        return $this->json($drawsArray, Response::HTTP_OK, [], [
            'groups' => ['ball_drawn.index', 'ball.index', 'draw.index', 'bet.index', 'code', 'created_at', 'type_bet.index', 'status.index']
        ]);
    }


    #[Route('/user/draw/{number}', name: 'draw', methods: ['GET'])]
    #[OA\Get(
        summary: 'Cette route permet d\'obtenir les paris d\'un ticket',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Les paris d\'un ticket',
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
    public function findBetOfDraw(string $number): JsonResponse
    {
        $user = $this->getUser();
        $actor = $this->actorRepository->findOneBy(['deleted' => false, 'user' => $user]);
        $draw = $this->drawRepository->findOneBy(['number' => $number, 'deleted' => false]);
        if (!$draw) {
            return new JsonResponse([
                'message' => "Aucun tirage ne correspond à cet numéro"
            ], Response::HTTP_FORBIDDEN);
        }
        $betArray = [];
        $bets = $this->betRepository->findBy(['deleted' => false, 'draw' => $draw]);
        foreach ($bets as $bet) {
            $balls = [];
            if (
                $bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_PERM2
                || $bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_PERM3
            ) {
                $coupleDrawns = $this->coupleDrawnRepository->findBy(['deleted' => false, 'bet' => $bet]);
                foreach ($coupleDrawns as $coupleDrawn) {
                    $ballsCouple = json_decode($coupleDrawn->getBalls());
                    foreach ($ballsCouple as $value) {
                        if (!in_array($value, $balls)) {
                            $balls[] = $value;
                        }
                    }
                }
            } else {
                $ballsDrawns = $this->ballDrawnRepository->findBy(['deleted' => false, 'bet' => $bet]);
                foreach ($ballsDrawns as $ballDrawn) {
                    $balls[] = $ballDrawn->getBall()->getId();
                }
            }
            $betArray[] = [
                'amount' => $bet->getAmount(),
                'potentials_gains' => $bet->getGains(),
                'typeBet' => $bet->getTypeBet(),
                'createdAt' => $bet->getCreatedAt(),
                'balls' => $balls,
                
            ];
        }
        $results = [
            'draw' => $draw,
            'bets' => $betArray,
        ];
        return $this->json($results, Response::HTTP_OK, [], [
            'groups' => ['ball_drawn.index', 'ball.index', 'draw.index', 'bet.index', 'code', 'created_at', 'type_bet.index', 'status.index']
        ]);
    }

    /**
     * @throws \Exception
     */
    #[Route('/user/play', name: 'play', methods: 'POST')]
    #[OA\Post(
        summary: 'Cette route permet de faire un tirage de boule',
        requestBody: new OA\RequestBody(
            description: 'Corps de la requete',
            content: new OA\JsonContent(
                example: [
                    'balls' => 'string',
                    'amount' => 'string',
                    'type_bet' => 'string',
                    'number_draw' => 'string',
                    'double_chance' => false,
                    'country' => 'string'
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Tirage de boule pour un jeu',
                content: new OA\JsonContent(
                    example: [
                        'balls' => 'string',
                        'amount' => 'string',
                        'type_bet' => 'string',
                        'number_draw' => 'string',
                        'double_chance' => false,
                        'country' => 'string'
                    ]
                )
            ),
        ]
    )
    ]
    public function play(PlayRequest $request): JsonResponse
    {
        $user = $this->getUser();
        $actor = $this->actorRepository->findOneBy(['deleted' => false, 'user' => $user]);
        $roleRequirement = false;
        foreach ($user->getRoles() as $role) {
            if ($role === AppValuesConstants::ROLE_USER) {
                $roleRequirement = true;
            }
        }
        if (!$roleRequirement) {
            return new JsonResponse([
                'message' => "Vous n'avez pas le rôle réquis pour tirer"
            ], Response::HTTP_FORBIDDEN);
        }
        $balls = json_decode($request->balls);
        $ballsDrawns = [];
        $ballsNumbers = [];
        foreach ($balls as $value) {
            $ball = $this->ballRepository->find($value);
            if (!$ball) {
                return new JsonResponse([
                    'message' => "Le numéro de boules choisir n'est pas correct"
                ], Response::HTTP_FORBIDDEN);
            }
            $ballsDrawns[] = $ball;
            $ballsNumbers[] = $value;
        }

        $typeBet = $this->typeBetRepository->findOneBy(['deleted' => false, 'reference' => $request->typeBet]);
        if (!$typeBet) {
            return new JsonResponse([
                'message' => "Ce type de pari n'existe pas dans la base de donnée"
            ], Response::HTTP_FORBIDDEN);
        }

        if ($typeBet->getReference() == FixedValuesConstants::TYPE_BET_NAP1_BLOQUE || $typeBet->getReference() == FixedValuesConstants::TYPE_BET_NAP1_POTO) {
            if (count($ballsDrawns) != 1) {
                return new JsonResponse([
                    'message' => "Le nombre de boule total pour ce type de pari n'est pas valide"
                ], Response::HTTP_FORBIDDEN);
            }
        }
        if ($typeBet->getReference() == FixedValuesConstants::TYPE_BET_NAP2) {
            if (count($ballsDrawns) != 2) {
                return new JsonResponse([
                    'message' => "Le nombre de boule total pour ce type de pari n'est pas valide"
                ], Response::HTTP_FORBIDDEN);
            }
        }
        if ($typeBet->getReference() == FixedValuesConstants::TYPE_BET_NAP3) {
            if (count($ballsDrawns) != 3) {
                return new JsonResponse([
                    'message' => "Le nombre de boule total pour ce type de pari n'est pas valide"
                ], Response::HTTP_FORBIDDEN);
            }
        }

        if ($typeBet->getReference() == FixedValuesConstants::TYPE_BET_NAP4) {
            if (count($ballsDrawns) != 4) {
                return new JsonResponse([
                    'message' => "Le nombre de boule total pour ce type de pari n'est pas valide"
                ], Response::HTTP_FORBIDDEN);
            }
        }
        if ($typeBet->getReference() == FixedValuesConstants::TYPE_BET_NAP5) {
            if (count($ballsDrawns) != 5) {
                return new JsonResponse([
                    'message' => "Le nombre de boule total pour ce type de pari n'est pas valide"
                ], Response::HTTP_FORBIDDEN);
            }
        }
        if ($typeBet->getReference() == FixedValuesConstants::TYPE_BET_TCHIGAN) {
            if (count($ballsDrawns) != 5) {
                return new JsonResponse([
                    'message' => "Le nombre de boule total pour ce type de pari n'est pas valide"
                ], Response::HTTP_FORBIDDEN);
            }
        }
        if ($typeBet->getReference() == FixedValuesConstants::TYPE_BET_PERM2) {
            if (count($ballsDrawns) < 3 || count($ballsDrawns) > 20) {
                return new JsonResponse([
                    'message' => "Le nombre de boule total pour ce type de pari n'est pas valide"
                ], Response::HTTP_FORBIDDEN);
            }
        }
        if ($typeBet->getReference() == FixedValuesConstants::TYPE_BET_PERM3) {
            if (count($ballsDrawns) < 4 || count($ballsDrawns) > 20) {
                return new JsonResponse([
                    'message' => "Le nombre de boule total pour ce type de pari n'est pas valide"
                ], Response::HTTP_FORBIDDEN);
            }
        }

        $now = new \DateTime('now');
        $now->modify('+1 hour'); 
        $date = $now->format('Y-m-d');
        $currentHour = $now->format('H:i');
        $dateTime = "$date $currentHour";
        $country = $this->countryRepository->findOneBy(['code' => $request->country]);
        $benin = $this->countryRepository->findOneBy(['code' => AppValuesConstants::CODE_BENIN]);
        $togo = $this->countryRepository->findOneBy(['code' => AppValuesConstants::CODE_TOGO]);
        $draw = $this->drawRepository->findOneBy(['deleted' => false, 'number' => $request->numberDraw]);
        $bet = new Bet();
        $drawNew = null;
        /**
         * Vérifier le pays qui organise le jeu et 
         * Vérifier l'intervalle d'heure dans laquelle on est(Le tirage de 09h)
         */
        if ($togo === $country && $dateTime < "$date 09:00") {
            $bet->setDoubleChance($request->doubleChance);
        }

        if (!$draw) {
            $drawNew = new Draw();
            $drawNew->setActor($actor);
            // // Pour le togo 
            // if($country === $togo)
            // {
            //     if ($dateTime < "$date 09:00" && $country === $togo) {
            //         $status = $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_NINE]);
            //         $game = $this->gameRepository->findOneGameOfDay($now->modify('-1 day'),$status, $country);
            //         $drawNew->setGame($game);
            //     }
            //     else if($dateTime > "$date 09:10"  && $dateTime < "$date 13:00" ){
            //         $status = $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_THIRTEEN]);
            //         $game = $this->gameRepository->findOneGameOfDay($now,$status, $country);
            //         $drawNew->setGame($game);
            //     }
            //     else if( $dateTime > "$date 13:10"  && $dateTime < "$date 18:00"){
            //         $status = $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_EIGHTEEN]);
            //         $game = $this->gameRepository->findOneGameOfDay($now,$status, $country);
            //         $drawNew->setGame($game);
            //     }
            //     else if($dateTime > "$date 18:10"  && $dateTime < "$date 23:00"){
            //         $status = $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_NINE]);
            //         $game = $this->gameRepository->findOneGameOfDay($now,$status, $country);
            //         $drawNew->setGame($game);
            //     }
            // }
            // else if ($country === $benin){
            if ($dateTime >= "$date 00:00" && $dateTime <= "$date 10:55") {
                $status = $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_ELEVEN]);
                $game = $this->gameRepository->findOneGameOfDay($now, $status, $benin);
                $drawNew->setGame($game);
            } else if ($dateTime >= "$date 11:00" && $dateTime <= "$date 13:55") {
                $status = $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_FOURTEEN]);
                $game = $this->gameRepository->findOneGameOfDay($now, $status, $benin);
                $drawNew->setGame($game);
            } else if ($dateTime >= "$date 14:00" && $dateTime <= "$date 17:55") {
                $status = $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_EIGHTEEN]);
                $game = $this->gameRepository->findOneGameOfDay($now, $status, $benin);
                $drawNew->setGame($game);
            } else if ($dateTime >= "$date 18:00" && $dateTime <= "$date 20:55") {
                $status = $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_TWENTY_ONE]);
                $game = $this->gameRepository->findOneGameOfDay($now, $status, $benin);
                $drawNew->setGame($game);
            } else if ($dateTime >= "$date 21:00" && $dateTime <= "$date 23:55") {
                $status = $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_TWENTY_TREE]);
                $game = $this->gameRepository->findOneGameOfDay($now, $status, $benin);
                $drawNew->setGame($game);
            }
            // }

            $drawNew->setNumber($request->numberDraw);
            $drawNew->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_INCOMPLETE]));
            $this->manager->persist($drawNew);
            $bet->setDraw($drawNew);
        } else {

            if ($actor->getId() != $draw->getActor()->getId()) {
                return new JsonResponse([
                    'message' => "Vous n'êtes pas autorisé à ajouté des paris à ce tirage!!!"
                ], Response::HTTP_FORBIDDEN);
            }
            if ($draw->getStatus()->getName() == FixedValuesConstants::STATUS_GAME_VALIDATED) {
                return new JsonResponse([
                    'message' => "Vous ne pouvez plus ajouté d'autre pari à ce ticket"
                ], Response::HTTP_FORBIDDEN);
            }
            $dateTime = new \DateTime("now");
            $draw->setUpdatedAt($dateTime);
            $this->manager->persist($draw);
            $bet->setDraw($draw);
        }


        /*
         * Vérifier le type de pari
         * Si c'est permutation alors, il faut enregistré des couples de boules
         * Il faut récupérer les différents types de couples avec l'algoriythme correspondant
         * Sinon, il faut enregistré des  boules
         * */
        if ($typeBet->getReference() == FixedValuesConstants::TYPE_BET_PERM2) {
            $results = $this->appServices->combinationsTwo($ballsNumbers);
            /*
             * Vérifier le montant de pari
             * */
            $amount = (double) $request->amount;
            $amountMin = count($results) * 10;
            if ($amount < $amountMin) {
                return new JsonResponse([
                    'message' => "Impossible de parier. Le montant de pari est inférieur au montant minimal autorié"
                ], Response::HTTP_FORBIDDEN);
            }

            $bet->setAmount($request->amount);
            $bet->setTypeBet($typeBet);
            $this->manager->persist($bet);

            foreach ($results as $value) {
                $balls = json_encode([$value[0], $value[1]]);
                $coupleDrawn = new CoupleDrawn();
                $coupleDrawn->setBet($bet);
                $coupleDrawn->setBalls($balls);
                $coupleDrawn->setAmount($amount / count($results));
                $this->manager->persist($coupleDrawn);
            }
        } else if ($typeBet->getReference() == FixedValuesConstants::TYPE_BET_PERM3) {
            $results = $this->appServices->combinationsThree($ballsNumbers);
            /*
             * Vérifier le montant de pari
             * */
            $amount = (double) $request->amount;
            $amountMin = count($results) * 10;
            if ($amount < $amountMin) {
                return new JsonResponse([
                    'message' => "Impossible de parier. Le montant de pari est inférieur au montant minimal autorié"
                ], Response::HTTP_FORBIDDEN);
            }
            $bet->setAmount((double) $request->amount);
            $bet->setTypeBet($typeBet);
            $this->manager->persist($bet);
            foreach ($results as $value) {
                $balls = json_encode([$value[0], $value[1], $value[2]]);
                $coupleDrawn = new CoupleDrawn();
                $coupleDrawn->setBet($bet);
                $coupleDrawn->setBalls($balls);
                $coupleDrawn->setAmount($amount / count($results));
                $this->manager->persist($coupleDrawn);
            }
        } else {

            if (
                ($typeBet->getReference() == FixedValuesConstants::TYPE_BET_NAP1_BLOQUE
                    || $typeBet->getReference() == FixedValuesConstants::TYPE_BET_NAP1_POTO)
                && (double) $request->amount < 100
            ) {
                return new JsonResponse([
                    'message' => "Impossible de parier. Le montant de pari est inférieur au montant minimal autorié"
                ], Response::HTTP_FORBIDDEN);
            }
            if ($typeBet->getReference() == FixedValuesConstants::TYPE_BET_TCHIGAN && (double) $request->amount < 310) {
                return new JsonResponse([
                    'message' => "Impossible de parier. Le montant de pari est inférieur au montant minimal autorié"
                ], Response::HTTP_FORBIDDEN);
            }
            if (
                ($typeBet->getReference() == FixedValuesConstants::TYPE_BET_NAP2
                    || $typeBet->getReference() == FixedValuesConstants::TYPE_BET_NAP3
                    || $typeBet->getReference() == FixedValuesConstants::TYPE_BET_NAP4
                    || $typeBet->getReference() == FixedValuesConstants::TYPE_BET_NAP5)
                && (double) $request->amount < 10
            ) {
                return new JsonResponse([
                    'message' => "Impossible de parier. Le montant de pari est inférieur au montant minimal autorié"
                ], Response::HTTP_FORBIDDEN);
            }
            $bet->setAmount((double) $request->amount);
            $bet->setTypeBet($typeBet);
            $bet->setGains($this->potentialsGains($bet, null));
            $this->manager->persist($bet);
            

            foreach ($ballsDrawns as $ball) {
                $ballDrawn = new BallDrawn();
                $ballDrawn->setBet($bet)
                    ->setBall($ball);
                $this->manager->persist($ballDrawn);
            }
        }
        if ($drawNew != null) {
            // Alors il s'agit du premier bet
            $drawNew->setPotentialsGains($this->potentialsGains($bet, null));
        } else {
            $draw->setPotentialsGains( $this->potentialsGains($bet, $draw));
        }
        $this->manager->flush();
        
        return $this->json([
            'message' => 'Tirage effectué avec succès',
            'draw' => ($drawNew != null)? $drawNew : $draw,
        ], Response::HTTP_OK, [], [
            'groups' => ['ball_drawn.index', 'ball.index', 'draw.index', 'bet.index', 'code', 'created_at']
        ]);

    }

    #[Route('/user/draw/validate/{number}', name: 'validate_draw', methods: ['POST'])]
    #[OA\Post(
        summary: 'Cette route permet d\'obtenir les paris d\'un ticket',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Les paris d\'un ticket',
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

    public function validate(string $number): JsonResponse
    {
        $user = $this->getUser();
        $actor = $this->actorRepository->findOneBy(['deleted' => false, 'user' => $user]);
        $draw = $this->drawRepository->findOneBy(['number' => $number, 'deleted' => false]);
        if (!$draw) {
            return new JsonResponse([
                'message' => "Aucun tirage ne correspond à cet numéro"
            ], Response::HTTP_FORBIDDEN);
        }
        if ($actor->getId() != $draw->getActor()->getId()) {
            return new JsonResponse([
                'message' => "Vous n'êtes pas autorisé à validé ce ticket"
            ], Response::HTTP_FORBIDDEN);
        }

        if ($draw->getStatus()->getName() == FixedValuesConstants::STATUS_GAME_VALIDATED) {
            return new JsonResponse([
                'message' => "Ce ticket a déja été validé"
            ], Response::HTTP_FORBIDDEN);
        }

        $amountBet = 0;
        $betsOfDraw = $this->betRepository->findBy(['draw' => $draw, 'deleted' => false]);
        foreach ($betsOfDraw as $bet) {
            $amountBet += $bet->getAmount();
        }
        $mainWallet = $actor->getMainWallet();
        if ($amountBet < 100) {
            return new JsonResponse([
                'message' => "Impossible de valider le ticket. Veuillez ajouter d'autres paris"
            ], Response::HTTP_FORBIDDEN);
        }
        if ($mainWallet < $amountBet) {
            /*
             * On regardes si on peut utiliser le compte ratacher pour faire la mise
             * */
            $reattachWallet = $actor->getReattachWallet();
            if ($reattachWallet < $amountBet) {
                return new JsonResponse([
                    'message' => "Solde insufisant"
                ], Response::HTTP_FORBIDDEN);
            } else {
                $reattachWallet -= $amountBet;
                $actor->setReattachWallet($reattachWallet);
                $draw->setWallet(FixedValuesConstants::TYPE_WALLET_REATTACH);
            }
        } else {
            $mainWallet -= $amountBet;
            $actor->setMainWallet($mainWallet);
            $draw->setWallet(FixedValuesConstants::TYPE_WALLET_MAIN);
        }
        $actor->setUpdatedAt(new \DateTime('now'));
        $draw->setAmount($amountBet);
        $draw->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_VALIDATED]));
        $draw->setUpdatedAt(new \DateTime('now'));
        $this->manager->persist($draw);
        $this->manager->flush();
        return $this->json([
            'message' => "Tirage validé avec succès",
            'draw' => $draw
        ], Response::HTTP_OK, [], [
            'groups' => ['ball_drawn.index', 'ball.index', 'draw.index', 'bet.index', 'code', 'created_at']
        ]);
    }

    private function potentialsGains(Bet $bet, Draw $draw = null): int
    {
        $gainsPotentials = 0;
        $gains = 0;
        if ($bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_NAP1_POTO) {
            $gains = (int) $bet->getAmount() * (int) $bet->getTypeBet()->getPercentage();
        }
        if ($bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_NAP1_BLOQUE) {
            $gains = $bet->getAmount() * $bet->getTypeBet()->getPercentage();
        }
        if ($bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_NAP2 || $bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_NAP3 || $bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_NAP4 || $bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_NAP5) {
            $gains = $bet->getAmount() * $bet->getTypeBet()->getPercentage();
        }
        if ($bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_TCHIGAN) {
            $amountByBall = $bet->getAmount() / 5;
            $gains = $amountByBall * 5 * 3548.791;

        }
        if (
            $bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_PERM2 ||
            $bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_PERM3
        ) {
            $couples = $this->coupleDrawnRepository->findBy(['bet' => $bet, 'deleted' => false]);
            foreach ($couples as $couple) {
                $gain = $couple->getAmount() * 300;
                $gains += $gain;
            }
        }
        $gainsPotentials += $gains;
        if($draw != null){
            $gainsPotentials += $draw->getPotentialsGains();
            // foreach ($bets as $bet) {
            //     $gains = 0;
            //     if ($bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_NAP1_POTO) {
            //         $gains = (int) $bet->getAmount() * (int) $bet->getTypeBet()->getPercentage();
            //     }
            //     if ($bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_NAP1_BLOQUE) {
            //         $gains = $bet->getAmount() * $bet->getTypeBet()->getPercentage();
            //     }
            //     if ($bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_NAP2 || $bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_NAP3 || $bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_NAP4 || $bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_NAP5) {
            //         $gains = $bet->getAmount() * $bet->getTypeBet()->getPercentage();
            //     }
            //     if ($bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_TCHIGAN) {
            //         $amountByBall = $bet->getAmount() / 5;
            //         $gains = $amountByBall * 5 * 3548.791;
        
            //     }
            //     if (
            //         $bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_PERM2 ||
            //         $bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_PERM3
            //     ) {
            //         $couples = $this->coupleDrawnRepository->findBy(['bet' => $bet, 'deleted' => false]);
            //         $gains = 0;
            //         foreach ($couples as $couple) {
            //             $gain = $couple->getAmount() * 300;
            //             $gains += $gain;
            //         }
            //     }
            //     $gainsPotentials += $gains;
            // }
        }
        return $gainsPotentials;
    }
}
