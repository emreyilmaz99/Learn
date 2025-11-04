<?php

namespace App\Services\Eloquent;

use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\IUserService;
use App\Core\Class\ServiceResponse;

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
     * @return ServiceResponse
     */
    public function getAll(): ServiceResponse
    {
        $users = $this->userRepository->getAll();

        return new ServiceResponse(200, true, 'Kullanıcılar başarıyla getirildi', $users);
    }

    /**
     * Get a user by ID.
     *
     * @param int $id
     * @return ServiceResponse
     */
    public function getById(int $id): ServiceResponse
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            return new ServiceResponse(404, false, 'Kullanıcı bulunamadı');
        }

        return new ServiceResponse(200, true, 'Kullanıcı başarıyla getirildi', $user);
    }

    /**
     * Update a user.
     *
     * @param int $id
     * @param array $data
     * @return ServiceResponse
     */
    public function update(int $id, array $data): ServiceResponse
    {
        $user = $this->userRepository->update($id, $data);

        if (!$user) {
            return new ServiceResponse(404, false, 'Kullanıcı bulunamadı');
        }

        return new ServiceResponse(200, true, 'Kullanıcı başarıyla güncellendi', $user);
    }

    /**
     * Delete a user.
     *
     * @param int $id
     * @return ServiceResponse
     */
    public function delete(int $id): ServiceResponse
    {
        $deleted = $this->userRepository->delete($id);

        if (!$deleted) {
            return new ServiceResponse(404, false, 'Kullanıcı bulunamadı');
        }

        return new ServiceResponse(200, true, 'Kullanıcı başarıyla silindi');
    }

    /**
     * Get all users except the specified user.
     *
     * @param int $userId
     * @return ServiceResponse
     */
    public function getUsersExcept(int $userId): ServiceResponse
    {
        $users = $this->userRepository->getAllExcept($userId);

        return new ServiceResponse(200, true, 'Kullanıcılar listelendi', $users);
    }
}
