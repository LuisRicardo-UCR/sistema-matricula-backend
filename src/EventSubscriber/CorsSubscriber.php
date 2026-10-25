<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class CorsSubscriber implements EventSubscriberInterface
{
    private const ALLOWED_ORIGIN = 'http://localhost:4200';
    private const ALLOWED_METHODS = 'GET, POST, PUT, DELETE, OPTIONS';
    private const ALLOWED_HEADERS = 'Content-Type, Authorization';

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['handlePreflightRequest', 1000],
            KernelEvents::RESPONSE => 'addCorsHeaders',
        ];
    }

    public function handlePreflightRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$this->isApiRequest($request) || !$request->isMethod(Request::METHOD_OPTIONS)) {
            return;
        }

        if ($request->headers->get('Origin') !== self::ALLOWED_ORIGIN) {
            $event->setResponse(new JsonResponse(['message' => 'Origin not allowed'], JsonResponse::HTTP_FORBIDDEN));
            return;
        }

        $response = new Response('', Response::HTTP_NO_CONTENT);
        $response->headers->set('Access-Control-Allow-Origin', self::ALLOWED_ORIGIN);
        $response->headers->set('Access-Control-Allow-Methods', self::ALLOWED_METHODS);
        $response->headers->set('Access-Control-Allow-Headers', self::ALLOWED_HEADERS);
        $response->headers->set('Access-Control-Max-Age', '3600');

        $event->setResponse($response);
    }

    public function addCorsHeaders(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        if (!$this->isApiRequest($request)
            || $request->headers->get('Origin') !== self::ALLOWED_ORIGIN
        ) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->set('Access-Control-Allow-Origin', self::ALLOWED_ORIGIN);
        $response->headers->set('Access-Control-Allow-Methods', self::ALLOWED_METHODS);
        $response->headers->set('Access-Control-Allow-Headers', self::ALLOWED_HEADERS);
        $response->setVary('Origin', false);
    }

    private function isApiRequest(Request $request): bool
    {
        return preg_match('#^/api(?:/|$)#', $request->getPathInfo()) === 1;
    }
}
