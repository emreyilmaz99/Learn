<?php

namespace App\Services\Interfaces;

interface IAuthService
{
    /**
     * Login a user.
     *
     * @param string $email
     * @param string $password
     * @return array
     */
    public function login(string $email, string $password): array;

    /**
     * Register a new user.
     *
     * @param array $data
     * @return array
     */
    public function register(array $data): array;

    /**
     * Logout a user.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function logout($user): bool;
}
