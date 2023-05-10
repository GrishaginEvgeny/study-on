<?php

namespace App\Services;

use App\Exception\BillingNotFoundException;
use App\Exception\BillingValidationException;
use App\Security\User;

class BillingUserService
{
    /**
     * @throws BillingUserWrongCredentialsException
     * @throws \JsonException
     */
    public function auth(string $jsonCredentials): array
    {
        $credentials = json_decode($jsonCredentials, true, 512, JSON_THROW_ON_ERROR);
        $jsonResponse = BaseApiService::post('/api/v1/auth', $credentials);
        $response = json_decode($jsonResponse, true, 512, JSON_THROW_ON_ERROR);
        if (array_key_exists('code', $response)) {
            throw new BillingUserWrongCredentialsException($response['message']);
        }
        return $this->currentUser($response['token'], $response['refresh_token']);
    }

    /**
     * @throws BillingUserWrongCredentialsException
     */
    public function currentUser(string $token, string $refreshToken): array
    {
        $jsonResponse = BaseApiService::get('/api/v1/users/current', null, ["Authorization: Bearer {$token}"]);
        $response = json_decode($jsonResponse, true, 512, JSON_THROW_ON_ERROR);
        if (array_key_exists('code', $response)) {
            throw new BillingUserWrongCredentialsException($response['message']);
        }
        $userInfo = array_merge($response, ['token' => $token, 'refreshToken' => $refreshToken]);
        return $userInfo;
    }

    /**
     * @throws BillingNotFoundException
     * @throws BillingValidationException
     */
    public function register(string $jsonCredentials): array
    {
        $credentials = json_decode($jsonCredentials, true, 512, JSON_THROW_ON_ERROR);
        $jsonResponse = BaseApiService::post('/api/v1/register', $credentials);
        $response = json_decode($jsonResponse, true, 512, JSON_THROW_ON_ERROR);
        if (array_key_exists('code', $response)) {
            if (array_key_exists('errors', $response)) {
                throw new BillingValidationException(null, 406, null, $response['errors']);
            }
            if (array_key_exists('message', $response)) {
                throw new BillingNotFoundException($response['message']);
            }
        }
        return $this->currentUser($response['token'], $response['refresh_token']);
    }

    /**
     * @throws BillingUserWrongCredentialsException
     * @throws \JsonException
     */
    public function refresh(string $refreshToken): array
    {
        $jsonResponse = BaseApiService::post('/api/v1/token/refresh', ['refresh_token' => $refreshToken]);
        $response = json_decode($jsonResponse, true, 512, JSON_THROW_ON_ERROR);
        if (array_key_exists('code', $response)) {
            throw new BillingUserWrongCredentialsException($response['message']);
        }

        return ['token' => $response['token'], 'refresh_token' => $response['refresh_token']];
    }
}
