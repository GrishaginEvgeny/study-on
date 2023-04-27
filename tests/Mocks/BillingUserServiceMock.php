<?php

namespace App\Tests\Mocks;

use App\Security\User;
use App\Services\BillingUserService;
use Symfony\Component\HttpFoundation\Response;

class BillingUserServiceMock extends BillingUserService
{
    public function auth(string $jsonedCredentials): User
    {
        $arrayedCredentials = json_decode($jsonedCredentials, true);
        if (
            ($arrayedCredentials['username'] === 'admin@study.com' && $arrayedCredentials['password'] === 'admin')
            || ($arrayedCredentials['username'] === 'usualuser@study.com' && $arrayedCredentials['password'] === 'user')
        ) {
            $refreshToken = base64_encode(json_encode([
                'username' => $arrayedCredentials['username'],
                'password' => $arrayedCredentials['password'],
                'roles' => $arrayedCredentials['username'] === 'admin@study.com' ? ['ROLE_SUPER_ADMIN'] : ['ROLE_USER'],
                'balance' => $arrayedCredentials['username'] === 'admin@study.com' ? 111111111111 : 1000
            ]));
            $token = base64_encode(json_encode([
                'username' => $arrayedCredentials['username'],
                'iat' => (new \DateTime('now'))->getTimestamp(),
                'exp' => (new \DateTime('+ 1 hour'))->getTimestamp(),
                'roles' => $arrayedCredentials['username'] === 'admin@study.com' ? ['ROLE_SUPER_ADMIN'] : ['ROLE_USER'],
            ]));
            $token = "filler.{$token}";
            return $this->currentUser($token, $refreshToken);
        } else {
            throw new \Exception(json_encode(['code' => 400,'message' => 'Invalid Credentials.']));
        }
    }

    public function currentUser(string $token, string $refreshToken): User
    {
        $arrayedCredentials = json_decode(base64_decode($refreshToken, true), true, 512, JSON_THROW_ON_ERROR);
        try {
            $user = new User();
            $user->setEmail($arrayedCredentials['username'])
                ->setApiToken($token)
                ->setRoles($arrayedCredentials['roles'])
                ->setBalance($arrayedCredentials['username'] === 'admin@study.com' ? 111111111111 : 1000)
                ->setRefreshToken($refreshToken);
            return $user;
        } catch (\Exception $e) {
            throw new \Exception(json_encode(['code' => 400,'message' => 'Invalid JWT Token.']));
        }
    }

    public function register(string $jsonedCredentials): User
    {
        $arrayedCredentials = json_decode($jsonedCredentials, true);
        $passExp = "/(?=.*[0-9])(?=.*[.!@#$%^&*])(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z!@#$%^&*.]+$/";
        if (!preg_match($passExp, $arrayedCredentials['password'])) {
            throw new \Exception(json_encode([
                    "password" => 'Пароль должен содержать как один из спец. символов (.!@#$%^&*), 
                    прописную и строчные буквы латинского алфавита и цифру.'
                ]));
        }
        if (strlen($arrayedCredentials['password']) < 6) {
            throw new \Exception(json_encode([
                    "password" => 'Пароль должен содержать минимум 6 символов.'
                ]));
        }
        if (
            $arrayedCredentials['username'] === 'admin@study.com'
            || $arrayedCredentials['username'] === 'usualuser@study.com'
        ) {
            throw new \Exception(json_encode([
                    "password" => 'Пользователь с таким E-mail уже зарегистрирован.'
                ]));
        }
        if (!filter_var($arrayedCredentials['username'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception(json_encode([
                    "password" => 'Поле e-mail содержит некорректные данные.'
                ]));
        }
        if (strlen($arrayedCredentials['username']) === 0) {
            throw new \Exception(json_encode([
                    "username" => 'Поле e-mail не может быт пустым.'
                ]));
        }
        $refreshToken = base64_encode(json_encode([
            'username' => $arrayedCredentials['username'],
            'password' => $arrayedCredentials['password'],
            'roles' => ['ROLE_USER'],
            'balance' => 1000
        ]));
        $token = base64_encode(json_encode([
            'username' => $arrayedCredentials['username'],
            'iat' => (new \DateTime('now'))->getTimestamp(),
            'exp' => (new \DateTime('+ 1 hour'))->getTimestamp(),
            'roles' => ['ROLE_USER'],
        ]));
        $token = "filler.{$token}";
        return $this->currentUser($token, $refreshToken);
    }

    /**
     * @throws \JsonException
     */
    public function refresh(string $refreshToken): User
    {
        $arrayedPayload = json_decode(base64_decode($refreshToken, true),
            true, 512, JSON_THROW_ON_ERROR);
        $user = new User();
        $token = base64_encode(json_encode([
            'username' => $arrayedPayload['username'],
            'iat' => (new \DateTime('now'))->getTimestamp(),
            'exp' => (new \DateTime('+ 1 hour'))->getTimestamp(),
            'roles' => ['ROLE_USER'],
        ]));
        $token = "filler.{$token}";
        $user->setEmail($arrayedPayload['username'])
            ->setApiToken($token)
            ->setRoles($arrayedPayload['username'] === 'admin@study.com' ? ['ROLE_SUPER_ADMIN'] : ['ROLE_USER'])
            ->setBalance($arrayedPayload['username'] === 'admin@study.com' ? 111111111111 : 1000)
            ->setRefreshToken($refreshToken);
        return $user;
    }
}
