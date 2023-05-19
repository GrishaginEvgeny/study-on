<?php

namespace App\Controller;

use App\Exception\BillingNotFoundException;
use App\Exception\BillingValidationException;
use App\Form\RegisterType;
use App\Form\TransactionForm;
use App\Security\BillingAuthenticator;
use App\Security\User;
use App\Services\BillingCoursesService;
use App\Services\BillingUserService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class UserSecurityController extends AbstractController
{
    private BillingUserService $billingUserService;

    private AuthenticationUtils $authenticationUtils;

    private BillingCoursesService $billingCoursesService;

    private UserAuthenticatorInterface $authenticator;

    private BillingAuthenticator $formAuthenticator;

    public function __construct(
        BillingUserService $billingUserService,
        AuthenticationUtils $authenticationUtils,
        BillingCoursesService $billingCoursesService,
        UserAuthenticatorInterface $authenticator,
        BillingAuthenticator $formAuthenticator
    ) {
        $this->billingCoursesService = $billingCoursesService;
        $this->billingUserService = $billingUserService;
        $this->authenticationUtils = $authenticationUtils;
        $this->authenticator = $authenticator;
        $this->formAuthenticator = $formAuthenticator;
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_course_index');
        }

        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/profile", name="app_profile")
     */
    public function profile(): Response
    {
        $authedUser = $this->getUser();
        $token = $authedUser->getApiToken();
        $refreshToken = $authedUser->getRefreshToken();
        $userInfo = $this->billingUserService->currentUser($token, $refreshToken);
        return $this->render('security/profile.html.twig', [
            'email' => $userInfo['username'],
            'roles' => $userInfo['roles'],
            'balance' => $userInfo['balance'],
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/transactions", name="app_transactions")
     */
    public function transactions(Request $request): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(TransactionForm::class);
        $form->handleRequest($request);
        $params = [
            'type' => null,
            'course_code' => null,
            'skip_expired' => null
        ];
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $type = count($formData['type']) === 1 ? array_values($formData['type'])[0] : null;
            $params = [
                'type' => $type,
                'course_code' => $formData['course_code'],
                'skip_expired' => $formData['skip_expired']
            ];
        }
        $transactions = $this->billingCoursesService->transactions($user, $params);
        return $this->render('security/transactions.html.twig', [
            'transactions' => $transactions,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_profile');
        }
        $form = $this->createForm(RegisterType::class);
        $form->handleRequest($request);
        $error = null;
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $userInfo = $this->billingUserService->register(json_encode($form->getData(), JSON_THROW_ON_ERROR));
                $user = new User();
                $user->setEmail($userInfo['username']);
                $user->setBalance($userInfo['balance']);
                $user->setRoles($userInfo['roles']);
                $user->setApiToken($userInfo['token']);
                $user->setRefreshToken($userInfo['refreshToken']);
                return $this->authenticator->authenticateUser(
                    $user,
                    $this->formAuthenticator,
                    $request
                );
            } catch (BillingValidationException $e) {
                $error = implode("\n", $e->getErrors());
            } catch (BillingNotFoundException $e) {
                $error = $e->getMessage();
            }
        }
        return $this->render('security/register.html.twig', [
            'form' => $form->createView(),
            'error' => $error
        ]);
    }
}
