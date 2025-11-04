<?php

namespace App\Services\Interfaces;

use App\Core\Class\ServiceResponse;

interface IAuthService
{
    /**
     * Login a user.
     *
     * @param string $email
     * @param string $password
     * @return ServiceResponse
     */
    public function login(string $email, string $password): ServiceResponse;

    /**
     * Register a new user.
     *
     * @param array $data
     * @return ServiceResponse
     */
    public function register(array $data): ServiceResponse;

    /**
     * Logout a user.
     *
     * @param \App\Models\User $user
     * @return ServiceResponse
     */
    public function logout($user): ServiceResponse;
}
