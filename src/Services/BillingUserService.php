<?php

namespace App\Services;

use App\Security\User;

class BillingUserService
{
    /**
     * @throws \Exception
     */
    public function auth(string $jsonedCredentials): User
    {
        $Credentials = json_decode($jsonedCredentials, true);
        $api = new BaseApiService();
        $jsonedResponse = $api->post('/api/v1/auth', $Credentials, [
            'Content-Type: application/json',
        ]);
        $arrayedResponse = json_decode($jsonedResponse, true);
        if (array_key_exists('message', $arrayedResponse)) {
            throw new \Exception($arrayedResponse['message']);
        }
        return $this->currentUser($arrayedResponse['token'], $arrayedResponse['refresh_token']);
    }
    /**
     * @throws \Exception
     */
    public function currentUser(string $token, string $refreshToken): User
    {
        $api = new BaseApiService();
        $jsonedResponse = $api->get('/api/v1/users/current', null, ["Authorization: Bearer {$token}"]);
        $arrayedResponse = json_decode($jsonedResponse, true);
        if (array_key_exists('message', $arrayedResponse)) {
            throw new \Exception($arrayedResponse['message']);
        }
        $user = new User();
        $user->setEmail($arrayedResponse['username']);
        $user->setBalance($arrayedResponse['balance']);
        $user->setRoles($arrayedResponse['roles']);
        $user->setApiToken($token);
        $user->setRefreshToken($refreshToken);
        return $user;
    }

    /**
     * @throws \Exception
     */
    public function register(string $jsonedCredentials): User
    {
        $Credentials = json_decode($jsonedCredentials, true);
        $api = new BaseApiService();
        $jsonedResponse = $api->post('/api/v1/register', $Credentials, [
            'Content-Type: application/json',
        ]);
        $arrayedResponse = json_decode($jsonedResponse, true);
        if (array_key_exists('errors', $arrayedResponse)) {
            throw new \Exception(json_encode($arrayedResponse['errors']));
        }
        return $this->currentUser($arrayedResponse['token'], $arrayedResponse['refresh_token']);
    }

    public function refresh(string $refreshToken): User
    {
        $api = new BaseApiService();
        $jsonedResponse = $api->post('/api/v1/token/refresh', ['refresh_token' => $refreshToken], [
            'Content-Type: application/json',
        ]);
        $arrayedResponse = json_decode($jsonedResponse, true);
        if (array_key_exists('message', $arrayedResponse)) {
            throw new \Exception(json_encode($arrayedResponse['message']));
        }

        if (array_key_exists('code', $arrayedResponse) && $arrayedResponse['code'] === 401) {
            throw new \Exception('Ошибка авторизации. Проверьте правильность введенных данных!');
        }

        return $this->currentUser($arrayedResponse['token'], $arrayedResponse['refresh_token']);
    }
}
