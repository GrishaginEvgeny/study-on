<?php

namespace App\Services;

use App\Security\User;

class BillingCoursesService
{
    public function course(string $code): array
    {
        $api = new BaseApiService();
        $jsonedResponse = $api->get("/api/v1/courses/{$code}", null, [
            'Content-Type: application/json',
        ]);
        $arrayedResponse = json_decode($jsonedResponse, true);
        if (array_key_exists('message', $arrayedResponse)) {
            throw new \Exception($arrayedResponse['message']);
        }
        return $arrayedResponse;
    }

    public function buy(string $code, User $user)
    {
        $api = new BaseApiService();
        $jsonedResponse = $api->post(
            "/api/v1/courses/{$code}/pay",
            null,
            ["Authorization: Bearer {$user->getApiToken()}"]
        );
        $arrayedResponse = json_decode($jsonedResponse, true);
        if (array_key_exists('message', $arrayedResponse)) {
            throw new \Exception($arrayedResponse['message']);
        }
    }

    public function transactions(User $user, array $params = []): array
    {
        $api = new BaseApiService();
        $jsonedResponse = $api->get("/api/v1/transactions", $params, ["Authorization: Bearer {$user->getApiToken()}"]);
        $arrayedResponse = json_decode($jsonedResponse, true);
        if (array_key_exists('message', $arrayedResponse)) {
            throw new \Exception($arrayedResponse['message']);
        }
        return $arrayedResponse;
    }
}
