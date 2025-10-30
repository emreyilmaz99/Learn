<?php

namespace App\Repositories\Interfaces;

interface UserRepositoryInterface
{
    /**
     * Get all users.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll();

    /**
     * Find a user by ID.
     *
     * @param int $id
     * @return \App\Models\User|null
     */
    public function findById(int $id);

    /**
     * Find a user by email.
     *
     * @param string $email
     * @return \App\Models\User|null
     */
    public function findByEmail(string $email);

    /**
     * Create a new user.
     *
     * @param array $data
     * @return \App\Models\User
     */
    public function create(array $data);

    /**
     * Update a user.
     *
     * @param int $id
     * @param array $data
     * @return \App\Models\User|null
     */
    public function update(int $id, array $data);

    /**
     * Delete a user.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get all users except the specified user.
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllExcept(int $userId);
}
