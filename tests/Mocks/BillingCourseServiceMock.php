<?php

namespace App\Tests\Mocks;

use App\Security\User;
use App\Services\BillingCoursesService;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class BillingCourseServiceMock extends BillingCoursesService
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buy(string $code, User $user)
    {
        if (
            $code !== 'webdev' && $code !== 'pydev' && $code !== 'layoutdesigner'
            && $code !== 'chessPlayer' && $code !== 'desktopDeveloper'
        ) {
            throw new NotFoundHttpException($this->translator->trans(
                'errors.course.doesnt_exist',
                [],
                'validators'
            ));
        }
    }

    public function transactions(User $user, array $params = []): array
    {
        $type = $params["type"] ?? null;
        $courseCode = $params["course_code"] ?? null;
        $skipExpired = $params["skip_expired"] ?? null;
        ;

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
        if (!in_array($code, ['pydev', 'webdev', 'layoutdesigner', 'chessPlayer','desktopDeveloper'])) {
            throw new NotFoundHttpException($this->translator->trans(
                'errors.course.doesnt_exist',
                [],
                'validators'
            ));
        }
        $result = null;
        switch (true) {
            case $code === "webdev":
                $result = [
                    "course_code" => "webdev",
                    "type" => "buy",
                    "price" => 199.99
                ];
                break;
            case $code === "pydev":
                $result = [
                    "course_code" => "pydev",
                    "type" => "rent",
                    "price" => 99.99
                ];
                break;
            case $code === "layoutdesigner":
                $result = [
                    "course_code" => "layoutdesigner",
                    "type" => "free",
                    "price" => null
                ];
                break;
            case $code === 'chessPlayer':
                $result = [
                    "course_code" => "chessPlayer",
                    "type" => "rent",
                    "price" => 1100.99
                ];
                break;
            case $code === 'desktopDeveloper':
                $result = [
                    "course_code" => "desktopDeveloper",
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
                "course_code" => "webdev",
                "type" => "buy",
                "price" => 199.99
            ],
            [
                "course_code" => "pydev",
                "type" => "rent",
                "price" => 99.99
            ],
            [
                "course_code" => "layoutdesigner",
                "type" => "free",
                "price" => null
            ],
            [
                "course_code" => "chessPlayer",
                "type" => "rent",
                "price" => 1100.99
            ],
            [
                "course_code" => "desktopDeveloper",
                "type" => "rent",
                "price" => 1999.99
            ]
        ];
    }

    public function addCourse(User $user, array $params = [])
    {
        if (in_array($params['code'], ['pydev', 'webdev', 'layoutdesigner', 'chessPlayer','desktopDeveloper'])) {
            throw new NotFoundHttpException($this->translator->trans(
                'errors.course.doesnt_exist',
                [],
                'validators'
            ));
        }
    }

    public function editCourse(User $user, string $code, array $params = [])
    {
        if (in_array($params['code'], ['pydev', 'webdev', 'layoutdesigner', 'chessPlayer','desktopDeveloper'])) {
            throw new NotAcceptableHttpException($this->translator->trans(
                'errors.course.slug.non_unique',
                [],
                'validators'
            ));
        }

        if (!in_array($code, ['pydev', 'webdev', 'layoutdesigner', 'chessPlayer','desktopDeveloper'])) {
            throw new NotFoundHttpException($this->translator->trans(
                'errors.course.doesnt_exist',
                [],
                'validators'
            ));
        }
    }
}
