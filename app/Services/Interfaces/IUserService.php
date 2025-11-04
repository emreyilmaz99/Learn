<?php

namespace App\Services\Interfaces;

use App\Core\Class\ServiceResponse;

interface IUserService
{
    /**
     * Get all users.
     *
     * @return ServiceResponse
     */
    public function getAll(): ServiceResponse;

    /**
     * Get a user by ID.
     *
     * @param int $id
     * @return ServiceResponse
     */
    public function getById(int $id): ServiceResponse;

    /**
     * Update a user.
     *
     * @param int $id
     * @param array $data
     * @return ServiceResponse
     */
    public function update(int $id, array $data): ServiceResponse;

    /**
     * Delete a user.
     *
     * @param int $id
     * @return ServiceResponse
     */
    public function delete(int $id): ServiceResponse;

    /**
     * Get all users except the specified user.
     *
     * @param int $userId
     * @return ServiceResponse
     */
    public function getUsersExcept(int $userId): ServiceResponse;
}
