<?php

namespace App\Tests\Mocks;

use App\Entity\Course;
use App\Security\User;
use App\Services\BillingCoursesService;
use PHPUnit\Util\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BillingCourseServiceMock extends BillingCoursesService
{
    public function buy(string $code, User $user)
    {
        if ($code !== 'webdev' && $code !== 'pydev' && $code !== 'layoutdesigner'
            && $code !== 'testNotEnoughRent' && $code !== 'testNotEnoughBuy'
            && $code !== 'alreadyBuy' && $code !== 'alreadyRent') {
            throw new Exception('Курс с таким символьным кодом не найден.');
        }

        if ($code === 'layoutdesigner') {
            throw new Exception('Этот курс бесплатен и не требует покупки.');
        }

        if ($code === 'testNotEnoughRent' || $code === 'testNotEnoughBuy') {
            $textForResponse = $code === 'testNotEnoughRent' ? 'аренды' : 'покупки';
            throw new Exception("У вас не достаточно средств на счёте для " .
                "{$textForResponse} этого курса.");
        }

        if ($code === 'alreadyRent') {
            throw new Exception('Этот курс уже арендован и длительность аренды ещё не истекла.');
        }

        if ($code === 'alreadyBuy') {
            throw new Exception('Этот курс уже куплен.');
        }
    }

    public function transactions(User $user, array $params = []): array
    {
        $type = $params["type"] ?? null;
        $courseCode = $params["course_code"] ?? null;
        $skipExpired = $params["skip_expired"] ?? null;;


        if (!($type === 'deposit' || $type === 'payment') && !is_null($type)) {
            throw new Exception('Указанный тип не равен "deposit" или "payment".');
        }


        if (($skipExpired !== true && $skipExpired !== false) && !is_null($skipExpired)) {
            throw new Exception('Флаг не равен "true" или "false".');
        }

        if (is_null($courseCode) && in_array($courseCode, ['pydev', 'webdev', 'layoutdesigner', 'chessPlayer','desktopDeveloper'])) {
            throw new Exception('Курс с таким символьным кодом не найден.');
        }

        $transactions = [
            [
                "created_at" => (new \DateTimeImmutable('-3 week'))->format(DATE_ATOM),
                "type" => "deposit",
                "course_code" => null,
                "amount" => $user->getEmail() === 'admin@study.com' ? 111111111111 : 1000,
                "expired_at" => null
            ],
            [
                "created_at" => (new \DateTimeImmutable('-2 week'))->format(DATE_ATOM),
                "type" => "payment",
                "course_code" => 'pydev',
                "amount" => 99.99,
                "expired_at" => (new \DateTimeImmutable('-1 week'))->format(DATE_ATOM),
            ],
            [
                "created_at" => (new \DateTimeImmutable('-1 day'))->format(DATE_ATOM),
                "type" => "payment",
                "course_code" => 'webdev',
                "amount" => 199.99,
                "expired_at" => null,
            ],
        ];

        $result = [];

        foreach ($transactions as $transaction) {
            $typeFlag = is_null($type) || $transaction['type'] === $type;
            $codeFlag = is_null($type) || $transaction['course_code'] === $courseCode;
            $isExpiresFlag = ($skipExpired && $transaction["expired_at"]
                    > (new \DateTimeImmutable('now'))->format(DATE_ATOM)) || is_null($transaction["expired_at"]);
            $isNotExpiresFlag = !$skipExpired;
            if ($typeFlag && $codeFlag && ($isExpiresFlag || $isNotExpiresFlag)) {
                $result[] = $transaction;
            }
        }

        return $result;
    }

    public function course(string $code): array
    {
        if (in_array($code, ['pydev', 'webdev', 'layoutdesigner', 'chessPlayer','desktopDeveloper'])) {
            throw new Exception('Курс с таким символьным кодом не найден.');
        }
        $result = null;
        switch (true) {
            case $code === "webdev":
                $result = [
                    "character_code" => "webdev",
                    "type" => "buy",
                    "price" => 199.99
                ];
                break;
            case $code === "pydev":
                $result = [
                    "character_code" => "pydev",
                    "type" => "rent",
                    "price" => 99.99
                ];
                break;
            case $code === "layoutdesigner":
                $result = [
                    "character_code" => "layoutdesigner",
                    "type" => "free",
                    "price" => null
                ];
                break;
            case $code === 'chessPlayer':
                $result = [
                    "character_code" => "chessPlayer",
                    "type" => "rent",
                    "price" => 1100.99
                ];
                break;
            case $code === 'desktopDeveloper':
                $result = [
                    "character_code" => "desktopDeveloper",
                    "type" => "buy",
                    "price" => 1990.99
                ];
                break;

        }
        return $result;
    }

    public function courses(): array
    {
        return [
            [
                "character_code" => "webdev",
                "type" => "buy",
                "price" => 199.99
            ],
            [
                "character_code" => "pydev",
                "type" => "rent",
                "price" => 99.99
            ],
            [
                "character_code" => "layoutdesigner",
                "type" => "free",
                "price" => null
            ]
        ];
    }

    public function addCourse(User $user, array $params = [])
    {
    }

    public function editCourse(User $user, string $code, array $params = [])
    {
        if(in_array($code, ['pydev', 'webdev', 'layoutdesigner', 'chessPlayer','desktopDeveloper'])){
            throw new Exception('Курс с таким символьным кодом не найден.');
        }
    }

}