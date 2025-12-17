<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Controller;

use App\Product\Application\CreateProduct\CreateProductCommand;
use App\Product\Application\CreateProduct\CreateProductHandler;
use App\Product\Infrastructure\Http\CreateProductRequest;
use App\Shared\Infrastructure\Http\AbstractApiController;
use App\Shared\Infrastructure\Security\Attribute\RequiresRole;
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

