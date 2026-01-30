<?php

declare(strict_types=1);

namespace Gazelle\Http;

use Gazelle\Application\Common\Bus\CommandBus;
use Gazelle\Application\Common\Bus\QueryBus;
use Gazelle\Core\Container\Container;
use Gazelle\Core\Http\Response;

/**
 * Base Controller
 *
 * Provides common functionality for all controllers.
 */
abstract class Controller
{
    protected readonly CommandBus $commandBus;
    protected readonly QueryBus $queryBus;

    public function __construct(
        protected readonly Container $container
    ) {
        $this->commandBus = $container->get(CommandBus::class);
        $this->queryBus = $container->get(QueryBus::class);
    }

    /**
     * Return a JSON response
     *
     * @param array<string, mixed> $headers
     */
    protected function json(mixed $data, int $status = 200, array $headers = []): Response
    {
        return Response::json($data, $status, $headers);
    }

    /**
     * Return a success response
     */
    protected function success(mixed $data = null, string $message = 'Success'): Response
    {
        return $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Return an error response
     */
    protected function error(string $message, int $status = 400, mixed $errors = null): Response
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return $this->json($response, $status);
    }

    /**
     * Return a not found response
     */
    protected function notFound(string $message = 'Not found'): Response
    {
        return $this->error($message, 404);
    }

    /**
     * Return an unauthorized response
     */
    protected function unauthorized(string $message = 'Unauthorized'): Response
    {
        return $this->error($message, 401);
    }

    /**
     * Return a forbidden response
     */
    protected function forbidden(string $message = 'Forbidden'): Response
    {
        return $this->error($message, 403);
    }

    /**
     * Return a redirect response
     */
    protected function redirect(string $url, int $status = 302): Response
    {
        return Response::redirect($url, $status);
    }

    /**
     * Return a created response
     */
    protected function created(mixed $data = null, string $location = ''): Response
    {
        $response = $this->json([
            'success' => true,
            'data' => $data,
        ], 201);

        if ($location !== '') {
            return $response->withHeader('Location', $location);
        }

        return $response;
    }

    /**
     * Return a no content response
     */
    protected function noContent(): Response
    {
        return Response::noContent();
    }
}
