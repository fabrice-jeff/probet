<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Repository\ActorRepository;
use App\Repository\TransactionRepository;
use App\Repository\TypeTypeRepository;
use App\Repository\UserRepository;
use App\Request\TransactionRequest;
use App\Utils\Constants\AppValuesConstants;
use App\Utils\Constants\FixedValuesConstants;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
#[Route('/api')]
#[OA\Tag(name: "Transaction")]
class TransactionController extends AbstractController
{
    private EntityManagerInterface $manager;
    private UserRepository $userRepository;
    private TransactionRepository $transactionRepository;
    private TypeTypeRepository $typeTypeRepository;

    private ActorRepository $actorRepository;
    public function __construct(EntityManagerInterface $manager, UserRepository $userRepository, TransactionRepository $transactionRepository, ActorRepository $actorRepository, TypeTypeRepository $typeTypeRepository, )
    {
        $this->manager = $manager;
        $this->userRepository = $userRepository;
        $this->actorRepository = $actorRepository;
        $this->typeTypeRepository = $typeTypeRepository;
        $this->transactionRepository = $transactionRepository;
    }

    #[Route('/user/transaction/recharge_account', name: 'recharge_acccount', methods: ['GET'])]
    #[OA\Get(
        summary: 'Cette route permet de récuperer les numéros des diférents adminisracteurs  à contacter pour une recharge',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Les numéros des diférents adminisracteurs  à contacter pour une recharge',
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
    public function reahchargeAccount(): JsonResponse
    {
        $actorsArray = [];
        $actors = $this->actorRepository->findInformationAdmin();
        foreach ($actors as $actor){
            if($actor->getUser()->getRoles()[0] == AppValuesConstants::ROLE_ASSISTANT_ADMIN || $actor->getUser()->getRoles()[0] ==  AppValuesConstants::ROLE_USER_MERCHANT){
                $actorsArray[] = $actor;
            }
        }
        return $this->json( $actorsArray, Response::HTTP_OK, [], [
            'groups' => ['transaction.index','type_type.index','actor.index', 'country.index','code', 'created_at', 'insert_by', 'user.index']
        ]);
    }

    #[Route('/user/transaction/history', name: 'history_transaction', methods: ['GET'])]
    #[OA\Get(
        summary: 'Cette route permet de récuperer l\'historique des transactions',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'l\'historique des transactions',
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
    public function findHistoryTransaction(): JsonResponse
    {
        $user = $this->getUser();
        $actor = $this->actorRepository->findOneBy(['deleted' => false, 'user' => $user]);
        $transactionsArray = [];
        $transactions = null ;
        if($user->getRoles()[0] == AppValuesConstants::ROLE_SUPER_ADMIN ||  $user->getRoles()[0] == AppValuesConstants::ROLE_ASSISTANT_ADMIN){

            $transactions =  $this->transactionRepository->findBy(['deleted' => false]);
        }
        else if($user->getRoles()[0] == AppValuesConstants::ROLE_USER_MERCHANT){
            $transactions = $this->transactionRepository->findBy(['deleted' => false, 'insertBy' => $user]);
        }
        else{
            $transactions = $this->transactionRepository->findBy(['deleted' => false, 'actor' => $actor]);
        }

        foreach ($transactions as $transaction) {
            $objet =  [
                'id' => $transaction->getId(),
                'amount' => $transaction->getAmount(),
                'typeTransaction'=> $transaction->getTypeTransaction()->getReference(),
                'code' => $transaction->getCode(),
                'created_at' => $transaction->getCreatedAt(),
                'client' => $transaction->getActor()->getUser(),
                'insert_by' =>  $this->actorRepository->findOneBy(['user' => $transaction->getInsertBy()])->getUser(),
            ];
            $transactionsArray[] = $objet;
        }

        return $this->json( $transactionsArray, Response::HTTP_OK, [], [
            'groups' => ['transaction.index','type_type.index','actor.index', 'country.index','code', 'created_at', 'insert_by', 'user.index']
        ]);
    }

    #[Route('/user/transaction', name: 'transaction', methods: ['POST'])]
    #[OA\Post(
        summary: 'Cette route permet de faire une transaction',
        requestBody: new OA\RequestBody(
            description: 'Corps de la requete',
            content: new OA\JsonContent(
                example: [
                    'amount' => 'string',
                    'identifier' => 'string',
                    'type_transaction' => 'string',
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'TransactionRequest success',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'TransactionRequest success',
                    ],
                )
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: 'TransactionRequest error',
                content: new OA\JsonContent(
                    example: ['message' => 'TransactionRequest error'],
                )
            )
        ]
    )]
    public function transaction(TransactionRequest $request): JsonResponse
    {
        if($this->getUser()->getRoles()[0] == AppValuesConstants::ROLE_USER){
            return new JsonResponse([
                'message' => "Vous n'avez pas le rôle réquis pour faire une transaction"
            ], Response::HTTP_FORBIDDEN);
        }
        $currentActor = $this->actorRepository->findOneBy(['user' => $this->getUser()]);
        $actor  = $this->actorRepository->findOneBy(['identifier'=> $request->identifier]);
        if($actor == null){
            return new JsonResponse([
                'message' => "L'identifiant ne correspond à aucun utilisateur dans la base de donnée"
            ], Response::HTTP_FORBIDDEN);
        }
        if($actor->getDeleted()){
            return new JsonResponse([
                'message' => "Le compte de ce utilisateur n'est plus actif"
            ], Response::HTTP_FORBIDDEN);
        }
        $typeTransaction = $this->typeTypeRepository->findOneBy(['reference' => $request->typeTransaction]);
        if(!$typeTransaction){
            return new JsonResponse([
                'message' => "Cet type de transaction n'existe pas dans la base de donnée"
            ], Response::HTTP_FORBIDDEN);
        }
        $transactionsActor = $this->transactionRepository->findBy(['actor' => $actor, 'deleted' => false]);
        /**
         * Déterminer le type de transaction que l'utilisateur veut effectué d'abord
         */
        if($request->typeTransaction == FixedValuesConstants::TYPE_TRANSACTION_DEPOSIT){
            if($this->getUser()->getRoles()[0] == AppValuesConstants::ROLE_ASSISTANT_ADMIN || $this->getUser()->getRoles()[0] == AppValuesConstants::ROLE_USER_MERCHANT){
                /*
                 * Vérifier si l'utilisateur a les fonds nécessaires sur son compte pour l'envoyer au joueur
                 * Si C'est le cas retrancher le montant du compte
                 * Sinon le solde est insuffisant pour faire cette opération
                 * */
                $mainWalletCurrentActor =  $currentActor->getMainWallet();
                $amount = (double) $request->amount;
                if($mainWalletCurrentActor < $amount){
                    return new JsonResponse([
                        'message' => "Votre solde est insuffisant"
                    ], Response::HTTP_FORBIDDEN);
                }
                else{
                    /*
                     * Soustraire le montant de dépôt du solde de l'administracteur
                     * */
                    $mainWalletCurrentActor -= $amount;
                    $currentActor->setMainWallet($mainWalletCurrentActor);
                }
            }
            if(count($transactionsActor) == 0){
                $actor->setFirstTransaction(true);
                $actor->setMainWallet((double)$request->amount);
                $actor->setReattachWallet(1000);
            }
            else{
                $mainWallet = $actor->getMainWallet() + (double)$request->amount;
                $actor->setMainWallet($mainWallet);
            }
        }
        else if($request->typeTransaction == FixedValuesConstants::TYPE_TRANSACTION_WITHDRAW){

            /**
             * Déterminer le compte dans lequel il souhaite faire le retrait
             */
            if($request->wallet == FixedValuesConstants::TYPE_WALLET_MAIN){
                $mainWallet = $actor->getMainWallet();
                $amount = (double)$request->amount;
                if($mainWallet < $amount ){
                    return new JsonResponse([
                        'message' => "Solde insufisant pour faire ce retrait"
                    ], Response::HTTP_FORBIDDEN);
                }
                $mainWallet -=$amount ;
                $actor->setMainWallet( $mainWallet);
                if($this->getUser()->getRoles()[0] == AppValuesConstants::ROLE_ASSISTANT_ADMIN || $this->getUser()->getRoles()[0] == AppValuesConstants::ROLE_USER_MERCHANT){
                    $mainWalletCurrentActor =  $currentActor->getMainWallet();
                    $mainWalletCurrentActor += (double) $request->amount;
                    $currentActor->setMainWallet($mainWalletCurrentActor);
                }
            }
            else{
                /**
                 * Il s'agit du compte rattaché
                 */
                $reattachWallet =  $actor->getReattachWallet();
                $amount = (double)$request->amount;
                if($reattachWallet < 1000 * 100){
                    return new JsonResponse([
                        'message' => "Solde insufisant pour faire ce retrait dans le compte rattaché"
                    ], Response::HTTP_FORBIDDEN);
                }
                if($amount > ($reattachWallet*0.1)){
                    return new JsonResponse([
                        'message' => "Solde insufisant pour faire ce retrait dans le compte rattaché"
                    ], Response::HTTP_FORBIDDEN);
                }
                $reattachWallet -=$amount ;
                $actor->setReattachWallet( $reattachWallet);
                if($this->getUser()->getRoles()[0] == AppValuesConstants::ROLE_ASSISTANT_ADMIN || $this->getUser()->getRoles()[0] == AppValuesConstants::ROLE_USER_MERCHANT){
                    $mainWalletCurrentActor =  $currentActor->getMainWallet();
                    $mainWalletCurrentActor += (double) $request->amount;
                    $currentActor->setMainWallet($mainWalletCurrentActor);
                }
            }
            
        }
        $actor->setUpdatedAt(new \DateTime('now'));

        // Enregistrement de la transaction
        $transaction =  new Transaction();
        $transaction->setAmount((double) $request->amount);
        $transaction->setActor($actor);
        $transaction->setTypeTransaction($typeTransaction);
        $this->manager->persist($transaction);
        $this->manager->flush();
        return $this->json("Transaction effectuée avec succès", Response::HTTP_OK, [],);
    }
}
