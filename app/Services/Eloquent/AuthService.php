<?php

namespace App\Services\Eloquent;

use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\IAuthService;
use Illuminate\Support\Facades\Hash;

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
    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            return [
                'success' => false,
                'message' => 'Geçersiz kimlik bilgileri',
            ];
        }

        $newToken = $user->createToken('auth_token');
        $token = $newToken->plainTextToken;
        // set 30 minutes expiry on the token model if available
        if (isset($newToken->accessToken) && $newToken->accessToken) {
            $newToken->accessToken->expires_at = now()->addMinutes(30);
            $newToken->accessToken->save();
        }

        return [
            'success' => true,
            'message' => 'Giriş başarılı',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ];
    }

    /**
     * Register a new user.
     *
     * @param array $data
     * @return array
     */
    public function register(array $data): array
    {
        $data['password'] = Hash::make($data['password']);
        
        $user = $this->userRepository->create($data);
        
        $newToken = $user->createToken('auth_token');
        $token = $newToken->plainTextToken;
        if (isset($newToken->accessToken) && $newToken->accessToken) {
            $newToken->accessToken->expires_at = now()->addMinutes(30);
            $newToken->accessToken->save();
        }

        return [
            'success' => true,
            'message' => 'Kullanıcı başarıyla kaydedildi',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ];
    }

    /**
     * Logout a user.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function logout($user): bool
    {
        $user->currentAccessToken()->delete();
        return true;
    }
}
