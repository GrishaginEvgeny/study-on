<?php

namespace App\Security;

use App\Exception\BillingUnavailableException;
use App\Services\BillingUserService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class BillingAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private UrlGeneratorInterface $urlGenerator;
    private BillingUserService $billingService;

    public function __construct(UrlGeneratorInterface $urlGenerator, BillingUserService $billingService)
    {
        $this->urlGenerator = $urlGenerator;
        $this->billingService = $billingService;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');
        $password = $request->request->get('password', '');
        $userCredentials = json_encode([
            'username' => $email,
            'password' => $password
        ]);

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        $loadUser = function ($userCredentials) {
            try {
                $userInfo = $this->billingService->auth($userCredentials);
                $user = new User();
                $user->setEmail($userInfo['username']);
                $user->setBalance($userInfo['balance']);
                $user->setRoles($userInfo['roles']);
                $user->setApiToken($userInfo['token']);
                $user->setRefreshToken($userInfo['refreshToken']);
                return $user;
            } catch (BillingUnavailableException | \Exception $e) {
                throw new CustomUserMessageAuthenticationException($e->getMessage());
            }
        };
        return new SelfValidatingPassport(
            new UserBadge($userCredentials, $loadUser),
            [
                new CsrfTokenBadge('authenticate', $request->get('_csrf_token')),
                new RememberMeBadge()
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_course_index'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
