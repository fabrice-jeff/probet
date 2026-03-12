<?php

namespace App\Controller\Auth;

use App\Entity\Actor;
use App\Entity\User;
use App\Repository\ActorRepository;
use App\Repository\CountryRepository;
use App\Repository\TypeTypeRepository;
use App\Repository\UserRepository;
use App\Request\AccountCreationRequest;
use App\Request\Auth\LoginRequest;
use App\Request\ForgetPasswordRequest;
use App\Request\ReinitializePasswordRequest;
use App\Request\VerificationCodeReinitialize;
use App\Services\NotificationServices;
use App\Utils\Constants\FixedValuesConstants;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use OpenApi\Attributes as OA;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
#[OA\Tag(name: "Login Check")]
class LoginController extends AbstractController
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private EntityManagerInterface $manager;
    private NotificationServices $notificationServices;
    private CountryRepository $countryRepository;
    private TypeTypeRepository $typeTypeRepository;
    private ActorRepository $actorRepository;

    public function __construct(UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $manager, NotificationServices $notificationServices, CountryRepository $countryRepository, TypeTypeRepository $typeTypeRepository, ActorRepository $actorRepository){
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->manager = $manager;
        $this->notificationServices = $notificationServices;
        $this->countryRepository = $countryRepository;
        $this->typeTypeRepository = $typeTypeRepository;
        $this->actorRepository = $actorRepository;
    }

    #[Route('/login', name: 'login', methods: 'POST')]
    #[OA\Post(
        summary: "Cette route d'obtenir le token JWT pour se logger",
        requestBody: new OA\RequestBody(
            description: 'Corps de la requête',
            content: new OA\JsonContent(
                example: [
                    'email' => 'johndoe@gmail.com',
                    'password' => 'password',
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
    public function login(LoginRequest $request, JWTTokenManagerInterface $tokenManager): JsonResponse
    {
        $user = $this->userRepository->findOneBy(['email' => $request->email]);

        if (!$user) {
            return new JsonResponse([
                'message' => 'Indentifiants invalides'
            ], Response::HTTP_FORBIDDEN);
        }

        if (!$this->passwordHasher->isPasswordValid($user, $request->password)) {
            return new JsonResponse([
                'message' => 'Indentifiants invalides'
            ], Response::HTTP_FORBIDDEN);
        }

        if (!$user->isActive()) {
            return new JsonResponse([
                'message' => 'Votre compte n\'est pas encore actif! Veuillez contacter l\'administrateur système'
            ], Response::HTTP_FORBIDDEN);
        }

        return $this->json([

            'token' => $tokenManager->create($user)
        ], Response::HTTP_OK, [], [
            'groups' => ['user.index', 'acteur.index', 'pays.index']
        ]);
    }


    /**
     * @throws RandomException
     */
    #[Route('/create-account', methods: ['POST'])]
    #[OA\Post(
        summary: "Cette route permet de créer un compte utilisateur",
        requestBody: new OA\RequestBody(
            description: 'Corps de la requete',
            content: new OA\JsonContent(
                example: [
                    'lastName' => 'string',
                    'firstName' => 'string',
                    'email' => 'string',
                    'country' => 'string',
                    'password' => 'string',
                    'password_confirmation' => 'string'
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Création d\'un utilisateur',
                content: new OA\JsonContent(
                    example: [
                        'lastName' => 'string',
                        'firstName' => 'string',
                        'email' => 'string',
                        'country' => 'string',
                        'password' => 'string',
                        'password_confirmation' => 'string'
                    ]
                )
            ),
        ]
    )]

    public function createAccount(AccountCreationRequest $request): JsonResponse
    {
        $country = $this->countryRepository->findOneBy(['code' => $request->country]);
        if (!$country) {
             return new JsonResponse([
                 'message' => "Le pays n'existe pas"
             ], Response::HTTP_FORBIDDEN);
        }


        if ($this->userRepository->findOneBy(['email' => $request->email])) {
            return new JsonResponse([
                'message' => 'Cet email est déjà associé à un compte utilisateur.'
            ], Response::HTTP_CONFLICT);
        }
        if($request->password != $request->passwordConfirmation){
            return new JsonResponse([
                'message' => "Les mots de passe ne correspondent pas."
            ], Response::HTTP_FORBIDDEN);
        }

        $user = new User();
        $password = $this->passwordHasher->hashPassword($user, $request->password);
        $user->setEmail($request->email)
            ->setActive(true)
            ->setRoles(['ROLE_USER'])
            ->setPassword($password);

        $this->manager->persist($user);
        $type = $this->typeTypeRepository->findOneBy(['reference' => FixedValuesConstants::TYPE_CATEGORIE_FAIBLE]);
        $identifier = strtoupper($request->lastName[0] .$request->lastName[1] . $request->firstName[0] . $request->firstName[1] . random_int(1000, 9999));
        $actor = new Actor();
        $actor->setEmail($request->email)
            ->setLastName($request->lastName)
            ->setFirstName($request->firstName)
            ->setCountry($country)
            ->setMainWallet(0)
            ->setReattachWallet(0)
            ->setUser($user)
            ->setIdentifier($identifier);
        $this->manager->persist($actor);
        $this->manager->flush();
        $this->notificationServices->notifyAccountCreation($actor, $password);
        return $this->json([
            'message' => 'Vous avez créer votre compte utilisateur avec succès',
        ]);
    }

    #[Route('/forget-password', methods: ['POST'])]
    #[OA\Post(
        summary: "Cette route permet de confirmer son email pour réinitailiser son mot de passe",
        requestBody: new OA\RequestBody(
            description: 'Corps de la requete',
            content: new OA\JsonContent(
                example: [
                    'email' => 'string',
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Confirmer son email pour réinitailiser son mot de passe',
                content: new OA\JsonContent(
                    example: [
                        'email' => 'string',
                    ]
                )
            ),
        ]
    )]
    public function forgetPassword(ForgetPasswordRequest $request): JsonResponse
    {
        $actor = $this->actorRepository->findOneBy(['email' => $request->email,]);
        if(!$actor){
            return new JsonResponse([
                'message' => "Ce utilisateur n'est pas dans la base de donnée"
            ], Response::HTTP_FORBIDDEN);
        }
        else if($actor->getDeleted()){
            return new JsonResponse([
                'message' => "Le compte de ce utilisateur est désactivé"
            ], Response::HTTP_FORBIDDEN);
        }
        $user = $actor->getUser();
        $code = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $user->setReinitializeCode($this->passwordHasher->hashPassword($user, $code));
        $user->setReinitializeCodeSentAt(new \DateTimeImmutable('now'));
        $this->notificationServices->notifyForgetPassword($actor, $code);
        $this->manager->flush();
        return $this->json([
            'message' => 'Compte trouvé avec succès. Consultez votre adresse mail',
        ]);
    }

    #[Route('/verification-code-reinitialize', methods: ['POST'])]
    #[OA\Post(
        summary: "Cette route permet de vérifier le code de réinitialisation de votre mot de passe",
        requestBody: new OA\RequestBody(
            description: 'Corps de la requete',
            content: new OA\JsonContent(
                example: [
                    'code' => 'string',
                    'email' => 'string'
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'vérifier le code de réinitialisation de votre mot de passe',
                content: new OA\JsonContent(
                    example: [
                        'code' => 'string',
                        'email' => 'string'
                    ]
                )
            ),
        ]
    )]
    public function verificationCodeReinitialize(VerificationCodeReinitialize $request): JsonResponse
    {
        $user = $this->userRepository->findOneBy(['email' => $request->email]);

        if (!$user) {
            return new JsonResponse([
                'message' => 'Cet email n\'existe pas'
            ], Response::HTTP_FORBIDDEN);
        }
        if (!password_verify($request->code, $user->getReinitializeCode())) {
            return new JsonResponse([
                'message' => 'Code incorrect'
            ], Response::HTTP_FORBIDDEN);
        }
        /*
         * Vérifir maintenant que le code est toujours valide         * */
        return $this->json([
            'message' => 'Code correct, vous pouvez changer votre mot de passe',
        ]);
    }


    #[Route('/reinitialize-password', methods: ['POST'])]
    #[OA\Post(
        summary: "Cette route permet de réinitailiser votre mot de passe",
        requestBody: new OA\RequestBody(
            description: 'Corps de la requete',
            content: new OA\JsonContent(
                example: [
                    'email' => 'string',
                    'password' => 'string',
                    'password_confirmation' => 'string'
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Vérifier le code de réinitialisation de votre mot de passe',
                content: new OA\JsonContent(
                    example: [
                        'email' => 'string',
                        'password' => 'string',
                        'password_confirmation' => 'string'
                    ]
                )
            ),
        ]
    )]
    public function ReinitializePassword(ReinitializePasswordRequest $request): JsonResponse
    {
        $user = $this->userRepository->findOneBy(['email' => $request->email]);
        if (!$this->userRepository->findOneBy(['email' => $request->email])) {
            return new JsonResponse([
                'message' => 'Cet email n\'est pas  associé à un compte utilisateur.'
            ], Response::HTTP_CONFLICT);
        }
        if($request->password != $request->passwordConfirmation){
            return new JsonResponse([
                'message' => "Les mots de passe ne correspondent pas."
            ], Response::HTTP_FORBIDDEN);
        }
        $password = $this->passwordHasher->hashPassword($user, $request->password);
        $user->setPassword($password);
        $this->manager->flush();
        return $this->json([
            'message' => 'Mot de passe modifier avec succès',
        ]);
    }

}
