<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Functional;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['onKernelException', 1],
            ],
        ];
    }

    public function onKernelException($event): void
    {
        if ($event instanceof GetResponseForExceptionEvent) { // Symfony 3.4 only
            $exception = $event->getException();
        } else {
            $exception = $event->getThrowable();
        }

        $code = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

        $event->setResponse(new JsonResponse([
            'code' => $code,
            'message' => $exception->getMessage(),
            'trace' => $exception->getTrace(),
        ], $code));
    }
}
