<?php

namespace App\Services;

use App\Security\User;

class BillingUserService
{
    /**
     * @throws \Exception
     */
    public function auth(string $jsonCredentials): User
    {
        $credentials = json_decode($jsonCredentials, true);
        $api = new BaseApiService();
        $jsonResponse = $api->post('/api/v1/auth', $credentials, [
            'Content-Type: application/json',
        ]);
        $response = json_decode($jsonResponse, true);
        if (array_key_exists('message', $response)) {
            throw new \Exception($response['message']);
        }
        return $this->currentUser($response['token'], $response['refresh_token']);
    }

    /**
     * @throws \Exception
     */
    public function currentUser(string $token, string $refreshToken): User
    {
        $api = new BaseApiService();
        $jsonResponse = $api->get('/api/v1/users/current', null, ["Authorization: Bearer {$token}"]);
        $response = json_decode($jsonResponse, true);
        if (array_key_exists('message', $response)) {
            throw new \Exception($response['message']);
        }
        $user = new User();
        $user->setEmail($response['username']);
        $user->setBalance($response['balance']);
        $user->setRoles($response['roles']);
        $user->setApiToken($token);
        $user->setRefreshToken($refreshToken);
        return $user;
    }

    /**
     * @throws \Exception
     */
    public function register(string $jsonCredentials): User
    {
        $credentials = json_decode($jsonCredentials, true);
        $api = new BaseApiService();
        $jsonResponse = $api->post('/api/v1/register', $credentials, [
            'Content-Type: application/json',
        ]);
        $response = json_decode($jsonResponse, true);
        if (array_key_exists('errors', $response)) {
            throw new \Exception(json_encode($response['errors']));
        }
        return $this->currentUser($response['token'], $response['refresh_token']);
    }

    public function refresh(string $refreshToken): User
    {
        $api = new BaseApiService();
        $jsonResponse = $api->post('/api/v1/token/refresh', ['refresh_token' => $refreshToken], [
            'Content-Type: application/json',
        ]);
        $response = json_decode($jsonResponse, true);
        if (array_key_exists('message', $response)) {
            throw new \Exception(json_encode($response['message']));
        }


        return $this->currentUser($response['token'], $response['refresh_token']);
    }
}
