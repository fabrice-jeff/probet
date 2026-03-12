<?php
/**
 * Created by PhpStorm.
 * User: LANGANFIN  Rogelio
 * Date: 24/04/2021
 * Time: 21:18
 */

namespace App\EventListener;

use App\Services\AppServices;
use App\Services\RandomStringGeneratorServices;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CustomKernelListener
{
    /**
     * @var RandomStringGeneratorServices
     */
    private $stringGeneratorServices;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var AppServices
     */
    private $appServices;

    /**
     * CustomKernelListener constructor.
     * @param RandomStringGeneratorServices $stringGeneratorServices
     * @param EntityManagerInterface $manager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(AppServices $appServices,RandomStringGeneratorServices $stringGeneratorServices,TokenStorageInterface $tokenStorage){
        $this->stringGeneratorServices = $stringGeneratorServices;
        $this->tokenStorage = $tokenStorage;
        $this->appServices = $appServices;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $uniqueId = md5(uniqid());
        $uniqueId = substr($uniqueId, 0, 7);

        $rand = $this->stringGeneratorServices->random_alphanumeric_custom_length(8);
        $prefixe = $this->appServices->getTablenamePrefix(get_class($entity));

        if ($this->appServices->checkIfEntityHasField(get_class($entity), 'code'))
            $entity->setCode($prefixe . '-' . $rand . "-" . strtoupper($uniqueId));

        if ($this->appServices->checkIfEntityHasAssociation(get_class($entity), 'insertBy'))
            $entity->setInsertBy($this->tokenStorage->getToken()?->getUser());
    }


    public function onKernelException(ExceptionEvent $event)
    {
        // You get the exception object from the received event
        $exception = $event->getThrowable();
        $message = sprintf(
            'My Error says: %s with code: %s',
            $exception->getMessage(),
            $exception->getCode()
        );

        // Customize your response object to display the exception details
        $response = new Response();
        $response->setContent($message);

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // sends the modified response object to the event
        $event->setResponse($response);
    }
}