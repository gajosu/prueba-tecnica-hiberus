<?php

declare(strict_types=1);

namespace App\Order\Infrastructure\Controller;

use App\Order\Application\CheckoutOrder\CheckoutOrderCommand;
use App\Order\Application\CheckoutOrder\CheckoutOrderHandler;
use App\Order\Domain\Exception\OrderNotFoundException;
use App\Order\Infrastructure\Http\CheckoutOrderRequest;
use App\Product\Domain\Exception\InsufficientStockException;
use App\Shared\Infrastructure\Http\AbstractApiController;
use App\Shared\Infrastructure\Security\Attribute\RequiresRole;
use App\Shared\Infrastructure\Security\CurrentUser;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
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
    #[OA\Post(
        path: '/api/orders/{id}/checkout',
        summary: 'Checkout order',
        description: 'Process payment for an order (simulated)',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: CheckoutOrderRequest::class))
        ),
        tags: ['Orders']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Order ID',
        required: true,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Response(
        response: 200,
        description: 'Payment processed successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Payment processed successfully'),
                new OA\Property(property: 'order_id', type: 'string'),
                new OA\Property(property: 'status', type: 'string', example: 'paid')
            ]
        )
    )]
    #[OA\Response(response: 400, description: 'Bad request (insufficient stock, payment failed, etc.)')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 404, description: 'Order not found')]
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
        } catch (InsufficientStockException $e) {
            // Return 400 Bad Request for insufficient stock
            return new JsonResponse([
                'error' => $e->getMessage(),
                'type' => 'insufficient_stock'
            ], Response::HTTP_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (\DomainException $e) {
            // Domain exceptions (like payment failed)
            return new JsonResponse([
                'error' => $e->getMessage(),
                'type' => 'domain_error'
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}

