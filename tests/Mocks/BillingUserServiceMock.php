<?php

namespace App\Tests\Mocks;

use App\Exception\BillingValidationException;
use App\Security\User;
use App\Services\BillingUserService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class BillingUserServiceMock extends BillingUserService
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @throws \App\Services\BillingUserWrongCredentialsException
     * @throws \JsonException
     */
    public function auth(string $jsonedCredentials): array
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
            ], JSON_THROW_ON_ERROR));
            $token = base64_encode(json_encode([
                'username' => $arrayedCredentials['username'],
                'iat' => (new \DateTime('now'))->getTimestamp(),
                'exp' => (new \DateTime('+ 1 hour'))->getTimestamp(),
                'roles' => $arrayedCredentials['username'] === 'admin@study.com' ? ['ROLE_SUPER_ADMIN'] : ['ROLE_USER'],
            ], JSON_THROW_ON_ERROR));
            $token = "filler.{$token}";
            return $this->currentUser($token, $refreshToken);
        }

        throw new BadRequestHttpException($this->translator->trans(
            'errors.user.invalid_credentials',
            [],
            'validators'
        ));
    }

    /**
     * @throws \JsonException
     */
    public function currentUser(string $token, string $refreshToken): array
    {
        $arrayedCredentials = json_decode(base64_decode($refreshToken, true), true, 512, JSON_THROW_ON_ERROR);
        return array_merge($arrayedCredentials, ['token' => $token, 'refreshToken' => $refreshToken]);
    }

    public function register(string $jsonedCredentials): array
    {
        $arrayedCredentials = json_decode($jsonedCredentials, true);
        if (
            $arrayedCredentials['username'] === 'admin@study.com'
            || $arrayedCredentials['username'] === 'usualuser@study.com'
        ) {
            throw new BillingValidationException(
                null,
                406,
                null,
                [$this->translator->trans(
                    'errors.register.email.non_unique',
                    [],
                    'validators'
                )]
            );
        }
        $refreshToken = base64_encode(json_encode([
            'username' => $arrayedCredentials['username'],
            'password' => $arrayedCredentials['password'],
            'roles' => ['ROLE_USER'],
            'balance' => 1000
        ], JSON_THROW_ON_ERROR));
        $token = base64_encode(json_encode([
            'username' => $arrayedCredentials['username'],
            'iat' => (new \DateTime('now'))->getTimestamp(),
            'exp' => (new \DateTime('+ 1 hour'))->getTimestamp(),
            'roles' => ['ROLE_USER'],
        ], JSON_THROW_ON_ERROR));
        $token = "filler.{$token}";
        return $this->currentUser($token, $refreshToken);
    }

    /**
     * @throws \JsonException
     */
    public function refresh(string $refreshToken): array
    {
        $arrayedPayload = json_decode(
            base64_decode($refreshToken, true),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $user = new User();
        $token = base64_encode(json_encode([
            'username' => $arrayedPayload['username'],
            'iat' => (new \DateTime('now'))->getTimestamp(),
            'exp' => (new \DateTime('+ 1 hour'))->getTimestamp(),
            'roles' => ['ROLE_USER'],
        ], JSON_THROW_ON_ERROR));
        $token = "filler.{$token}";
        return ['token' => $token, 'refreshToken' => $refreshToken];
    }
}
