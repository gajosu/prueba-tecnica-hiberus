<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Controller;

use App\Product\Application\CreateProduct\CreateProductCommand;
use App\Product\Application\CreateProduct\CreateProductHandler;
use App\Product\Infrastructure\Http\CreateProductRequest;
use App\Shared\Infrastructure\Http\AbstractApiController;
use App\Shared\Infrastructure\Security\Attribute\RequiresRole;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[RequiresRole('ROLE_ADMIN')]
final class CreateProductController extends AbstractApiController
{
    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        private readonly CreateProductHandler $handler
    ) {
        parent::__construct($serializer, $validator);
    }

    #[Route('/api/products', name: 'api_products_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/products',
        summary: 'Create a new product',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: CreateProductRequest::class))
        ),
        tags: ['Products']
    )]
    #[OA\Response(
        response: 201,
        description: 'Product created successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'string', example: 'product-123'),
                new OA\Property(property: 'message', type: 'string', example: 'Product created successfully')
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Validation error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Validation failed'),
                new OA\Property(
                    property: 'violations',
                    type: 'object',
                    example: ['name' => 'Name is required']
                )
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 403, description: 'Forbidden - Admin role required')]
    public function __invoke(Request $request): JsonResponse
    {
        $dto = $this->validateRequest($request, CreateProductRequest::class);

        if ($dto instanceof JsonResponse) {
            return $dto; // Validation error
        }

        try {
            $command = new CreateProductCommand(
                name: $dto->name,
                description: $dto->description,
                price: $dto->price,
                currency: $dto->currency,
                stock: $dto->stock
            );

            $productId = ($this->handler)($command);

            return $this->successResponse([
                'id' => $productId,
                'message' => 'Product created successfully'
            ], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Internal server error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

