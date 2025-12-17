<?php

declare(strict_types=1);

namespace App\Customer\Infrastructure\Controller;

use App\Customer\Application\Login\LoginCommand;
use App\Customer\Application\Login\LoginHandler;
use App\Customer\Infrastructure\Http\LoginRequest;
use App\Shared\Infrastructure\Http\AbstractApiController;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
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
    #[OA\Post(
        path: '/api/login',
        summary: 'User login',
        description: 'Authenticate user and get JWT token. Use admin@example.com / customer1@example.com with password "password"',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: LoginRequest::class))
        ),
        tags: ['Authentication']
    )]
    #[OA\Response(
        response: 200,
        description: 'Login successful',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'customer_id', type: 'string', example: 'admin-001'),
                new OA\Property(property: 'email', type: 'string', example: 'admin@example.com'),
                new OA\Property(property: 'name', type: 'string', example: 'Admin User'),
                new OA\Property(property: 'role', type: 'string', example: 'ROLE_ADMIN'),
                new OA\Property(property: 'token', type: 'string', example: 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...')
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Invalid credentials',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Invalid credentials')
            ]
        )
    )]
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

