<?php

declare(strict_types=1);

namespace App\Customer\Infrastructure\Controller;

use App\Customer\Application\Login\LoginCommand;
use App\Customer\Application\Login\LoginHandler;
use App\Customer\Infrastructure\Http\LoginRequest;
use App\Shared\Infrastructure\Http\AbstractApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class LoginController extends AbstractApiController
{
    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        private readonly LoginHandler $handler
    ) {
        parent::__construct($serializer, $validator);
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $dto = $this->validateRequest($request, LoginRequest::class);

        if ($dto instanceof JsonResponse) {
            return $dto; // Validation error
        }

        try {
            $command = new LoginCommand(
                email: $dto->email,
                password: $dto->password
            );

            $result = ($this->handler)($command);

            return $this->successResponse($result);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return $this->errorResponse('Internal server error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

