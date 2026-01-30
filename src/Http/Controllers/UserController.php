<?php

declare(strict_types=1);

namespace Gazelle\Http\Controllers;

use Gazelle\Application\User\Queries\GetUserById;
use Gazelle\Core\Http\Request;
use Gazelle\Core\Http\Response;
use Gazelle\Http\Controller;

/**
 * User Controller
 */
final class UserController extends Controller
{
    /**
     * Get the current authenticated user
     */
    public function me(Request $request): Response
    {
        $userId = $request->attribute('user_id');

        if ($userId === null) {
            return $this->unauthorized();
        }

        $query = new GetUserById((int) $userId);
        $user = $this->queryBus->dispatch($query);

        if ($user === null) {
            return $this->notFound('User not found');
        }

        return $this->success($user);
    }

    /**
     * Get a user by ID
     */
    public function show(Request $request, int $id): Response
    {
        $query = new GetUserById($id);
        $user = $this->queryBus->dispatch($query);

        if ($user === null) {
            return $this->notFound('User not found');
        }

        return $this->success($user);
    }

    /**
     * List users (admin only)
     */
    public function index(Request $request): Response
    {
        // Pagination and filtering would go here
        return $this->success([]);
    }
}
