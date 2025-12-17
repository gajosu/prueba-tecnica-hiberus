<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: 'kernel.exception')]
final class ExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        // Only handle JSON requests
        if (!$request->isXmlHttpRequest() && !str_contains($request->getRequestFormat(), 'json')) {
            return;
        }

        $response = $this->createJsonResponse($exception);
        $event->setResponse($response);
    }

    private function createJsonResponse(\Throwable $exception): JsonResponse
    {
        // Handle validation errors
        if ($exception instanceof ValidationFailedException) {
            return $this->handleValidationException($exception);
        }

        // Handle HTTP exceptions
        if ($exception instanceof HttpExceptionInterface) {
            return $this->handleHttpException($exception);
        }

        // Handle generic exceptions
        return $this->handleGenericException($exception);
    }

    private function handleValidationException(ValidationFailedException $exception): JsonResponse
    {
        $violations = $exception->getViolations();
        $errors = [];

        foreach ($violations as $violation) {
            $propertyPath = $violation->getPropertyPath();
            $errors[$propertyPath][] = $violation->getMessage();
        }

        return new JsonResponse([
            'message' => 'The given data was invalid.',
            'errors' => $errors,
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function handleHttpException(HttpExceptionInterface $exception): JsonResponse
    {
        $statusCode = $exception->getStatusCode();
        $message = $this->getMessageForStatusCode($statusCode, $exception);

        $data = [
            'message' => $message,
            'status' => $statusCode,
        ];

        // Add details for specific status codes
        if ($statusCode === Response::HTTP_FORBIDDEN) {
            $data['error'] = 'Access denied. You do not have permission to access this resource.';
        } elseif ($statusCode === Response::HTTP_NOT_FOUND) {
            $data['error'] = 'The requested resource was not found.';
        } elseif ($statusCode === Response::HTTP_UNAUTHORIZED) {
            $data['error'] = 'Authentication is required to access this resource.';
        }

        return new JsonResponse($data, $statusCode);
    }

    private function handleGenericException(\Throwable $exception): JsonResponse
    {
        // In production, don't expose internal error messages
        $isDebug = $_ENV['APP_ENV'] === 'dev';

        $data = [
            'message' => 'An error occurred while processing your request.',
            'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
        ];

        if ($isDebug) {
            $data['error'] = $exception->getMessage();
            $data['file'] = $exception->getFile();
            $data['line'] = $exception->getLine();
        }

        return new JsonResponse($data, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    private function getMessageForStatusCode(int $statusCode, \Throwable $exception): string
    {
        return match ($statusCode) {
            Response::HTTP_BAD_REQUEST => 'Bad request',
            Response::HTTP_UNAUTHORIZED => 'Unauthorized',
            Response::HTTP_FORBIDDEN => 'Forbidden',
            Response::HTTP_NOT_FOUND => 'Not found',
            Response::HTTP_METHOD_NOT_ALLOWED => 'Method not allowed',
            Response::HTTP_UNPROCESSABLE_ENTITY => 'Unprocessable entity',
            default => $exception->getMessage() ?: 'An error occurred',
        };
    }
}

