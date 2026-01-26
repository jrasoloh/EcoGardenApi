<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
        } else {
            $statusCode = 500;
        }

        if ($statusCode === 404) {
            $message = "Ressource introuvable.";
        } elseif ($statusCode === 403) {
            $message = "Accès refusé. Vous n'avez pas les droits nécessaires pour effectuer cette action.";
        } elseif ($statusCode === 403) {
            $message = "Accès refusé. Vous n'avez pas les droits nécessaires pour effectuer cette action.";
        } else {
            $message = $exception->getMessage();
        }

        $data = [
            'error' => $message,
            'code' => $statusCode
        ];

        $response = new JsonResponse($data, $statusCode);
        $event->setResponse($response);
    }
}
