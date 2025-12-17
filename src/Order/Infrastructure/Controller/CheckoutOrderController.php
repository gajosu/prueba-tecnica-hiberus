<?php

declare(strict_types=1);

namespace App\Order\Infrastructure\Controller;

use App\Order\Application\CheckoutOrder\CheckoutOrderCommand;
use App\Order\Application\CheckoutOrder\CheckoutOrderHandler;
use App\Order\Domain\Exception\OrderNotFoundException;
use App\Order\Infrastructure\Http\CheckoutOrderRequest;
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
final class CheckoutOrderController extends AbstractApiController
{
    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        private readonly CheckoutOrderHandler $handler,
        private readonly CurrentUser $currentUser
    ) {
        parent::__construct($serializer, $validator);
    }

    #[Route('/api/orders/{id}/checkout', name: 'api_orders_checkout', methods: ['POST'])]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        $dto = $this->validateRequest($request, CheckoutOrderRequest::class);

        if ($dto instanceof JsonResponse) {
            return $dto; // Validation error
        }

        try {
            // Get customer ID from authenticated user
            $customerId = $this->currentUser->id();

            $command = new CheckoutOrderCommand(
                orderId: $id,
                customerId: $customerId,
                paymentMethod: $dto->paymentMethod
            );

            $result = ($this->handler)($command);

            return $this->successResponse($result);
        } catch (OrderNotFoundException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Internal server error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

