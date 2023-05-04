<?php

namespace App\Services;

use App\Entity\Course;
use App\Security\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BillingCoursesService
{
    public function course(string $code): array
    {
        $api = new BaseApiService();
        $jsonResponse = $api->get("/api/v1/courses/{$code}", null, [
            'Content-Type: application/json',
        ]);
        $response = json_decode($jsonResponse, true, 512, JSON_THROW_ON_ERROR);
        if (array_key_exists('message', $response)) {
            throw new \RuntimeException($response['message']);
        }
        return $response;
    }

    public function buy(string $code, User $user)
    {
        $api = new BaseApiService();
        $jsonResponse = $api->post(
            "/api/v1/courses/{$code}/pay",
            null,
            ["Authorization: Bearer {$user->getApiToken()}"]
        );
        $response = json_decode($jsonResponse, true, 512, JSON_THROW_ON_ERROR);
        if (array_key_exists('message', $response)) {
            throw new \RuntimeException($response['message']);
        }
    }

    public function transactions(User $user, array $params = []): array
    {
        $api = new BaseApiService();
        $jsonResponse = $api->get("/api/v1/transactions", $params, ["Authorization: Bearer {$user->getApiToken()}"]);
        $response = json_decode($jsonResponse, true, 512, JSON_THROW_ON_ERROR);
        if (array_key_exists('message', $response)) {
            throw new \RuntimeException($response['message']);
        }
        return $response;
    }

    public function addCourse(User $user, array $params = []) {
        $api = new BaseApiService();
        $jsonResponse = $api->post("/api/v1/courses", $params, ["Authorization: Bearer {$user->getApiToken()}"]);
        $response = json_decode($jsonResponse, true, 512, JSON_THROW_ON_ERROR);
        if (array_key_exists('message', $response)) {
            throw new \RuntimeException($response['message']);
        }
    }

    public function editCourse(User $user, string $code, array $params = []) {
        $api = new BaseApiService();
        $jsonResponse = $api->post("/api/v1/courses/{$code}/edit", $params, ["Authorization: Bearer {$user->getApiToken()}"]);
        $response = json_decode($jsonResponse, true, 512, JSON_THROW_ON_ERROR);
        if (array_key_exists('message', $response)) {
            throw new \RuntimeException($response['message']);
        }
    }
}
