<?php

namespace App\Controller;

use App\Repository\ActorRepository;
use App\Repository\CountryRepository;
use App\Repository\UserRepository;
use App\Request\AppointRequest;
use App\Request\DeleteUserRequest;
use App\Request\UpdatedUserRequest;
use App\Utils\Constants\AppValuesConstants;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[Route('/api')]
#[OA\Tag(name: "User")]
class UserController extends AbstractController
{
    private ActorRepository $actorRepository;
    private UserRepository $userRepository;
    private EntityManagerInterface $manager;
    private  CountryRepository $countryRepository;
    public function __construct(ActorRepository $actorRepository,  UserRepository $userRepository, EntityManagerInterface $manager, CountryRepository $countryRepository)
    {
         $this->userRepository = $userRepository;
         $this->actorRepository = $actorRepository;
         $this->manager = $manager;
         $this->countryRepository = $countryRepository;
    }

    #[Route('/user/current', name: 'current_user', methods: ['GET'])]
    #[OA\Get(
        summary: 'Cette route permet d\'obtenir l\'utilisateur actuel',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Informations de l\'utilisateur actuel',
                content: new OA\JsonContent(
                    example: [
                        'id' => 1,
                        'email' => 'johndoe@gmail.com',
                        'roles' => ['ROLE_USER'],
                        'acteur' => [
                            'id' => 10,
                            'name' => 'John Doe'
                        ],
                        'pays' => [
                            'id' => 20,
                            'name' => 'France'
                        ]
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
    public function current(): JsonResponse
    {
        return $this->json($this->getUser(), Response::HTTP_OK, [], [
            'groups' => ['user.index','code' , 'actor.index', 'country.index']
        ]);
    }

    #[Route('/user/wallet', name: 'wallet_user', methods: ['GET'])]
    #[OA\Get(
        summary: 'Cette route permet d\'obtenir le solde de l\'utilisateur',
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Informations sur le solde de l\'utilisateur courant' ,
                content: new OA\JsonContent(
                    example: [
                       'main_wallet' => 'string',
                        'reattach_wallet' => 'string'
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
    public function wallet(): JsonResponse
    {
        $user = $this->getUser();
        $actor = $this->actorRepository->findOneBy(['deleted' => false, 'user' =>  $user]);

        return $this->json([
            'main_wallet' => $actor->getMainWallet(),
            'reattach_wallet' => $actor->getReattachWallet(),
        ], Response::HTTP_OK, [], [
            'groups' => ['user.index','code' , 'actor.index', 'country.index']
        ]);
    }

    #[Route('/user/appoint/marchant', name: 'appoint_marchant', methods: ['POST'])]
    #[OA\Post(
        summary: "Cette route de nommer un parieur simple en utilisateur marchant",
        requestBody: new OA\RequestBody(
            description: 'Corps de la requete',
            content: new OA\JsonContent(
                example: [
                    'identifier' => 'string',
                    'email' => 'string',
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_FORBIDDEN,
                description: 'Compte non actif',
                content: new OA\JsonContent(
                    example: ['message' => 'Votre compte n\'est pas encore actif! Veuillez contacter l\'administrateur système']
                )
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: 'Identifiants invalides',
                content: new OA\JsonContent(
                    example: ['message' => 'Identifiants invalides']
                )
            ),
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Authentification effectuée',
                content: new OA\JsonContent(
                    example: ['user' => [], 'token' => 'token.genere']
                )
            )
        ]
    )]
    public function appointMarchant(AppointRequest $request): JsonResponse
    {
        $role = $this->getUser()->getRoles()[0];
        if($role !== AppValuesConstants::ROLE_SUPER_ADMIN) {
            return new JsonResponse([
                'message' => 'Vous n\'avez pas le rôle réquis pour nommer un parieur simple en utilisateur marchant',
            ], Response::HTTP_FORBIDDEN);
        }
        $actor = $this->actorRepository->findOneBy(['deleted' => false, 'identifier' => $request->identifier, 'email' => $request->email]);
        $user = ($actor !== null) ? $actor->getUser() : null;
        if($actor == null){
            return new JsonResponse([
                'message' => 'Ces informations ne correspond pas un utilisateur dans la base de données',
            ], Response::HTTP_FORBIDDEN);
        }
        if($user !== null && !$user->isActive()){
            return new JsonResponse([
                'message' => 'Le compte de cet utilisateur n\'est pas actif',
            ], Response::HTTP_FORBIDDEN);
        }

        if($user->getRoles()[0] == AppValuesConstants::ROLE_USER_MERCHANT || $user->getRoles()[0] == AppValuesConstants::ROLE_SUPER_ADMIN){
            return new JsonResponse([
                'message' => 'Vous ne pouvez pas nommer cet utilisateur en marchant',
            ], Response::HTTP_FORBIDDEN);
        }
        $user->setRoles([AppValuesConstants::ROLE_USER_MERCHANT]);
        $this->manager->flush();
        return $this->json($user, Response::HTTP_OK, [], [
            'groups' => ['user.index','code' , 'actor.index', 'country.index']
        ]);
    }

    #[Route('/user/appoint/assistant', name: 'appoint_assistant', methods: ['POST'])]
    #[OA\Post(
        summary: "Cette route permet de nommer un utilisateur en administracteur assistant",
        requestBody: new OA\RequestBody(
            description: 'Corps de la requete',
            content: new OA\JsonContent(
                example: [
                    'identifier' => 'string',
                    'email' => 'string',
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_FORBIDDEN,
                description: 'Compte non actif',
                content: new OA\JsonContent(
                    example: ['message' => 'Votre compte n\'est pas encore actif! Veuillez contacter l\'administrateur système']
                )
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: 'Identifiants invalides',
                content: new OA\JsonContent(
                    example: ['message' => 'Identifiants invalides']
                )
            ),
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Authentification effectuée',
                content: new OA\JsonContent(
                    example: ['user' => [], 'token' => 'token.genere']
                )
            )
        ]
    )]
    public function appointAssistant(AppointRequest $request): JsonResponse
    {
        if($this->getUser()->getRoles()[0] !== AppValuesConstants::ROLE_SUPER_ADMIN) {
            return new JsonResponse([
                'message' => 'Vous n\'avez pas le rôle réquis pour nommer un parieur simple en utilisateur marchant',
            ], Response::HTTP_FORBIDDEN);
        }

        $actor = $this->actorRepository->findOneBy(['deleted' => false, 'identifier' => $request->identifier, 'email' => $request->email]);
        $user = ($actor !== null) ? $actor->getUser() : null;
        if($actor == null){
            return new JsonResponse([
                'message' => 'Ces informations ne correspond pas un utilisateur dans la base de données',
            ], Response::HTTP_FORBIDDEN);
        }
        if($user !== null && !$user->isActive()){
            return new JsonResponse([
                'message' => 'Le compte de cet utilisateur n\'est pas actif',
            ], Response::HTTP_FORBIDDEN);
        }

        if($user->getRoles()[0] == AppValuesConstants::ROLE_ASSISTANT_ADMIN || $user->getRoles()[0] == AppValuesConstants::ROLE_SUPER_ADMIN){
            return new JsonResponse([
                'message' => 'Vous ne pouvez pas nommer cet utilisateur en administracteur assistant',
            ], Response::HTTP_FORBIDDEN);
        }
        $user->setRoles([AppValuesConstants::ROLE_ASSISTANT_ADMIN]);
        $this->manager->flush();
        return $this->json($user, Response::HTTP_OK, [], [
            'groups' => ['user.index','code' , 'actor.index', 'country.index']
        ]);
    }

    #[Route('/user/update', name: 'update_user', methods: ['PUT'])]
    #[OA\Put(
        summary: "Cette route permet de modifier les informations des l'utilisateur connecté",
        requestBody: new OA\RequestBody(
            description: 'Corps de la requete',
            content: new OA\JsonContent(
                example: [
                    'lastName' => 'string',
                    'firstName' => 'string',
                    'country' => 'string',
                    'phone_number' => 'string',
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_FORBIDDEN,
                description: 'Compte non actif',
                content: new OA\JsonContent(
                    example: ['message' => 'Votre compte n\'est pas encore actif! Veuillez contacter l\'administrateur système']
                )
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: 'Identifiants invalides',
                content: new OA\JsonContent(
                    example: ['message' => 'Identifiants invalides']
                )
            ),
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Authentification effectuée',
                content: new OA\JsonContent(
                    example: ['user' => [], 'token' => 'token.genere']
                )
            )
        ]
    )]
    public function update(UpdatedUserRequest $request): JsonResponse
    {
        $user = $this->getUser();
        $actor = $this->actorRepository->findOneBy(['deleted' => false,  'user' =>$user]);
        $country =  $this->countryRepository->findOneBy(['deleted' => false, 'code' => $request->country]);
        if($country == null){
            return new JsonResponse([
                'message' => "Le pays choisi  n'existe pas."
            ], Response::HTTP_FORBIDDEN);
        }
        $actor->setCountry($country);
        $actor->setLastName($request->lastName);
        $actor->setFirstName($request->firstName);
        $actor->setPhoneNumber($request->phoneNumber);
        $this->manager->flush();
        return $this->json($actor, Response::HTTP_OK, [], [
            'groups' => ['user.index','code' , 'actor.index', 'country.index']
        ]);
    }
    #[Route('/user/delete', name: 'delete_user', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Cette route permet de désactiver un compte utilisateur",
        requestBody: new OA\RequestBody(
            description: 'Corps de la requete',
            content: new OA\JsonContent(
                example: [
                    'email' => 'string',
                    'identifier' => 'string',
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_FORBIDDEN,
                description: 'Compte non actif',
                content: new OA\JsonContent(
                    example: ['message' => 'Votre compte n\'est pas encore actif! Veuillez contacter l\'administrateur système']
                )
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: 'Identifiants invalides',
                content: new OA\JsonContent(
                    example: ['message' => 'Identifiants invalides']
                )
            ),
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Authentification effectuée',
                content: new OA\JsonContent(
                    example: ['user' => [], 'token' => 'token.genere']
                )
            )
        ]
    )]
    public function deleteAccount(DeleteUserRequest $request): JsonResponse
    {
        $actor = $this->actorRepository->findOneBy(['identifier' => $request->identifier, 'email' => $request->email, 'deleted' => false]);
        if($actor == null){
            return new JsonResponse([
                'message' => "Le compte de ce utilisateur n'est plus actif"
            ], Response::HTTP_FORBIDDEN);
        }
        $user = $actor->getUser();
        $actor->setDeleted(true);
        $user->setActive(false);
        $user->setDeleted(true);
        $this->manager->flush();
        return $this->json([
            'message' => 'Compte désactivé avec succès'
        ], Response::HTTP_OK, [], [

        ]);
    }
}
