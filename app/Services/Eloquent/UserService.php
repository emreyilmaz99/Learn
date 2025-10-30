<?php

namespace App\Services\Eloquent;

use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\IUserService;

class UserService implements IUserService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get all users.
     *
     * @return array
     */
    public function getAll(): array
    {
        $users = $this->userRepository->getAll();

        return [
            'success' => true,
            'message' => 'Kullanıcılar başarıyla getirildi',
            'data' => $users,
        ];
    }

    /**
     * Get a user by ID.
     *
     * @param int $id
     * @return array
     */
    public function getById(int $id): array
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Kullanıcı bulunamadı',
            ];
        }

        return [
            'success' => true,
            'message' => 'Kullanıcı başarıyla getirildi',
            'data' => $user,
        ];
    }

    /**
     * Update a user.
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update(int $id, array $data): array
    {
        $user = $this->userRepository->update($id, $data);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Kullanıcı bulunamadı',
            ];
        }

        return [
            'success' => true,
            'message' => 'Kullanıcı başarıyla güncellendi',
            'data' => $user,
        ];
    }

    /**
     * Delete a user.
     *
     * @param int $id
     * @return array
     */
    public function delete(int $id): array
    {
        $deleted = $this->userRepository->delete($id);

        if (!$deleted) {
            return [
                'success' => false,
                'message' => 'Kullanıcı bulunamadı',
            ];
        }

        return [
            'success' => true,
            'message' => 'Kullanıcı başarıyla silindi',
        ];
    }

    /**
     * Get all users except the specified user.
     *
     * @param int $userId
     * @return array
     */
    public function getUsersExcept(int $userId): array
    {
        $users = $this->userRepository->getAllExcept($userId);

        return [
            'success' => true,
            'message' => 'Kullanıcılar listelendi',
            'data' => $users,
        ];
    }
}
