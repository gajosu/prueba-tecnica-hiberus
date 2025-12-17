<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractApiController extends AbstractController
{
    public function __construct(
        protected readonly SerializerInterface $serializer,
        protected readonly ValidatorInterface $validator
    ) {
    }

    protected function validateRequest(Request $request, string $dtoClass): mixed
    {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            if (!is_array($data)) {
                return new JsonResponse([
                    'error' => 'Invalid JSON',
                    'message' => 'Request body must be a JSON object'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Create DTO instance using reflection for constructor properties
            $reflection = new \ReflectionClass($dtoClass);
            $constructor = $reflection->getConstructor();

            if (!$constructor) {
                throw new \RuntimeException("DTO $dtoClass must have a constructor");
            }

            $args = [];
            foreach ($constructor->getParameters() as $param) {
                $paramName = $param->getName();
                $value = $data[$paramName] ?? null;

                // Handle nested DTOs (like OrderItemRequest[])
                if ($value !== null && is_array($value) && $param->getType() && $param->getType()->getName() === 'array') {
                    // Check if it's an array of DTOs using doc comment
                    $paramType = $this->getParameterDocType($reflection, $paramName);
                    if ($paramType && str_ends_with($paramType, '[]')) {
                        $itemClass = substr($paramType, 0, -2);
                        $fullItemClass = $reflection->getNamespaceName() . '\\' . $itemClass;

                        if (class_exists($fullItemClass)) {
                            $value = array_map(function($item) use ($fullItemClass) {
                                if (is_array($item)) {
                                    // Get constructor params for nested DTO
                                    $itemReflection = new \ReflectionClass($fullItemClass);
                                    $itemConstructor = $itemReflection->getConstructor();
                                    $itemArgs = [];

                                    foreach ($itemConstructor->getParameters() as $itemParam) {
                                        $itemParamName = $itemParam->getName();
                                        $itemArgs[] = $item[$itemParamName] ?? ($itemParam->isDefaultValueAvailable() ? $itemParam->getDefaultValue() : null);
                                    }

                                    return new $fullItemClass(...$itemArgs);
                                }
                                return $item;
                            }, $value);
                        }
                    }
                }

                if ($value === null && !$param->isDefaultValueAvailable() && !$param->allowsNull()) {
                    $args[] = $param->getType()->getName() === 'string' ? '' : null;
                } else {
                    $args[] = $value ?? ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : null);
                }
            }

            $dto = new $dtoClass(...$args);

            $errors = $this->validator->validate($dto);

            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }

                return new JsonResponse([
                    'error' => 'Validation failed',
                    'violations' => $errorMessages
                ], Response::HTTP_BAD_REQUEST);
            }

            return $dto;
        } catch (\JsonException $e) {
            return new JsonResponse([
                'error' => 'Invalid JSON',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Invalid request',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    private function getParameterDocType(\ReflectionClass $class, string $paramName): ?string
    {
        // First try the constructor's docblock
        $constructor = $class->getConstructor();
        if ($constructor) {
            $docComment = $constructor->getDocComment();
            if ($docComment && preg_match('/@param\s+([^\s]+)\s+\$' . preg_quote($paramName) . '/', $docComment, $matches)) {
                return $matches[1];
            }
        }

        // Then try the class's docblock
        $docComment = $class->getDocComment();
        if ($docComment && preg_match('/@param\s+([^\s]+)\s+\$' . preg_quote($paramName) . '/', $docComment, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function successResponse(mixed $data, int $status = Response::HTTP_OK): JsonResponse
    {
        return new JsonResponse($data, $status);
    }

    protected function errorResponse(string $message, int $status = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return new JsonResponse(['error' => $message], $status);
    }
}

