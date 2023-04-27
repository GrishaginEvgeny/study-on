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
            throw new Exception(json_encode([
                'code' => Response::HTTP_NOT_FOUND,
                'message' => 'Курс с таким символьным кодом не найден.',
            ]));
        }

        if($code === 'layoutdesigner') {
            throw new Exception(json_encode([
                'code' => Response::HTTP_NOT_ACCEPTABLE,
                'message' => 'Этот курс бесплатен и не требует покупки.',
            ]));
        }

        if($code === 'testNotEnoughRent' || $code === 'testNotEnoughBuy') {
            $textForResponse = $code === 'testNotEnoughRent' ? 'аренды' : 'покупки';
            throw new Exception(json_encode([
                'code' => Response::HTTP_NOT_ACCEPTABLE,
                'message' => "У вас не достаточно средств на счёте для ".
                    "{$textForResponse} этого курса.",
            ]));
        }

        if($code === 'alreadyRent'){
            throw new Exception(json_encode([
                'code' => Response::HTTP_NOT_ACCEPTABLE,
                'message' => 'Этот курс уже арендован и длительность аренды ещё не истекла.',
            ]));
        }

        if($code === 'alreadyBuy'){
            throw new Exception(json_encode([
                'code' => Response::HTTP_NOT_ACCEPTABLE,
                'message' => 'Этот курс уже куплен.',
            ]));
        }
    }

    public function transactions(User $user, array $params = []): array
    {
        $type = $params["type"] ?? null;
        $courseCode = $params["course_code"] ?? null;
        $skipExpired = $params["skip_expired"] ?? null;;


        if(!($type === 'deposit' || $type === 'payment') && !is_null($type)){
            throw new Exception(json_encode([
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => 'Указанный тип не равен "deposit" или "payment".',
            ]));
        }


        if(($skipExpired !== true && $skipExpired !== false) && !is_null($skipExpired)){
            throw new Exception(json_encode([
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => 'Флаг не равен "true" или "false".',
            ]));
        }


        if($courseCode && in_array($courseCode, ['pydev', 'webdev', 'layoutdesigner'])){
            throw new Exception(json_encode([
                'code' => Response::HTTP_NOT_FOUND,
                'message' => 'Курс с таким символьным кодом не найден.',
            ]));
        }

        $transArray = [
            [
                "created_at" => (new \DateTimeImmutable('-3 week'))->format(DATE_ATOM),
                "type" => "deposit",
                "course_code" => null,
                "amount" =>  $user->getEmail() === 'admin@study.com' ? 111111111111 : 1000,
                "expired_at" => null
            ],
            [
                "created_at" => (new \DateTimeImmutable('-2 week'))->format(DATE_ATOM),
                "type" => "payment",
                "course_code" => 'pydev',
                "amount" =>  99.99,
                "expired_at" => (new \DateTimeImmutable('-1 week'))->format(DATE_ATOM),
            ],
            [
                "created_at" => (new \DateTimeImmutable('-1 day'))->format(DATE_ATOM),
                "type" => "payment",
                "course_code" => 'webdev',
                "amount" =>  199.99,
                "expired_at" => null,
            ],
        ];

        $resultArray = [];

        foreach ($transArray as $trans){
            $typeFlag = is_null($type) || $trans['type'] === $type;
            $codeFlag = is_null($type) || $trans['course_code'] === $courseCode;
            $isExpiresFlag = ($skipExpired && $trans["expired_at"]
                    > (new \DateTimeImmutable('now'))->format(DATE_ATOM)) || is_null($trans["expired_at"]);
            $isNotExpiresFlag = !$skipExpired;
            if($typeFlag && $codeFlag && ($isExpiresFlag || $isNotExpiresFlag))  {
                $resultArray[] = $trans;
            }
        }

        return $resultArray;
    }

}