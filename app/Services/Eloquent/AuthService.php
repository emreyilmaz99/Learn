<?php

namespace App\Services\Eloquent;

use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\IAuthService;
use Illuminate\Support\Facades\Hash;
use App\Core\Class\ServiceResponse;

class AuthService implements IAuthService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Login a user.
     *
     * @param string $email
     * @param string $password
     * @return array
     */
    public function login(string $email, string $password): ServiceResponse
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            return new ServiceResponse(401, false, 'Geçersiz kimlik bilgileri');
        }

        $newToken = $user->createToken('auth_token');
        $token = $newToken->plainTextToken;
        if (isset($newToken->accessToken) && $newToken->accessToken) {
            $newToken->accessToken->expires_at = now()->addMinutes(30);
            $newToken->accessToken->save();
        }

        return new ServiceResponse(200, true, 'Giriş başarılı', [
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Register a new user.
     *
     * @param array $data
     * @return ServiceResponse
     */
    public function register(array $data): ServiceResponse
    {
        $data['password'] = Hash::make($data['password']);
        
        $user = $this->userRepository->create($data);
        
        $newToken = $user->createToken('auth_token');
        $token = $newToken->plainTextToken;
        if (isset($newToken->accessToken) && $newToken->accessToken) {
            $newToken->accessToken->expires_at = now()->addMinutes(30);
            $newToken->accessToken->save();
        }

        return new ServiceResponse(201, true, 'Kullanıcı başarıyla kaydedildi', [
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Logout a user.
     *
     * @param \App\Models\User $user
     * @return ServiceResponse
     */
    public function logout($user): ServiceResponse
    {
        $user->currentAccessToken()->delete();
        return new ServiceResponse(200, true, 'Çıkış başarılı');
    }
}
