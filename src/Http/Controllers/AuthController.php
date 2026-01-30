<?php

declare(strict_types=1);

namespace Gazelle\Http\Controllers;

use Gazelle\Application\User\Commands\AuthenticateUser;
use Gazelle\Application\User\Commands\RegisterUser;
use Gazelle\Core\Http\Request;
use Gazelle\Core\Http\Response;
use Gazelle\Http\Controller;

/**
 * Authentication Controller
 */
final class AuthController extends Controller
{
    /**
     * Handle user login
     */
    public function login(Request $request): Response
    {
        $username = $request->input('username', '');
        $password = $request->input('password', '');

        if ($username === '' || $password === '') {
            return $this->error('Username and password are required');
        }

        $command = new AuthenticateUser(
            username: $username,
            password: $password,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        $result = $this->commandBus->dispatch($command);

        if (!$result->success) {
            return $this->error($result->error, 401);
        }

        return $this->success($result->session, 'Login successful');
    }

    /**
     * Handle user registration
     */
    public function register(Request $request): Response
    {
        $username = $request->input('username', '');
        $email = $request->input('email', '');
        $password = $request->input('password', '');
        $inviteCode = $request->input('invite_code');

        if ($username === '' || $email === '' || $password === '') {
            return $this->error('All fields are required');
        }

        try {
            $command = new RegisterUser(
                username: $username,
                email: $email,
                password: $password,
                inviteCode: $inviteCode
            );

            $user = $this->commandBus->dispatch($command);

            return $this->created($user);
        } catch (\DomainException $e) {
            return $this->error($e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Handle logout
     */
    public function logout(Request $request): Response
    {
        // Session invalidation would happen here
        return $this->success(null, 'Logout successful');
    }
}
