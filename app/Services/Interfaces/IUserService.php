<?php

namespace App\Services\Interfaces;

interface IUserService
{
    /**
     * Get all users.
     *
     * @return array
     */
    public function getAll(): array;

    /**
     * Get a user by ID.
     *
     * @param int $id
     * @return array
     */
    public function getById(int $id): array;

    /**
     * Update a user.
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update(int $id, array $data): array;

    /**
     * Delete a user.
     *
     * @param int $id
     * @return array
     */
    public function delete(int $id): array;
}
