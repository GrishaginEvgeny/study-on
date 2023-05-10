<?php

namespace App\Services;

use App\Entity\Course;
use App\Exception\BillingNotFoundException;
use App\Exception\BillingValidationException;
use App\Security\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BillingCoursesService
{
    public function courses(): array
    {
        $jsonResponse = BaseApiService::get("/api/v1/courses");
        return json_decode($jsonResponse, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws BillingValidationException
     * @throws BillingNotFoundException
     * @throws \JsonException
     */

    public function course(string $code): array
    {
        $jsonResponse = BaseApiService::get("/api/v1/courses/{$code}");
        $response = json_decode($jsonResponse, true, 512, JSON_THROW_ON_ERROR);
        if (array_key_exists('code', $response)) {
                throw new BillingNotFoundException($response['message']);
        }
        return $response;
    }

    /**
     * @throws BillingNotFoundException
     * @throws BillingValidationException
     * @throws \JsonException
     */
    public function buy(string $code, User $user)
    {
        $jsonResponse = BaseApiService::post(
            "/api/v1/courses/{$code}/pay",
            null,
            ["Authorization: Bearer {$user->getApiToken()}"]
        );
        $response = json_decode($jsonResponse, true, 512, JSON_THROW_ON_ERROR);
        if (array_key_exists('code', $response)) {
            if (array_key_exists('errors', $response)) {
                throw new BillingValidationException(null, 406, null, $response['errors']);
            }
            if (array_key_exists('message', $response)) {
                throw new BillingNotFoundException($response['message']);
            }
        }
    }

    /**
     * @throws BillingNotFoundException
     * @throws \JsonException
     */
    public function transactions(User $user, array $params = []): array
    {
        $jsonResponse = BaseApiService::get(
            "/api/v1/transactions",
            $params,
            ["Authorization: Bearer {$user->getApiToken()}"]
        );
        $response = json_decode($jsonResponse, true, 512, JSON_THROW_ON_ERROR);
        if (array_key_exists('code', $response)) {
            throw new BillingNotFoundException($response['message']);
        }
        return $response;
    }

    /**
     * @throws BillingValidationException
     * @throws BillingNotFoundException
     * @throws \JsonException
     */
    public function addCourse(User $user, array $params = [])
    {
        $jsonResponse = BaseApiService::post(
            "/api/v1/courses",
            $params,
            ["Authorization: Bearer {$user->getApiToken()}"]
        );
        $response = json_decode($jsonResponse, true, 512, JSON_THROW_ON_ERROR);
        if (array_key_exists('code', $response)) {
            if (array_key_exists('errors', $response)) {
                throw new BillingValidationException(null, 406, null, $response['errors']);
            }
            if (array_key_exists('message', $response)) {
                throw new BillingNotFoundException($response['message']);
            }
        }
    }

    /**
     * @throws BillingNotFoundException
     * @throws BillingValidationException
     * @throws \JsonException
     */
    public function editCourse(User $user, string $code, array $params = [])
    {
        $jsonResponse = BaseApiService::post(
            "/api/v1/courses/{$code}/edit",
            $params,
            ["Authorization: Bearer {$user->getApiToken()}"]
        );
        $response = json_decode($jsonResponse, true, 512, JSON_THROW_ON_ERROR);
        if (array_key_exists('code', $response)) {
            if (array_key_exists('errors', $response)) {
                throw new BillingValidationException(null, 406, null, $response['errors']);
            }
            if (array_key_exists('message', $response)) {
                throw new BillingNotFoundException($response['message']);
            }
        }
    }
}
