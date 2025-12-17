<?php

declare(strict_types=1);

namespace App\Order\Infrastructure\Controller;

use App\Order\Application\CreateOrder\CreateOrderCommand;
use App\Order\Application\CreateOrder\CreateOrderHandler;
use App\Order\Domain\Exception\InsufficientStockException;
use App\Order\Infrastructure\Http\CreateOrderRequest;
use App\Product\Domain\Exception\ProductNotFoundException;
use App\Shared\Infrastructure\Http\AbstractApiController;
use App\Shared\Infrastructure\Security\Attribute\RequiresRole;
use App\Shared\Infrastructure\Security\CurrentUser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[RequiresRole('ROLE_USER')]
final class CreateOrderController extends AbstractApiController
{
    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        private readonly CreateOrderHandler $handler,
        private readonly CurrentUser $currentUser
    ) {
        parent::__construct($serializer, $validator);
    }

    #[Route('/api/orders', name: 'api_orders_create', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $dto = $this->validateRequest($request, CreateOrderRequest::class);

        if ($dto instanceof JsonResponse) {
            return $dto; // Validation error
        }

        try {
            // Get customer ID from authenticated user
            $customerId = $this->currentUser->id();

            $items = array_map(
                fn($item) => [
                    'productId' => $item->productId,
                    'quantity' => $item->quantity
                ],
                $dto->items
            );

            $command = new CreateOrderCommand(
                customerId: $customerId,
                items: $items
            );

            $orderId = ($this->handler)($command);

            return $this->successResponse([
                'id' => $orderId,
                'message' => 'Order created successfully'
            ], Response::HTTP_CREATED);
        } catch (ProductNotFoundException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (InsufficientStockException $e) {
            return $this->errorResponse($e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Internal server error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

