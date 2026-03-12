<?php

namespace App\Controller;

use App\Controller\CountryController;
use App\Entity\BallWinner;
use App\Entity\BallWinnerDrawn;
use App\Repository\ActorRepository;
use App\Repository\BallDrawnRepository;
use App\Repository\BallRepository;
use App\Repository\BallWinnerDrawnRepository;
use App\Repository\BallWinnerRepository;
use App\Repository\BetRepository;
use App\Repository\CountryRepository;
use App\Repository\CoupleDrawnRepository;
use App\Repository\DrawRepository;
use App\Repository\GameRepository;
use App\Repository\StatusRepository;
use App\Request\WinnerBallRequest;
use App\Utils\Constants\AppValuesConstants;
use App\Utils\Constants\FixedValuesConstants;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
#[Route('/api')]
#[OA\Tag(name: "Game")]
class GameController extends AbstractController
{
    private BallRepository $ballRepository;
    private StatusRepository $statusRepository;
    private EntityManagerInterface $manager;
    private GameRepository $gameRepository;
    private DrawRepository $drawRepository;
    private BetRepository $betRepository;
    private BallDrawnRepository $ballDrawnRepository;
    private CoupleDrawnRepository $coupleDrawnRepository;
    private  BallWinnerRepository $ballWinnerRepository;
    private  CountryRepository $countryRepository;
    private BallWinnerDrawnRepository $ballWinnerDrawnRepository;
    private ActorRepository $actorRepository;

    public function __construct(
        BallRepository            $ballRepository,
        StatusRepository          $statusRepository,
        EntityManagerInterface    $manager,
        GameRepository            $gameRepository,
        DrawRepository            $drawRepository,
        BetRepository             $betRepository,
        BallDrawnRepository       $ballDrawnRepository,
        CoupleDrawnRepository     $coupleDrawnRepository,
        BallWinnerRepository      $ballWinnerRepository,
        CountryRepository         $countryRepository,
        BallWinnerDrawnRepository $ballWinnerDrawnRepository, ActorRepository $actorRepository,
    )
    {
        $this->ballRepository = $ballRepository;
        $this->statusRepository = $statusRepository;
        $this->manager =$manager;
        $this->gameRepository = $gameRepository;
        $this->drawRepository = $drawRepository;
        $this->betRepository = $betRepository;
        $this->ballDrawnRepository = $ballDrawnRepository;
        $this->coupleDrawnRepository = $coupleDrawnRepository;
        $this->ballWinnerRepository = $ballWinnerRepository;
        $this->countryRepository = $countryRepository;
        $this->ballWinnerDrawnRepository = $ballWinnerDrawnRepository;
        $this->actorRepository = $actorRepository;
    }

    /*#[Route('/game/period/{startTime}', name: 'game_by_period', methods: ['GET'])]
    #[OA\Get(
        summary: 'Cette route permet d\'obtenir les tirages d\'une période donnée dans la journée',
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
    public function findDrawByPeriod(String $startTime): JsonResponse
    {
        $now = new \DateTime('now');
        $date = $now->format('Y-m-d');
        $dateTime = "$date $startTime";
        $games =  $this->gameRepository->findGamesOfDay();
        $gameOfTime =  null;
        foreach ($games as $game) {
            if($dateTime == $game->getStartAt()->format('Y-m-d H')){
                $gameOfTime =  $game;
            }
        }
        $draws = ($gameOfTime != null)? $this->drawRepository->findBy(['deleted' => false, 'game' => $gameOfTime]) : [];
        $drawsArray = [];
        foreach ($draws as $draw){
            $betArray = [];
            $bets = $this->betRepository->findBy(['deleted' => false, 'draw' =>$draw]);
            foreach ($bets as $bet){
                $balls = [];
                $ballsDrawns =  $this->ballDrawnRepository->findBy(['deleted'=> false, 'bet' =>$bet]);
                foreach ($ballsDrawns as $ballDrawn){
                    $balls[] = $ballDrawn->getBall()->getId();
                }
                $betArray[]  = [
                    'amount' =>  $bet->getAmount(),
                    'typeBet' => $bet->getTypeBet(),
                    'createdAt' =>$bet->getCreatedAt(),
                    'balls' => $balls,
                ];
            }
            $drawsArray[] = [
                'draw' => $draw,
                'bets' => $betArray,
            ];
        }
        return $this->json( $drawsArray, Response::HTTP_OK, [], [
            'groups' => ['game.index' ,'code', 'created_at']
        ]);
    }*/

    #[Route('/game/history_winner_balls', name: 'game_history_winner_ball', methods: ['GET'])]
    #[OA\Get(
        summary: 'Cette route permet d\'obtenir la liste des boules gagnantes',
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
    public function historyWinnerBall(): JsonResponse
    {
        $games =  $this->gameRepository->findBy(['deleted' => false]);
        $gamesArray = [];
        foreach ($games as $game) {
            $ballWinner = $this->ballWinnerRepository->findOneBy(['deleted' => false, 'game' => $game]);
            $ballsWinnersDrawns = $this->ballWinnerDrawnRepository->findBy(['ballWinner' => $ballWinner]);
            $balls = [];
            $createdAt = null;
            foreach ($ballsWinnersDrawns as $value) {
                $createdAt = $value->getCreatedAt();
                $balls[] = $value->getBall()->getId();
            }
            $result =  [
                'game' => $game,
                'balls' => $balls,
                'createdAt' =>  $createdAt,
            ];
            $gamesArray[] = $result;
        }

        return $this->json( $gamesArray, Response::HTTP_OK, [], [
            'groups' => ['ball_winner.index' ,'game.index','code', 'created_at']
        ]);
    }


    /**
     * @throws \Exception
     */
    #[Route('/game/user/pull_winner_balls', name: 'pull_winner_balls', methods: ['POST'])]
    #[OA\Post(
        summary: 'Cette route permet de tirer les boules gagnantes',
        requestBody: new OA\RequestBody(
            description: 'Corps de la requete',
            content: new OA\JsonContent(
                example: [
                    'balls' => 'string',
                    // 'balls_two' => 'string',
                    // 'country' => 'string'
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Tirage de boules gagnantes',
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
    public function pullWinnerBalls(WinnerBallRequest $request): JsonResponse
    {
        if($this->getUser()->getRoles()[0] !== AppValuesConstants::ROLE_SUPER_ADMIN && $this->getUser()->getRoles()[0] !== AppValuesConstants::ROLE_ASSISTANT_ADMIN){
            return new JsonResponse([
                'message' => "Vous n'avez pas le rôle réquis pour annoncer une boule"
            ], Response::HTTP_FORBIDDEN);
        }
        $benin = $this->countryRepository->findOneBy(['code' => AppValuesConstants::CODE_BENIN]);
        $togo = $this->countryRepository->findOneBy(['code' => AppValuesConstants::CODE_TOGO]);
        // $country = $this->countryRepository->findOneBy(['deleted' => false, 'code' => $request->country]);
        $country = $benin;
        if($country == null){
            return new JsonResponse([
                'message' => "Le pays choisi n'existe pas."
            ], Response::HTTP_FORBIDDEN);
        }
        $balls =  json_decode($request->balls);
        // $ballsTwo = json_decode($request->balls_two);
        $now = new \DateTime('now');
        $now->modify('+1 hour'); 
        $date = $now->format('Y-m-d');
        $currentHour = $now->format('H:i');
        $dateTime=  "$date $currentHour";
        if(count($balls) != 5){
            return new JsonResponse([
                'message' => "Vous devez envoyer 5 numéros de boules"
            ], Response::HTTP_FORBIDDEN);
        }
        /**
         * Vérifier le pays qui organise le jeu et 
         * Vérifier l'intervalle d'heure dans laquelle on est(Le tirage de 09h)
         */
        // if($togo === $country && $dateTime > "$date 08:50" &&   $dateTime < "$date 09:10" ){
        //     if(count($ballsTwo) != 5){
        //         return new JsonResponse([
        //             'message' => "Vous devez envoyer 5 numéros de boules"
        //         ], Response::HTTP_FORBIDDEN);
        //     }
        // }

        $ballsWinnerArray = [];
        $ballsWinnerTwoArray = [];    
        /*
         * Création de l'objet winner
         * */
        $ballWinner = new BallWinner();
        // Pour le jeu du Bénin
        // if($country === $benin){

            if($dateTime >= "$date 00:00" && $dateTime <= "$date 10:55" ){
                $status = $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_ELEVEN]);
                $game = $this->gameRepository->findOneGameOfDay($now,$status, $country);
                $ballWinner->setGame($game);
            }
            else if($dateTime >= "$date 11:00" && $dateTime <= "$date 13:55"){
                $status = $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_FOURTEEN]);
                $game = $this->gameRepository->findOneGameOfDay($now,$status, $country);
                $ballWinner->setGame($game);
            }
            else if($dateTime >= "$date 14:00"  && $dateTime <= "$date 17:55"){
                $status = $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_EIGHTEEN]);
                $game = $this->gameRepository->findOneGameOfDay($now,$status, $country);
                $ballWinner->setGame($game);
            }
            else if($dateTime  >= "$date 18:00" && $dateTime <= "$date 20:55" ){
                $status = $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_TWENTY_ONE]);
                $game = $this->gameRepository->findOneGameOfDay($now,$status, $country);
                $ballWinner->setGame($game);
            }
    
            else if($dateTime  >= "$date 21:00" && $dateTime <= "$date 23:55" ){
                $status = $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_TWENTY_TREE]);
                $game = $this->gameRepository->findOneGameOfDay($now,$status, $country);
                $ballWinner->setGame($game);
            }
        // }else if( $country === $togo){
        //     // Pour le jeu de Togo
        //     if($dateTime < "$date 09:00"){
        //         $status = $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_NINE]);
        //         $game = $this->gameRepository->findOneGameOfDay($now,$status, $country);
        //         $ballWinner->setGame($game);
        //     }
        //     else if( $dateTime > "$date 09:10"  && $dateTime < "$date 13:00") {
        //         $status = $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_THIRTEEN]);
        //         $game = $this->gameRepository->findOneGameOfDay($now,$status, $country);
        //         $ballWinner->setGame($game);
        //     }
        //     else if($dateTime > "$date 13:10"  && $dateTime < "$date 18:00"){
        //         $status = $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_EIGHTEEN]);
        //         $game = $this->gameRepository->findOneGameOfDay($now,$status, $country);
        //         $ballWinner->setGame($game);
        //     }
        //     else if( $dateTime > "$date 18:10"  && $dateTime < "$date 23:00"){
        //         $status = $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_OF_NINE]);
        //         $game = $this->gameRepository->findOneGameOfDay($now,$status,$country);
        //         $ballWinner->setGame($game);
        //     }
        // }
        $ballWinner->setCountry($country);
        $this->manager->persist($ballWinner);

        /*
         * Enregistrement des boules gagnantes dans la base de donnée
         * */
        foreach ($balls as $value){
            $ballWinnerDrawn =  new BallWinnerDrawn();
            if(!$this->ballRepository->find($value)){
                return new JsonResponse([
                    'message' => "Le numéro de boules choisir n'est pas correct"
                ], Response::HTTP_FORBIDDEN);
            }
            if(in_array($value, $ballsWinnerArray)){
                return new JsonResponse([
                    'message' => "Deux boules de la même valeur ne peuvent pas être dans les résultats"
                ], Response::HTTP_FORBIDDEN);
            }
            $ballsWinnerArray[] = $value;
            $ballWinnerDrawn->setBallWinner($ballWinner);
            $ballWinnerDrawn->setBall($this->ballRepository->find($value));
            $this->manager->persist($ballWinnerDrawn);
        }

        /**
         * Vérifier le pays qui organise le jeu et 
         * Vérifier l'intervalle d'heure dans laquelle on est(Le tirage de 09h)
         */
        // if($togo === $country && $dateTime > "$date 08:50" &&   $dateTime < "$date 09:10" ){
        //     foreach ($ballsTwo as $value){
        //         $ballWinnerTwoDrawn =  new BallWinnerDrawn();
        //         if(!$this->ballRepository->find($value)){
        //             return new JsonResponse([
        //                 'message' => "Le numéro de boules choisir n'est pas correct"
        //             ], Response::HTTP_FORBIDDEN);
        //         }
        //         if(in_array($value, $ballsWinnerTwoArray) ||in_array($value, $ballsWinnerArray) ){
        //             return new JsonResponse([
        //                 'message' => "Deux boules de la même valeur ne peuvent pas être dans les résultats"
        //             ], Response::HTTP_FORBIDDEN);
        //         }
        //         $ballsWinnerTwoArray[] = $value;
        //         $ballWinnerTwoDrawn->setBallWinner($ballWinner);
        //         $ballWinnerTwoDrawn->setBall($this->ballRepository->find($value));
        //         $this->manager->persist($ballWinnerTwoDrawn);
        //     }
        // }


        /*
         * Récuperer les différents tirages
         * */
        $gameCurrent = $ballWinner->getGame();
        if($this->ballWinnerRepository->findOneBy(['deleted' => false, 'game' => $gameCurrent])){
            return new JsonResponse([
                'message' => "Impossible de tirer une seconde fois les boules gagnantes pour une même période"
            ], Response::HTTP_FORBIDDEN);
        }
        $statusDrawValidated = $this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_GAME_VALIDATED]);
        $draws = $this->drawRepository->findBy(['game' => $gameCurrent, 'deleted' => false, 'status' => $statusDrawValidated]);
        foreach ($draws as $draw){
            $bets = $this->betRepository->findBy(['deleted' => false, 'draw' =>$draw]);
            $actor = $draw->getActor();
            foreach ($bets as $bet){
                $mainWallet = $actor->getMainWallet();
                $reattachWallet =  $actor->getReattachWallet();
                // if(!empty($ballsWinnerTwoArray) && $bet->isDoubleChance() && ($bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_NAP1_POTO || $bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_NAP2 ))
                // {
                //     if($bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_NAP1_POTO){
                //         $ballDraw = $this->ballDrawnRepository->findOneBy(['bet' => $bet]);
                //         if($ballDraw->getBall()->getId() == $ballsWinnerArray[0] || $ballDraw->getBall()->getId() == $ballsWinnerTwoArray[0] ){
                //             $gainNormal = (int)$bet->getAmount() * (int) $bet->getTypeBet()->getPercentage();
                //             $gain = ($ballDraw->getBall()->getId() == $ballsWinnerArray[0])?$gainNormal - 0.25 *$gainNormal:$gainNormal - 0.50 *$gainNormal;
                //             $bet->setAmountWon($gain);
                //             $bet->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_BET_WIN]));
                //             if($draw->getWallet() == FixedValuesConstants::TYPE_WALLET_MAIN){
                //                 $mainWallet +=$gain;
                //                 $actor->setMainWallet($mainWallet);
                //             }
                //             else{
                //                 $reattachWallet +=$gain;
                //                 $actor->setReattachWallet($reattachWallet);
                //             }
                //         }
                //         else{
                //             $bet->setAmountWon(0);
                //             $bet->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_BET_LOST]));
                //         }
                //     }
                //     if($bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_NAP1_BLOQUE){
                //         $ballDraw = $this->ballDrawnRepository->findOneBy(['bet' => $bet]);
                //         if(in_array($ballDraw->getBall()->getId(), $ballsWinnerArray) || in_array($ballDraw->getBall()->getId(), $ballsWinnerTwoArray)){
                //             $gainNormal = $bet->getAmount() *  $bet->getTypeBet()->getPercentage();
                //             $gain = (in_array($ballDraw->getBall()->getId(), $ballsWinnerArray)) ? $gainNormal - 0.25 *$gainNormal:$gainNormal - 0.50 *$gainNormal;
                //             $bet->setAmountWon($gain);
                //             $bet->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_BET_WIN]));
                //             if($draw->getWallet() == FixedValuesConstants::TYPE_WALLET_MAIN){
                //                 $mainWallet +=$gain;
                //                 $actor->setMainWallet($mainWallet);
                //             }
                //             else{
                //                 $reattachWallet +=$gain;
                //                 $actor->setReattachWallet($reattachWallet);
                //             }
                //         }
                //         else{
                //             $bet->setAmountWon(0);
                //             $bet->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_BET_LOST]));
                //         }
                //     }
                //     if($bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_NAP2 ){
                //         $ballsDraws = $this->ballDrawnRepository->findBy(['bet' => $bet]);
                //         $result = true;
                //         $result2 =  true;

                //         // Pour la deuxieme série
                //         foreach ($ballsDraws as $value) {
                //             if (!in_array($value->getBall()->getId(), $ballsWinnerArray)) {
                //                 $result = false;
                //                 break;
                //             }
                //         }
                //         // Pour la deuxieme série
                //         foreach ($ballsDraws as $value) {
                //             if (!in_array($value->getBall()->getId(), $ballsWinnerTwoArray)) {
                //                 $result2 = false;
                //                 break;
                //             }
                //         }
                //         if($result || $result2){
                //             $gainNormal = $bet->getAmount() *  $bet->getTypeBet()->getPercentage();
                //             $gain = ($result)? $gainNormal - 0.25 *$gainNormal:$gainNormal - 0.50 *$gainNormal;
                //             $bet->setAmountWon($gain);
                //             $bet->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_BET_WIN]));
                //             if($draw->getWallet() == FixedValuesConstants::TYPE_WALLET_MAIN){
                //                 $mainWallet +=$gain;
                //                 $actor->setMainWallet($mainWallet);
                //             }
                //             else{
                //                 $reattachWallet +=$gain;
                //                 $actor->setReattachWallet($reattachWallet);
                //             }
                //         }
                //         else{
                //             $bet->setAmountWon(0);
                //             $bet->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_BET_LOST]));
                //         }
                //     }
                    
                // }
                // else
                // {
                    if($bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_NAP1_POTO){
                        $ballDraw = $this->ballDrawnRepository->findOneBy(['bet' => $bet]);
                        if($ballDraw->getBall()->getId() == $ballsWinnerArray[0]){
                            $gain = (int)$bet->getAmount() * (int) $bet->getTypeBet()->getPercentage();
                            $bet->setAmountWon($gain);
                            $bet->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_BET_WIN]));
                            if($draw->getWallet() == FixedValuesConstants::TYPE_WALLET_MAIN){
                                $mainWallet +=$gain;
                                $actor->setMainWallet($mainWallet);
                            }
                            else{
                                $reattachWallet +=$gain;
                                $actor->setReattachWallet($reattachWallet);
                            }
                        }
                        else{
                            $bet->setAmountWon(0);
                            $bet->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_BET_LOST]));
                        }
                    }
                    if($bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_NAP1_BLOQUE){
                        $ballDraw = $this->ballDrawnRepository->findOneBy(['bet' => $bet]);
                        if(in_array($ballDraw->getBall()->getId(), $ballsWinnerArray)){
                            $gain = $bet->getAmount() *  $bet->getTypeBet()->getPercentage();
                            $bet->setAmountWon($gain);
                            $bet->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_BET_WIN]));
                            if($draw->getWallet() == FixedValuesConstants::TYPE_WALLET_MAIN){
                                $mainWallet +=$gain;
                                $actor->setMainWallet($mainWallet);
                            }
                            else{
                                $reattachWallet +=$gain;
                                $actor->setReattachWallet($reattachWallet);
                            }
                        }
                        else{
                            $bet->setAmountWon(0);
                            $bet->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_BET_LOST]));
                        }
                    }
                    if($bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_NAP2 || $bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_NAP3  || $bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_NAP4 || $bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_NAP5 ){
                        $ballsDraws = $this->ballDrawnRepository->findBy(['bet' => $bet]);
                        $result = true;
                        foreach ($ballsDraws as $value) {
                            if (!in_array($value->getBall()->getId(), $ballsWinnerArray)) {
                                $result = false;
                                break;
                            }
                        }
                        if($result){
                            $gain = $bet->getAmount() *  $bet->getTypeBet()->getPercentage();
                            $bet->setAmountWon($gain);
                            $bet->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_BET_WIN]));
                            if($draw->getWallet() == FixedValuesConstants::TYPE_WALLET_MAIN){
                                $mainWallet +=$gain;
                                $actor->setMainWallet($mainWallet);
                            }
                            else{
                                $reattachWallet +=$gain;
                                $actor->setReattachWallet($reattachWallet);
                            }
                        }
                        else{
                            $bet->setAmountWon(0);
                            $bet->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_BET_LOST]));
                        }
                    }
                    if($bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_TCHIGAN){
                        $ballsDraws = $this->ballDrawnRepository->findBy(['bet' => $bet]);
                        $ballsfind = [];
                        foreach ($ballsDraws as $value) {
                            if (in_array($value->getBall()->getId(), $ballsWinnerArray)) {
                                $ballsfind[] = $value->getBall()->getId();
                            }
                        }
                        $amountByBall = $bet->getAmount()/5;
    
                        if(count($ballsfind) == 1){
                            $gain = $amountByBall * 3.226 ;
                            $bet->setAmountWon($gain);
                            $bet->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_BET_WIN]));
                            if($draw->getWallet() == FixedValuesConstants::TYPE_WALLET_MAIN){
                                $mainWallet +=$gain;
                                $actor->setMainWallet($mainWallet);
                            }
                            else{
                                $reattachWallet +=$gain;
                                $actor->setReattachWallet($reattachWallet);
                            }
                        }
                        else if(count($ballsfind) == 2){
                            $gain = $amountByBall *2 * 20.565 ;
                            $bet->setAmountWon($gain);
                            $bet->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_BET_WIN]));
                            if($draw->getWallet() == FixedValuesConstants::TYPE_WALLET_MAIN){
                                $mainWallet +=$gain;
                                $actor->setMainWallet($mainWallet);
                            }
                            else{
                                $reattachWallet +=$gain;
                                $actor->setReattachWallet($reattachWallet);
                            }
    
                        }
                        else if(count($ballsfind) == 3){
                            $gain = $amountByBall * 3 * 175.135;
                            $bet->setAmountWon($gain);
                            $bet->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_BET_WIN]));
                            if($draw->getWallet() == FixedValuesConstants::TYPE_WALLET_MAIN){
                                $mainWallet +=$gain;
                                $actor->setMainWallet($mainWallet);
                            }
                            else{
                                $reattachWallet +=$gain;
                                $actor->setReattachWallet($reattachWallet);
                            }
                        }
                        else if(count($ballsfind) == 4){
                            $gain = $amountByBall * 4 * 766.5323;
                            $bet->setAmountWon($gain);
                            $bet->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_BET_WIN]));
                            if($draw->getWallet() == FixedValuesConstants::TYPE_WALLET_MAIN){
                                $mainWallet +=$gain;
                                $actor->setMainWallet($mainWallet);
                            }
                            else{
                                $reattachWallet +=$gain;
                                $actor->setReattachWallet($reattachWallet);
                            }
                        }
                        else if(count($ballsfind) == 5){
                            $gain = $amountByBall * 5 * 3548.791;
                            $bet->setAmountWon($gain);
                            $bet->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_BET_WIN]));
    
                            if($draw->getWallet() == FixedValuesConstants::TYPE_WALLET_MAIN){
                                $mainWallet +=$gain;
                                $actor->setMainWallet($mainWallet);
                            }
                            else{
                                $reattachWallet +=$gain;
                                $actor->setReattachWallet($reattachWallet);
                            }
                        }
                        else{
                            $bet->setAmountWon(0);
                            $bet->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_BET_LOST]));
                        }
                    }
                    if($bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_PERM2 ||
                        $bet->getTypeBet()->getReference() == FixedValuesConstants::TYPE_BET_PERM3 ){
                        $couples = $this->coupleDrawnRepository->findBy(['bet' => $bet, 'deleted' =>false]);
                        $win = 0;
                        $amountWon = 0;
                        foreach ($couples as $couple) {
                            $balls =  json_decode($couple->getBalls());
                            $result = true;
                            foreach ($balls as $ball) {
                                if (!in_array($ball, $ballsWinnerArray)) {
                                    $result = false;
                                    break;
                                }
                            }
                            if($result){
                                $gain = $couple->getAmount() * 300;
                                $amountWon += $gain;
                                $couple->setAmount($gain);
                                $couple->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_BET_WIN]));
                                if($draw->getWallet() == FixedValuesConstants::TYPE_WALLET_MAIN){
                                    $mainWallet +=$gain;
                                    $actor->setMainWallet($mainWallet);
                                }
                                else{
                                    $reattachWallet +=$gain;
                                    $actor->setReattachWallet($reattachWallet);
                                }
                                $win++;
                            }
                            else{
                                $couple->setAmountWon(0);
                                $couple->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_BET_LOST]));
                            }
                        }
                        if($win == 0){
                            $bet->setAmountWon(0);
                            $bet->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_BET_LOST]));
                        }
                        else{
                            $bet->setAmountWon($amountWon);
                            $bet->setStatus($this->statusRepository->findOneBy(['name' => FixedValuesConstants::STATUS_BET_WIN]));
                        }
                    }
                // }
                
            }
        }
        $this->manager->flush();
        return $this->json( [
            'message' => "Les boules gagnantes ont été tirée avec succès",
        ], Response::HTTP_OK, [], [
            'groups' => ['draw.index' , 'ball.index','draw.index', 'bet.index', 'code', 'created_at']
        ]);
    }
}
