<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    /**
     * Get all users.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return User::all();
    }

    /**
     * Find a user by ID.
     *
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Find a user by email.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Create a new user.
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Update a user.
     *
     * @param int $id
     * @param array $data
     * @return User|null
     */
    public function update(int $id, array $data): ?User
    {
        $user = $this->findById($id);
        
        if ($user) {
            $user->update($data);
            return $user->fresh();
        }

        return null;
    }

    /**
     * Delete a user.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $user = $this->findById($id);
        
        if ($user) {
            return $user->delete();
        }

        return false;
    }

    /**
     * Get all users except the specified user.
     *
     * @param int $userId
     * @return Collection
     */
    public function getAllExcept(int $userId): Collection
    {
        return User::where('id', '!=', $userId)
            ->select('id', 'name', 'email')
            ->orderBy('name', 'asc')
            ->get();
    }
}
